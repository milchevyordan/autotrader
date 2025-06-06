<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ImportEuOrWorldType;
use App\Enums\ImportOrOriginType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\VehicleStock;
use App\Enums\WorkOrderType;
use App\Exceptions\SalesOrderException;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\StoreWorkOrdersRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\File;
use App\Models\SalesOrder;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Services\DataTable\DataTable;
use App\Services\DataTable\RawOrdering;
use App\Services\Files\FileManager;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class SalesOrderService extends Service
{
    /**
     * The sales order model.
     *
     * @var SalesOrder
     */
    public SalesOrder $salesOrder;

    /**
     * Collection of all the selected vehicles for the sales order.
     *
     * @var Collection
     */
    private Collection $selectedVehicles;

    /**
     * Array holding all file types.
     *
     * @var string[]
     */
    private array $fileTypes = [
        'files',
        'contractSignedFiles',
        'creditCheckFiles',
        'viesFiles',
    ];

    /**
     * Create a new SalesOrderService instance.
     */
    public function __construct()
    {
        $this->setSalesOrder(new SalesOrder());
    }

    /**
     * Get the model of the Sales order.
     *
     * @return SalesOrder
     */
    public function getSalesOrder(): SalesOrder
    {
        return $this->salesOrder;
    }

    /**
     * Set the model of the Sales order.
     *
     * @param  SalesOrder $salesOrder
     * @return self
     */
    public function setSalesOrder(SalesOrder $salesOrder): self
    {
        $this->salesOrder = $salesOrder;

        return $this;
    }

    /**
     * Get the vehicles dataTable shown in create form.
     *
     * @return DataTable<Vehicle>
     */
    public function getCreateMethodVehiclesDataTable(): DataTable
    {
        $userHasSearched = request(null)->input('filter.global');

        return (new SystemVehicleService())->getSalesOrderAndQuoteIndexMethodTable()
            ->run(5, function ($model) use ($userHasSearched) {
                return $userHasSearched ? $model->withoutTrashed()->whereDoesntHave('salesOrder') : $model->whereNull('id');
            });
    }

    /**
     * Get the vehicles dataTable shown in edit form.
     *
     * @return DataTable<Vehicle>
     */
    public function getEditMethodVehiclesDataTable(): DataTable
    {
        $userHasSearched = request(null)->input('filter.global');
        $salesOrder = $this->getSalesOrder();

        $dataTable = (new SystemVehicleService())->getSalesOrderAndQuoteIndexMethodTable();

        if ($selectedVehiclesIds = $this->getSelectedVehicles()->pluck('id')->toArray()) {
            $dataTable->setRawOrdering(new RawOrdering('FIELD(vehicles.id, '.implode(',', $selectedVehiclesIds).') DESC'));
        }

        return $dataTable->run(5, function ($model) use ($userHasSearched, $salesOrder) {
            $vehiclesToShow = $model->whereHas('salesOrder', function ($query) use ($salesOrder) {
                $query->where('sales_orders.id', $salesOrder->id);
            });

            return (! $userHasSearched || SalesOrderStatus::Concept != $salesOrder->status) ? $vehiclesToShow : $vehiclesToShow->orWhere(function ($query) {
                return $query->withoutTrashed()->whereDoesntHave('salesOrder');
            });
        });
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
        $this->selectedVehicles = $this->getSalesOrder()
            ->vehicles()
            ->withTrashed()
            ->select('vehicles.id')
            ->withPivot('delivery_week')
            ->get();
    }

    /**
     * Sales order creation.
     *
     * @param StoreSalesOrderRequest $request
     * @return self
     */
    public function createSalesOrder(StoreSalesOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $salesOrder = new SalesOrder();
        $salesOrder->fill($validatedRequest);
        $salesOrder->creator_id = auth()->id();
        $salesOrder->save();
        $this->setSalesOrder($salesOrder);

        (new VehicleableService($salesOrder))->syncVehicles($validatedRequest['vehicles'] ?? []);

        $salesOrder->sendInternalRemarks($validatedRequest);
        $salesOrder->orderServices()->createMany($validatedRequest['additional_services'] ?? []);

        $orderItemsToCreate = collect($validatedRequest['items'] ?? [])
            ->filter(function ($orderItem) {
                return $orderItem['shouldBeAdded'];
            })
            ->all();

        $salesOrder->orderItems()->createMany($orderItemsToCreate);

        UploadHelper::handleFileUploads($salesOrder, $validatedRequest, $this->fileTypes);
        $this->setSalesOrder($salesOrder);

        return $this;
    }

    /**
     * Sales order update.
     *
     * @param UpdateSalesOrderRequest $request
     * @return self
     */
    public function updateSalesOrder(UpdateSalesOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $salesOrder = $this->getSalesOrder();

        $salesOrder->update($validatedRequest);
        $salesOrder->sendInternalRemarks($validatedRequest);

        $salesOrder->vehicles()->whereNotIn('id', array_keys($validatedRequest['vehicleIds'] ?? []))->detach();
        (new VehicleableService($salesOrder))->syncVehicles($validatedRequest['vehicles'] ?? []);

        (new AdditionalServiceService())->updateAdditionalServices($validatedRequest, $salesOrder);

        (new ItemService())->updateItems($validatedRequest, $salesOrder);

        UploadHelper::handleFileUploads($salesOrder, $validatedRequest, $this->fileTypes);
        $this->setSalesOrder($salesOrder);

        return $this;
    }

    /**
     * Copy resource prices.
     *
     * @return null|string
     */
    public function generatePreviewPdf(): ?string
    {
        try {
            return $this->generatePdf()['path'] ?? null;
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return null;
        }
    }

    /**
     * Update the sales order status.
     *
     * @param UpdateStatusRequest $request
     * @return SalesOrderService
     * @throws SalesOrderException
     */
    public function updateSalesOrderStatus(UpdateStatusRequest $request): self
    {
        $validatedRequest = $request->validated();

        $salesOrder = $this->getSalesOrder();

        switch ($validatedRequest['status']) {
            case SalesOrderStatus::Submitted->value:
                if (! auth()->user()->can('submit-sales-order')) {
                    throw new SalesOrderException(__('You do not have the permission.'));
                }

                if ($salesOrder->vehicles->isEmpty()) {
                    throw new SalesOrderException(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                break;
            case SalesOrderStatus::Approved->value:
                CalculationService::updateCalculation($salesOrder);
            case SalesOrderStatus::Rejected->value:
                if (! auth()->user()->can('approve-sales-order')) {
                    throw new SalesOrderException(__('You do not have the permission.'));
                }

                break;
            case SalesOrderStatus::Sent_to_buyer->value:
                $localeService = (new LocaleService())->checkChangeToLocale($validatedRequest['locale']);
                $this->generatePdf();
                $localeService->setOriginalLocale();

                break;
            case SalesOrderStatus::Uploaded_signed_contract->value:
                if ($salesOrder->getGroupedFiles(['contractSignedFiles'])['contractSignedFiles']->isEmpty()) {
                    throw new SalesOrderException(__('Not uploaded signed contract, need to upload signed contract.'));
                }

                break;
            case SalesOrderStatus::Down_payment_done->value:
                if (! $salesOrder->down_payment || $salesOrder->down_payment_amount <= 0) {
                    throw new SalesOrderException(__('Not saved down payment, need to enter down payment.'));
                }

                break;
            default:
                break;
        }

        $salesOrder->status = $validatedRequest['status'];
        $salesOrder->save();

        if (1 != $validatedRequest['status']) {
            $salesOrder->statuses()->updateOrCreate(['status' => $validatedRequest['status']], ['created_at' => now()]);
        }

        return $this;
    }

    /**
     * Generate sales order pdf.
     *
     * @return File
     */
    public function generatePdf(): File
    {
        $salesOrder = $this->getSalesOrder();

        $salesOrder->load([
            'customer:id,full_name',
            'customerCompany' => function ($query) {
                $query->select('id', 'name', 'address', 'postal_code', 'city', 'country', 'vat_number')->withTrashed();
            },
            'creator.company' => function ($query) {
                $query->select(
                    'id',
                    'name',
                    'address',
                    'postal_code',
                    'city',
                    'country',
                    'kvk_number',
                    'vat_number',
                    'bank_name',
                    'iban',
                    'swift_or_bic',
                )->withTrashed();
            },
            'seller:id,full_name',
            'vehicles' => function ($query) {
                $query->withTrashed()->withPivot('delivery_week')
                    ->with(['images' => function ($imagesQuery) {
                        return $imagesQuery->where('section', 'externalImages')->take(2);
                    },
                    ]);
            },
            'vehicles.creator',
            'vehicles.make:id,name',
            'vehicles.vehicleModel:id,name',
            'vehicles.variant:id,name',
            'vehicles.engine:id,name',
            'vehicles.calculation',
            'orderItems',
            'orderServices',
            'serviceLevel:id,name',
        ]);

        $vehiclesCount = $salesOrder->vehicles->count();
        $salesPriceServiceItemsPerVehicleUnits =
            CurrencyHelper::convertCurrencyToUnits($salesOrder->total_sales_price_service_items) /
            $vehiclesCount;
        $salesPriceServiceItemsPerVehicle = CurrencyHelper::convertUnitsToCurrency(
            $salesPriceServiceItemsPerVehicleUnits,
        );

        ImageManager::setVehicleImagesBase64($salesOrder);

        $companyPdfAssets = CompanyService::getPdfAssets();

        $pdfService = new PdfService(
            $this->getTemplate($salesOrder->is_brutto),
            [
                'salesOrder'                              => $salesOrder,
                'attributes'                              => ModelHelper::getVehicleAttributes(),
                'allItemsAndAdditionals'                  => ServiceLevelService::checkAllItemsAndAdditionalsInOutput($salesOrder),
                'salesPriceServiceItemsPerVehicleUnits'   => $salesPriceServiceItemsPerVehicleUnits,
                'salesPriceServiceItemsPerVehicle'        => $salesPriceServiceItemsPerVehicle,
                'vehiclesCount'                           => $vehiclesCount,
                'headerPrePurchaseSalesOrderImage'        => $companyPdfAssets['pdf_header_pre_purchase_sales_order_image'],
                'headerQuoteTransportAndDeclarationImage' => $companyPdfAssets['pdf_header_quote_transport_and_declaration_image'],
                'signatureImage'                          => $companyPdfAssets['pdf_signature_image'],
            ],
            [
                'fontDir' => storage_path('fonts'),
            ]
        );

        $fileManager = new FileManager();
        $generatedPdf = UploadHelper::uploadGeneratedFile($pdfService->setPaginator(new Paginator(x: 40, y: 810))
        ->setCanvasTextRenderers(
            new Collection([
                new CanvasTextRenderer(190, 810, true, (string) auth()->user()?->company->pdf_footer_text),
            ])
        )
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
        )->generate(), "sales-order-{$salesOrder->id}", 'pdf');
        $fileToReplace = $salesOrder->files()->where('section', 'generatedPdf')->first();
        $fileManager->deleteIfExists($fileToReplace);
        $salesOrder->saveWithFile($generatedPdf, 'generatedPdf');

        return $generatedPdf;
    }

    /**
     * Returns the template based on the brutto or netto.
     *
     * @param  bool   $isBrutto
     * @return string
     */
    private function getTemplate(bool $isBrutto): string
    {
        return $isBrutto ? 'templates/sales-orders/sales-order-brutto' :
            'templates/sales-orders/sales-order-netto';
    }

    /**
     * Create separate work orders for every vehicle.
     *
     * @param StoreWorkOrdersRequest $request
     * @return SalesOrderService
     */
    public function createWorkOrders(StoreWorkOrdersRequest $request): self
    {
        $validatedRequest = $request->validated();

        foreach ($validatedRequest['ids'] as $vehicleId) {
            $workOrder = new WorkOrder([
                'type'           => WorkOrderType::Vehicle->value,
                'vehicleable_id' => $vehicleId,
            ]);

            $workOrder->vehicleable_type = Vehicle::class;
            $workOrder->creator_id = auth()->id();

            $workOrder->save();

            OwnershipService::createAuthOwnership($workOrder);
        }

        return $this;
    }

    /**
     * Return sales orders in this company datatable used in index page and document's create and edit pages.
     *
     * @param  null|array $additionalRelations
     * @param  null|bool  $withTrashed
     * @return DataTable
     */
    public function getIndexMethodDataTable(?array $additionalRelations = [], ?bool $withTrashed = false): DataTable
    {
        $dataTable = (new DataTable(
            SalesOrder::inThisCompany()
                ->with([
                    'statuses',
                ])
                ->when($withTrashed, function ($query) {
                    return $query->withTrashed();
                })
                ->select(SalesOrder::$defaultSelectFields)
        ))
            ->setRelation('customerCompany', ['id', 'name'])
            ->setRelation('seller', ['id', 'full_name'])
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true, true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('type_of_sale', __('Type Of Sale'), true, true)
            ->setColumn('customerCompany.name', __('Customer Company'), true, true)
            ->setColumn('seller.full_name', __('Sales Person'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', SalesOrderStatus::class)
            ->setEnumColumn('type_of_sale', ImportEuOrWorldType::class);

        foreach ($additionalRelations as $relation) {
            if (str_contains($relation, ':')) {
                [$relationName, $columns] = explode(':', $relation, 2);
                $columnsArray = array_map('trim', explode(',', $columns)); // Support multiple columns if needed
                $dataTable->setRelation($relationName, $columnsArray);
            } else {
                // No specific columns provided, use default behavior
                $dataTable->setRelation($relation);
            }
        }

        return $dataTable;
    }

    /**
     * Return datatable of Sales Orders by provided builder.
     *
     * @param  Builder   $builder
     * @param  mixed     $full
     * @return DataTable
     */
    public static function getSalesOrdersDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(SalesOrder::$defaultSelectFields)
        ))
            ->setRelation('customerCompany', ['id', 'name'])
            ->setRelation('seller', ['id', 'full_name'])
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('status', __('Status'), true, true)
            ->setColumn('type_of_sale', __('Sale Type'), true, true)
            ->setColumn('customerCompany.name', __('Customer Company'), true, true)
            ->setColumn('seller.full_name', __('Sales Person'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', SalesOrderStatus::class)
            ->setEnumColumn('type_of_sale', ImportOrOriginType::class);

        return $dataTable;
    }

    public function canCreateDownPaymentInvoice(SalesOrder $salesOrder): bool
    {
        $vehicles = $salesOrder->vehicles->load(['purchaseOrder:id,status']);

        return ! $vehicles->contains(fn ($vehicle) => $vehicle->purchaseOrder->first()?->status?->value < PurchaseOrderStatus::Submitted->value);
    }
}
