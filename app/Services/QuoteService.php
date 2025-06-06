<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OwnershipStatus;
use App\Enums\QuoteInvitationStatus;
use App\Enums\QuoteStatus;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\File;
use App\Models\Ownership;
use App\Models\Quote;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;
use App\Services\DataTable\RawOrdering;
use App\Services\Files\UploadHelper;
use App\Services\Images\ImageManager;
use App\Services\Pdf\Canvas\CanvasImageRenderer;
use App\Services\Pdf\Canvas\CanvasTextRenderer;
use App\Services\Pdf\Pagination\Paginator;
use App\Services\Pdf\PdfService;
use App\Services\Vehicles\SystemVehicleService;
use App\Services\Vehicles\VehicleableService;
use App\Support\CurrencyHelper;
use App\Support\ModelHelper;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class QuoteService extends Service
{
    /**
     * Quote model.
     *
     * @var Quote
     */
    private Quote $quote;

    /**
     * Collection of all the selected vehicles for concrete quote.
     *
     * @var Collection
     */
    private Collection $selectedVehicles;

    /**
     * Vehicle id that will be automatically selected in create.
     *
     * @var int
     */
    private int $queryVehicleId;

    /**
     * Create a new QuoteService instance.
     */
    public function __construct()
    {
        $this->setQuote(new Quote());
    }

    /**
     * Get the value of selectedVehicles.
     *
     * @return Collection
     */
    public function getSelectedVehicles(): Collection
    {
        if (! isset($this->selectedVehicles)) {
            $this->setSelectedVehicles();
        }

        return $this->selectedVehicles;
    }

    /**
     * Set the value of selectedVehicles.
     *
     * @return void
     */
    private function setSelectedVehicles(): void
    {
        $this->selectedVehicles = $this->getQuote()
            ->vehicles()
            ->withTrashed()
            ->select('id')
            ->withPivot('delivery_week')
            ->get();
    }

    /**
     * Get the value of defaultCurrencies.
     *
     * @return int
     */
    public function getQueryVehicleId(): int
    {
        if (! isset($this->queryVehicleId)) {
            $this->setQueryVehicleId();
        }

        return $this->queryVehicleId;
    }

    /**
     * Set the value of queryVehicleId.
     *
     * @return void
     */
    private function setQueryVehicleId(): void
    {
        $this->queryVehicleId = (int) request()->input('filter.id');
    }

    /**
     * Collection of DataTable<Quote>.
     *
     * @return DataTable<Quote>
     */
    public function getIndexMethodTable(): DataTable
    {
        return (new DataTable(
            Quote::inThisCompany()->with([
                'statuses',
            ])->select(Quote::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('name', __('Name'), true, true)
            ->setColumn('delivery_week', __('Delivery week'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', QuoteStatus::class);
    }

    /**
     * Collection of DataTable<Vehicle>.
     *
     * @return DataTable<Vehicle>
     */
    public function getVehicleDataTable(): DataTable
    {
        $userHasSearched = request()->input('filter.global');
        $quote = $this->getQuote();

        $dataTable = (new SystemVehicleService())->getSalesOrderAndQuoteIndexMethodTable();

        if ($queryVehicleId = $this->getQueryVehicleId()) {
            $dataTable->setRawOrdering(new RawOrdering("FIELD(vehicles.id, {$queryVehicleId}) DESC"));
        } elseif ($selectedVehiclesIds = $this->getSelectedVehicles()->pluck('id')->toArray()) {
            $dataTable->setRawOrdering(new RawOrdering('FIELD(vehicles.id, '.implode(',', $selectedVehiclesIds).') DESC'));
        }

        return $dataTable->run(config('app.default.pageResults', 10), function ($model) use ($userHasSearched, $quote) {
            if (! $quote->id) {
                return $model->withoutTrashed();
            }

            return (! $userHasSearched || QuoteStatus::Concept != $quote->status) ?
                $model->whereHas('quotes', function ($query) use ($quote) {
                    $query->where('quotes.id', $quote->id);
                }) :
                $model->withoutTrashed();
        });
    }

    /**
     * Creates the quote.
     *
     * @param StoreQuoteRequest $request
     * @return self
     */
    public function createQuote(StoreQuoteRequest $request): self
    {
        $validatedRequest = $request->validated();

        $quote = new Quote();
        $quote->fill($validatedRequest);
        $quote->creator_id = auth()->id();
        $quote->save();
        $this->setQuote($quote);

        $quote->sendInternalRemarks($validatedRequest);
        (new VehicleableService($quote))->syncVehicles($validatedRequest['vehicles'] ?? []);

        $quote->orderServices()->createMany($validatedRequest['additional_services'] ?? []);

        $orderItemsToCreate = collect($validatedRequest['items'] ?? [])
            ->filter(function ($orderItem) {
                return $orderItem['shouldBeAdded'];
            })
            ->all();

        $quote->orderItems()->createMany($orderItemsToCreate);

        $this->setQuote($quote);

        return $this;
    }

    /**
     * Updates the quote.
     *
     * @param UpdateQuoteRequest $request
     * @return self
     */
    public function updateQuote(UpdateQuoteRequest $request): self
    {
        $validatedRequest = $request->validated();

        $quote = $this->getQuote();
        $quote->update($validatedRequest);

        $quote->sendInternalRemarks($validatedRequest);
        $quote->vehicles()->whereNotIn('id', array_keys($validatedRequest['vehicles'] ?? []))->detach();

        (new VehicleableService($quote))->syncVehicles($validatedRequest['vehicles'] ?? []);

        (new AdditionalServiceService())->updateAdditionalServices($validatedRequest, $quote);

        (new ItemService())->updateItems($validatedRequest, $quote);

        $this->setQuote($quote);

        return $this;
    }

    /**
     * Create sales order from quote.
     *
     * @return SalesOrder
     * @throws Exception
     */
    public function createSalesOrder(): SalesOrder
    {
        /**
         * @var User
         */
        $authUser = Auth::user();
        $authUser->load('company:id,default_currency');

        $quote = $this->getQuote();
        if ($quote->sales_order_id) {
            throw new Exception('Sales order already made, look in connected to the modules');
        }

        $quoteData = $this->getQuoteData();
        $originQuote = $quoteData['originQuote'];

        $salesOrder = new SalesOrder($quote->only([
            'customer_company_id',
            'customer_id',
            'seller_id',
            'reference',
            'type_of_sale',
            'transport_included',
            'vat_deposit',
            'service_level_id',
            'vat_percentage',
            'total_vehicles_purchase_price',
            'total_costs',
            'total_sales_price_service_items',
            'total_sales_margin',
            'total_fee_intermediate_supplier',
            'total_sales_price_exclude_vat',
            'total_sales_excl_vat_with_items',
            'total_vat',
            'total_bpm',
            'total_sales_price_include_vat',
            'total_sales_price',
            'is_brutto',
            'down_payment',
            'down_payment_amount',
            'delivery_week',
            'damage',
            'payment_condition',
            'payment_condition_free_text',
            'additional_info_conditions',
            'discount',
            'discount_in_output',
            'total_registration_fees',
        ]));
        $salesOrder->creator_id = $authUser->id;
        $salesOrder->currency = $authUser->company->default_currency;
        $salesOrder->total_sales_excl_vat_with_items = $quote->total_quote_price_exclude_vat;
        $salesOrder->total_sales_price = $quote->total_quote_price;

        $salesOrder->save();

        $originQuote->status = QuoteStatus::Created_sales_order;
        $originQuote->sales_order_id = $salesOrder->id;
        $originQuote->save();

        (new VehicleableService($salesOrder))->syncVehicles($quoteData['originVehicleIds'] ?? []);
        $salesOrder->orderServices()->createMany($quoteData['orderServicesData']);
        $salesOrder->orderItems()->createMany($quoteData['orderItemsData']);

        Ownership::insert([
            'creator_id'   => $authUser->id,
            'user_id'      => $authUser->id,
            'ownable_type' => SalesOrder::class,
            'ownable_id'   => $salesOrder->id,
            'status'       => OwnershipStatus::Accepted,
            'created_at'   => now(),
        ]);

        return $salesOrder;
    }

    /**
     * Copy resource prices.
     *
     * @param  null|string $price
     * @return null|string
     */
    private function copyPrice(?string $price): ?string
    {
        return '' == $price ? null : $price;
    }

    /**
     * Copy resource prices.
     *
     * @return null|string
     */
    public function generatePreviewPdf(): ?string
    {
        try {
            $quote = $this->getQuote();
            $vehiclesCount = $quote->vehicles->count();

            $quote->load([
                'customer:id,company_id,full_name',
                'customer.company:id,name',
                'customer.company' => function ($query) {
                    $query->select('id', 'name')->withTrashed();
                },
                'vehicles' => function ($query) use ($vehiclesCount) {
                    $query->withTrashed()->withPivot('delivery_week')
                        ->with(['images' => function ($imagesQuery) use ($vehiclesCount) {
                            $imagesQuery->where('section', 'externalImages');

                            return $vehiclesCount < 2 ? $imagesQuery : $imagesQuery->take(2);
                        },
                        ]);
                },
                'vehicles.calculation',
                'vehicles.engine:id,name',
                'vehicles.vehicleModel:id,name',
                'vehicles.make:id,name',
                'vehicles.variant:id,name',
                'vehicles.creator',
                'serviceLevel:id,name',
                'orderItems',
                'orderServices',
            ]);

            return $this->setQuote($quote)->generatePdf($vehiclesCount)['path'] ?? null;
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return null;
        }
    }

    /**
     * Generate quote pdf.
     *
     * @param  int  $vehiclesCount
     * @return File
     */
    public function generatePdf(int $vehiclesCount, ?string $customerName = null): File
    {
        $quote = $this->getQuote();

        $vehicle = $this->getVehicle($quote, $vehiclesCount);

        $salesPriceServiceItemsPerVehicleUnits =
            CurrencyHelper::convertCurrencyToUnits($quote->total_sales_price_service_items) /
            $vehiclesCount;
        $salesPriceServiceItemsPerVehicle = CurrencyHelper::convertUnitsToCurrency(
            $salesPriceServiceItemsPerVehicleUnits,
        );

        ImageManager::setVehicleImagesBase64($quote);

        $companyPdfAssets = CompanyService::getPdfAssets();

        $pdfService = new PdfService(
            $this->getTemplate($vehiclesCount),
            [
                'quote'                                   => $quote,
                'vehicle'                                 => $vehicle,
                'company'                                 => auth()->user()->company,
                'attributes'                              => ModelHelper::getVehicleAttributes(),
                'vehiclesCount'                           => $vehiclesCount,
                'customerName'                            => $customerName,
                'allItemsAndAdditionals'                  => ServiceLevelService::checkAllItemsAndAdditionalsInOutput($quote),
                'salesPriceServiceItemsPerVehicleUnits'   => $salesPriceServiceItemsPerVehicleUnits,
                'salesPriceServiceItemsPerVehicle'        => $salesPriceServiceItemsPerVehicle,
                'headerQuoteTransportAndDeclarationImage' => $companyPdfAssets['pdf_header_quote_transport_and_declaration_image'],
            ],
            [
                'fontDir' => storage_path('fonts'),
            ]
        );

        $generatedPdf = UploadHelper::uploadGeneratedFile($pdfService->setPaginator(new Paginator(x: 40, y: 810))
            ->setCanvasImageRenderers(
                new Collection([
                    new CanvasImageRenderer(
                        x: 450,
                        y: 795,
                        imagePath: public_path($companyPdfAssets['pdf_footer_image']),
                        height: 30,
                        resolution: 'high',
                        isOnEveryPage: true,
                    ),
                ])
            )->generate(), "quote-{$quote->id}", 'pdf');

        $quote->saveWithFile($generatedPdf);

        return $generatedPdf;
    }

    /**
     * Returns the template based on the vehicle count.
     *
     * @param  int    $vehiclesCount
     * @return string
     */
    private function getTemplate(int $vehiclesCount): string
    {
        return 1 == $vehiclesCount ? 'templates/quote-invitations/quote-single' :
            'templates/quote-invitations/quote-multiple';
    }

    /**
     * Return first vehicle if it is just one or null.
     *
     * @param  Quote        $quote
     * @param  int          $vehiclesCount
     * @return null|Vehicle
     */
    private function getVehicle(Quote $quote, int $vehiclesCount): Vehicle|null
    {
        return $vehiclesCount > 1 ? null : $quote->vehicles->first();
    }

    /**
     * Duplicate quote and all of its data.
     *
     * @return void
     */
    public function duplicate(): void
    {
        $quoteData = $this->getQuoteData();
        $originQuote = $quoteData['originQuote'];
        $authUser = Auth::user();

        $clonedQuote = $originQuote->replicate(['status']);
        $clonedQuote->save();
        $ownership = new Ownership([
            'user_id'      => $authUser->id,
            'ownable_id'   => $clonedQuote->id,
            'ownable_type' => $clonedQuote::class,
        ]);
        $ownership->creator_id = $authUser->id;
        $ownership->status = OwnershipStatus::Accepted;
        $clonedQuote->ownerships()->save($ownership);

        (new VehicleableService($clonedQuote))->syncVehicles($quoteData['originVehicleIds'] ?? []);
        $clonedQuote->orderServices()->createMany($quoteData['orderServicesData']);
        $clonedQuote->orderItems()->createMany($quoteData['orderItemsData']);
    }

    /**
     * Updates quote status.
     *
     * @param  int       $status
     * @return bool
     * @throws Exception
     */
    public function updateStatus(int $status): bool
    {
        $quote = $this->getQuote();

        switch ($status) {
            case QuoteStatus::Approved->value:
                if (! auth()->user()->can('approve-quote')) {
                    throw new Exception(__('You do not have the permission.'));
                }

                QuoteInvitationService::closeQuoteInvitations($quote);

                break;
            case QuoteStatus::Rejected->value:
                if (! auth()->user()->can('approve-quote')) {
                    throw new Exception(__('You do not have the permission.'));
                }

                break;
            case QuoteStatus::Stop_quote->value:
                $quote->status = QuoteStatus::Submitted;
                QuoteInvitationService::closeQuoteInvitations($quote);
                if (1 != $status) {
                    $quote->statuses()->updateOrCreate(['status' => $status]);
                    $quote->statuses()->updateOrCreate(['status' => QuoteStatus::Submitted->value]);
                }

                return $quote->save();
            case QuoteStatus::Accepted_by_client->value:
                $customerId = $quote->acceptedInvitation()?->customer_id;
                $quote->customer_id = $customerId;
                $quote->customer_company_id = User::select('id', 'company_id')->find($customerId)->company_id;

                $this->closeQuotesWithSameVehicles();
        }

        $quote->status = $status;

        if (1 != $status) {
            $quote->statuses()->updateOrCreate(['status' => $status], ['created_at' => now()]);
        }

        return $quote->save();
    }

    /**
     * Close all quotes that at least one vehicle is in.
     *
     * @return void
     */
    private function closeQuotesWithSameVehicles(): void
    {
        $quote = $this->getQuote();

        $quoteIdsToClose = $quote->vehicles()
            ->with('quotes:id')
            ->get()
            ->flatMap(fn ($vehicle) => $vehicle->quotes->pluck('id'))
            ->unique()
            ->reject(fn ($id) => $id === $quote->id)  // Exclude the current quote ID
            ->toArray();

        Quote::whereIn('id', $quoteIdsToClose)->update([
            'status' => QuoteStatus::Closed->value,
        ]);
    }

    /**
     * Reserve the quote for specific customer.
     *
     * @param  Request $request
     * @param  Quote   $quote
     * @return void
     */
    public function reserveQuote(Request $request, Quote $quote): void
    {
        $quote->reservation_customer_id = $request->reservation_customer_id;
        $quote->reservation_until = $request->reservation_until;

        $quote->save();
    }

    /**
     * Cancel the reservation for customer.
     *
     * @param  Request $request
     * @param  Quote   $quote
     * @return void
     */
    public function cancelReservation(Request $request, Quote $quote): void
    {
        $quote->reservation_customer_id = null;
        $quote->reservation_until = null;

        $quote->save();
    }

    /**
     * @param  Request $request
     * @param  Quote        $quote
     * @return Quote
     */
    public function updateCustomer(Request $request, Quote $quote): Quote
    {
        $quote->customer_id = $request->customer_id;
        $quote->customer_company_id = User::select('id', 'company_id')->find($request->customer_id)->company_id;

        $quote->status = QuoteStatus::Accepted_by_client;
        $quote->save();

        return $quote;
    }

    /**
     * Check if quote's invitations have at least one accepted that means quote is accepted,
     * and you can no longer accept another invitation.
     *
     * @param  Quote $quote
     * @return bool
     */
    public static function hasAcceptedInvitation(Quote $quote): bool
    {
        return $quote->quoteInvitations()->where('status', QuoteInvitationStatus::Accepted)->exists();
    }

    /**
     * Return quote data needed to create sales order or to duplicate quote.
     *
     * @return array
     */
    public function getQuoteData(): array
    {
        $quote = $this->getQuote();

        $originQuote = $quote->load([
            'vehicles:id',
            'vehicles' => function ($query) {
                $query->withTrashed()->withPivot('delivery_week')->select('id');
            },
            'orderServices',
            'orderItems',
        ]);

        return [
            'originQuote'       => $originQuote,
            'originVehicleIds'  => $originQuote->vehicles->pluck('pivot', 'id')->toArray(),
            'orderServicesData' => $originQuote->orderServices->map(function ($orderService) {
                return [
                    'name'           => $orderService->name,
                    'purchase_price' => $orderService->purchase_price,
                    'sale_price'     => $orderService->sale_price,
                    'in_output'      => $orderService->in_output,
                ];
            }),
            'orderItemsData' => $originQuote->orderItems->map(function ($orderItem) {
                return [
                    'id'            => $orderItem->id,
                    'sale_price'    => $orderItem->sale_price,
                    'in_output'     => $orderItem->in_output,
                    'shouldBeAdded' => $orderItem->shouldBeAdded,
                ];
            }),
        ];
    }

    /**
     * Return datatable of Quotes by provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getQuotesDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(Quote::$defaultSelectFields)
        ))
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('status', __('Status'), true, true)
            ->setColumn('name', __('Name'), true, true)
            ->setColumn('delivery_week', __('Delivery week'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', QuoteStatus::class);

        return $dataTable;
    }

    /**
     * Get the value of quote.
     *
     * @return Quote
     */
    public function getQuote(): Quote
    {
        return $this->quote;
    }

    /**
     * Set the value of quote.
     *
     * @param  Quote $quote
     * @return self
     */
    public function setQuote(Quote $quote): self
    {
        $this->quote = $quote;

        return $this;
    }
}
