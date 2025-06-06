<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransportableType;
use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Exceptions\TransportOrderException;
use App\Http\Requests\StoreTransportOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Requests\UpdateTransportOrderRequest;
use App\Models\CompanyAddress;
use App\Models\Transportable;
use App\Models\TransportOrder;
use App\Services\DataTable\DataTable;
use App\Services\DataTable\RawOrdering;
use App\Services\Files\UploadHelper;
use App\Services\Pdf\Canvas\CanvasImageRenderer;
use App\Services\Pdf\Canvas\CanvasTextRenderer;
use App\Services\Pdf\Pagination\Paginator;
use App\Services\Pdf\PdfService;
use App\Services\Vehicles\PreOrderVehicleService;
use App\Services\Vehicles\ServiceVehicleService;
use App\Services\Vehicles\SystemVehicleService;
use App\Support\CurrencyHelper;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TransportOrderService extends Service
{
    /**
     * The transport order model.
     *
     * @var TransportOrder
     */
    public TransportOrder $transportOrder;

    /**
     * Collection of all the selected vehicles for concrete transport order.
     *
     * @var Collection
     */
    private Collection $selectedTransportables;

    /**
     * Array holding vehicle ids that will be automatically selected in create.
     *
     * @var array
     */
    private array $queryVehicleIds;

    /**
     * Create a new TransportOrderService instance.
     */
    public function __construct()
    {
        $this->setTransportOrder(new TransportOrder());
    }

    /**
     * Collection of DataTable<TransportOrder>.
     *
     * @return DataTable
     */
    public function getIndexMethodTable(): DataTable
    {
        return (new DataTable(
            TransportOrder::inThisCompany()->with([
                'statuses',
            ])->select(TransportOrder::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setRelation('transporter', ['id', 'full_name', 'company_id'])
            ->setRelation('transportCompany', ['id', 'name'])
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('vehicle_type', __('Vehicle Type'), true, true)
            ->setColumn('transport_type', __('Transport Type'), true, true)
            ->setColumn('transporter.full_name', __('Transport Company'), true, true)
            ->setColumn('transportCompany.name', __('Transport Company'), true, true)
            ->setColumn('total_transport_price', __('Total Transport Price'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', TransportOrderStatus::class)
            ->setEnumColumn('transport_type', TransportType::class)
            ->setEnumColumn('vehicle_type', TransportableType::class)
            ->setPriceColumn('total_transport_price');
    }

    /**
     * Set the model of the transportOrder.
     *
     * @param  TransportOrder $transportOrder
     * @return self
     */
    public function setTransportOrder(TransportOrder $transportOrder): self
    {
        $this->transportOrder = $transportOrder;

        return $this;
    }

    /**
     * Get the model of the transportOrder.
     *
     * @return TransportOrder
     */
    private function getTransportOrder(): TransportOrder
    {
        return $this->transportOrder;
    }

    /**
     * Transport order creation.
     *
     * @param StoreTransportOrderRequest $request
     * @return self
     */
    public function createTransportOrder(StoreTransportOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $transportOrder = new TransportOrder();
        $transportOrder->fill($validatedRequest);
        $transportOrder->creator_id = auth()->id();

        $transportOrder->save();
        $this->setTransportOrder($transportOrder);
        $transportOrder->sendInternalRemarks($validatedRequest);

        $this->syncTransportables($validatedRequest['transportables'] ?? []);

        $transportOrder
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'files'), 'files')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'transportInvoiceFiles'), 'transportInvoiceFiles')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'cmrWaybillFiles'), 'cmrWaybillFiles');

        $this->setTransportOrder($transportOrder);

        return $this;
    }

    /**
     * Transport order update.
     *
     * @param UpdateTransportOrderRequest $request
     * @return self
     */
    public function updateTransportOrder(UpdateTransportOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $transportOrder = $this->getTransportOrder();

        $transportOrder->update($validatedRequest);
        $transportOrder->sendInternalRemarks($validatedRequest);

        $relationMethod = $transportOrder->typeRelationMap[$transportOrder->vehicle_type->value];
        $transportOrder->{$relationMethod}()->whereNotIn('id', $validatedRequest['transportableIds'] ?? [])->detach();
        $this->syncTransportables($validatedRequest['transportables'] ?? []);

        $transportOrder
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'files'), 'files')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'transportInvoiceFiles'), 'transportInvoiceFiles')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'cmrWaybillFiles'), 'cmrWaybillFiles');

        $this->setTransportOrder($transportOrder);

        return $this;
    }

    /**
     * Get the value of queryVehicleIds.
     *
     * @return array
     */
    public function getQueryVehicleIds(): array
    {
        if (! isset($this->queryVehicleIds)) {
            $this->setQueryVehicleIds();
        }

        return $this->queryVehicleIds;
    }

    /**
     * Get the value of queryVehicleIds.
     *
     * @return array
     */
    public function generateTransportOrderPickUpAuthorizationFile(): array
    {
        if (! isset($this->queryVehicleIds)) {
            $this->setQueryVehicleIds();
        }

        return $this->queryVehicleIds;
    }

    /**
     * Set the value of queryVehicleIds.
     *
     * @return void
     */
    private function setQueryVehicleIds(): void
    {
        $selectedVehicleIds = array_filter([
            request()->input('filter.id'),
            ...request()->input('filter.ids', []),
        ]);

        $this->queryVehicleIds = array_values(array_map('intval', $selectedVehicleIds));
    }

    /**
     * Update transportables relation.
     *
     * @param       $transportables
     * @return void
     */
    private function syncTransportables($transportables): void
    {
        $transportOrder = $this->getTransportOrder();
        $transportOrderId = $transportOrder->id;
        $transportableClass = $transportOrder->typeRelationClassMap[$transportOrder->vehicle_type->value];

        foreach ($transportables as &$transportable) {
            $transportable['transport_order_id'] = $transportOrderId;
            $transportable['transportable_type'] = $transportableClass;
            $transportable['price'] = CurrencyHelper::convertCurrencyToUnits($transportable['price']);
        }

        Transportable::upsert(
            $transportables,
            ['transportable_type', 'transportable_id', 'transport_order_id'],
            ['location_id', 'location_free_text', 'price']
        );
    }

    public function initTransportablesDataTable(): DataTable|array
    {
        $transportOrder = $this->getTransportOrder();
        $userHasSearched = request()->input('filter.global') ?? (int) request(null)->input('filter.id');
        $queryVehicleIds = $this->getQueryVehicleIds();

        $selectedTransportType = request()->input('transport_type', $transportOrder->transport_type?->value);
        $selectedVehicleType = request()->input('vehicle_type', $transportOrder->vehicle_type?->value);
        if (! $selectedVehicleType) {
            return [];
        }

        $selectedTransportableIds = $this->getSelectedTransportables()->pluck('id')->toArray();
        $ids = $selectedTransportableIds ?: $queryVehicleIds;
        $rawOrdering = $ids ? new RawOrdering('FIELD(id, '.implode(',', $ids).') DESC') : null;

        switch ($selectedTransportType) {
            case TransportType::Inbound->value:
                $relationsToLoad = ['supplier.company.logisticsAddresses' => []];
                $additionalSelectFields = ['supplier_id'];

                break;
            case TransportType::Outbound->value:
                $relationsToLoad = ['salesOrder.customer.company.logisticsAddresses' => []];
                $additionalSelectFields = [];

                break;
            case TransportType::Other->value:
                $relationsToLoad = [];
                $additionalSelectFields = [];

                break;
            default:
                throw new Exception('Type not provided or invalid', 404);
        }

        switch ($selectedVehicleType) {
            case TransportableType::Vehicles->value:
                $dataTable = (new SystemVehicleService())->getIndexMethodTable(array_merge(['calculation' => [
                    'vehicleable_type', 'vehicleable_id', 'net_purchase_price', 'total_purchase_price', 'fee_intermediate',
                    'sales_price_net', 'sales_price_total', 'leges_vat', 'transport_inbound', 'transport_outbound', 'costs_of_damages',
                    'vat', 'bpm', 'sales_price_incl_vat_or_margin',
                ]], $relationsToLoad), true, $additionalSelectFields);

                break;
            case TransportableType::Pre_order_vehicles->value:
                if ($selectedTransportType == TransportType::Outbound->value) {
                    unset($relationsToLoad['salesOrder.customer.company.logisticsAddresses']);
                }
                $dataTable = (new PreOrderVehicleService())->getIndexMethodTable($relationsToLoad, true, $additionalSelectFields);

                break;
            case TransportableType::Service_vehicles->value:
                $dataTable = ServiceVehicleService::getDataTable(true, true)
                    ->setRelation('serviceOrder.customer.company.logisticsAddresses');

                break;
            default:
                throw new Exception('Type not provided or invalid', 404);
        }

        if ($rawOrdering) {
            $dataTable->setRawOrdering($rawOrdering);
        }

        return $dataTable->run(
            config('app.default.pageResults', 10),
            function ($query) use ($queryVehicleIds, $selectedTransportableIds, $transportOrder, $selectedTransportType, $userHasSearched) {
                if ($selectedTransportType && ($userHasSearched || $queryVehicleIds)) {
                    return $query->withoutTrashed()->when($selectedTransportType != TransportType::Other->value, function ($query) use ($selectedTransportType) {
                        $query->whereDoesntHave('transportOrders', function ($subQuery) use ($selectedTransportType) {
                            $subQuery?->where('transport_type', '=', $selectedTransportType);
                        });
                    });
                }

                if ($transportOrder->id) {
                    return $query->whereIn('id', $selectedTransportableIds);
                }

                return $query->whereNull('id');
            }
        );
    }

    /**
     * Get the value of selectedTransportables.
     *
     * @return Collection
     */
    public function getSelectedTransportables(): Collection
    {
        if (! isset($this->selectedTransportables)) {
            $this->setSelectedTransportables();
        }

        return $this->selectedTransportables;
    }

    /**
     * Set the value of selectedTransportables.
     *
     * @return void
     */
    private function setSelectedTransportables(): void
    {
        $transportOrder = $this->getTransportOrder();

        $transportVehicleType = $transportOrder->vehicle_type?->value;
        if (! $transportVehicleType) {
            $this->selectedTransportables = collect();

            return;
        }

        $relationMethod = $transportOrder->typeRelationMap[$transportVehicleType];
        $this->selectedTransportables = $transportOrder->{$relationMethod}()->withTrashed()->select('id')->get();
    }

    /**
     * Update the transport order status.
     *
     * @param UpdateStatusRequest $request
     * @return TransportOrderService
     * @throws Exception
     */
    public function updateTransportOrderStatus(UpdateStatusRequest $request): self
    {
        $validatedRequest = $request->validated();

        $transportOrder = $this->getTransportOrder();

        switch ($validatedRequest['status']) {
            case TransportOrderStatus::Offer_requested->value:
                $this->generateTransportRequestOrTransportOrderPdf($validatedRequest['locale'], 'transport-order');

                break;
            case TransportOrderStatus::Issued->value:
                if (! $transportOrder->transport_company_use && $this->getSelectedTransportables()->isEmpty()) {
                    throw new Exception(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                break;
            default:
                break;
        }

        $transportOrder->status = $validatedRequest['status'];
        $transportOrder->save();

        if (1 != $validatedRequest['status']) {
            $transportOrder->statuses()->updateOrCreate(['status' => $validatedRequest['status']], ['created_at' => now()]);
        }

        return $this;
    }

    /**
     * Generate an addresses array. Summary that is used to build the transport order PDF.
     *
     * @param  Collection $selectedTransportables
     * @return array
     */
    private function generateAddressesArray(Collection $selectedTransportables): array
    {
        $transportOrder = $this->getTransportOrder();

        $addressesArray = [
            'pickUp' => [
                'addressesCount' => 0,
            ],
            'delivery' => [
                'addressesCount' => 0,
            ],
            'averageVehicleTransportPrice' => CurrencyHelper::convertUnitsToCurrency(CurrencyHelper::convertCurrencyToUnits($transportOrder->total_transport_price) / $selectedTransportables->count()),
            'addresses'                    => [],
        ];

        switch ($transportOrder->transport_type->value) {
            case TransportType::Inbound->value:
                $deliveryAddress = '';
                if ($transportOrder->delivery_location_id) {
                    $addressesArray['delivery']['addressesCount'] = 1;
                    $deliveryAddress = $transportOrder->deliveryLocation?->address;
                }

                $addressesCollectionCount = $selectedTransportables
                    ->map(fn ($vehicle) => $vehicle->pivot->location_id)
                    ->countBy();

                $addressesArray['pickUp']['addressesCount'] = count($addressesCollectionCount);

                $authCompany = auth()->user()->company->load(['logisticsContact:id,full_name']);
                foreach ($addressesCollectionCount as $addressId => $vehiclesCount) {
                    $vehicles = $selectedTransportables->filter(function ($vehicle) use ($addressId) {
                        return $addressId == $vehicle->pivot->location_id;
                    });

                    $price = 0;
                    foreach ($vehicles as $vehicle) {
                        $price += CurrencyHelper::convertCurrencyToUnits($vehicle->pivot->price);
                    }

                    $addressesArray['addresses'][] = [
                        'addressId'       => $addressId,
                        'pickUpAddress'   => CompanyAddress::find($addressId)?->address,
                        'deliveryAddress' => $deliveryAddress,
                        'vehiclesCount'   => $vehiclesCount,
                        'vehicles'        => $vehicles,
                        'price'           => CurrencyHelper::convertUnitsToCurrency($price),
                        'averagePrice'    => CurrencyHelper::convertUnitsToCurrency($price / $vehiclesCount),
                        'pickUpCompany'   => $vehicles->first()->supplierCompany ?? $vehicles->first()->serviceOrder?->first()->customerCompany,
                        'deliveryCompany' => $authCompany,
                    ];
                }

                break;
            case TransportType::Outbound->value:
                $pickUpAddress = '';
                if ($transportOrder->pick_up_location_id) {
                    $addressesArray['pickUp']['addressesCount'] = 1;
                    $pickUpAddress = $transportOrder->pickUpLocation?->address;
                }

                $addressesCollectionCount = $selectedTransportables
                    ->map(fn ($vehicle) => $vehicle->pivot->location_id)
                    ->countBy();

                $addressesArray['delivery']['addressesCount'] = count($addressesCollectionCount);

                $authCompany = auth()->user()->company->load(['logisticsContact:id,full_name']);
                foreach ($addressesCollectionCount as $addressId => $vehiclesCount) {
                    $vehicles = $selectedTransportables->filter(function ($vehicle) use ($addressId) {
                        return $addressId == $vehicle->pivot->location_id;
                    });

                    $price = 0;
                    foreach ($vehicles as $vehicle) {
                        $price += CurrencyHelper::convertCurrencyToUnits($vehicle->pivot->price);
                    }

                    $addressesArray['addresses'][] = [
                        'addressId'       => $addressId,
                        'deliveryAddress' => CompanyAddress::find($addressId)?->address,
                        'pickUpAddress'   => $pickUpAddress,
                        'vehiclesCount'   => $vehiclesCount,
                        'vehicles'        => $vehicles,
                        'price'           => CurrencyHelper::convertUnitsToCurrency($price),
                        'averagePrice'    => CurrencyHelper::convertUnitsToCurrency($price / $vehiclesCount),
                        'pickUpCompany'   => $authCompany,
                        'deliveryCompany' => $vehicles->first()->salesOrder?->first()->customerCompany ?? $vehicles->first()->serviceOrder?->first()->customerCompany,
                    ];
                }

                break;
            case TransportType::Other->value:
                $pickUpAddress = '';
                $deliveryAddress = '';
                if ($transportOrder->pick_up_location_id) {
                    $addressesArray['pickUp']['addressesCount'] = 1;
                    $pickUpAddress = $transportOrder->pickUpLocation?->address;
                }

                if ($transportOrder->delivery_location_id) {
                    $addressesArray['delivery']['addressesCount'] = 1;
                    $deliveryAddress = $transportOrder->deliveryLocation?->address;
                }

                $addressesArray['addresses'][] = [
                    'deliveryAddress' => $deliveryAddress,
                    'pickUpAddress'   => $pickUpAddress,
                    'vehiclesCount'   => $selectedTransportables->count(),
                    'vehicles'        => $selectedTransportables,
                    'price'           => $transportOrder->total_transport_price,
                    'averagePrice'    => CurrencyHelper::convertUnitsToCurrency(CurrencyHelper::convertCurrencyToUnits($transportOrder->total_transport_price) / $selectedTransportables->count()),
                    'pickUpCompany'   => $transportOrder->pickUpCompany,
                    'deliveryCompany' => $transportOrder->deliveryCompany,
                ];

                break;
        }

        return $addressesArray;
    }

    /**
     * Generate pick-up authorization for the transport order based on the language provided.
     *
     * @param int $locale
     * @return TransportOrderService
     * @throws Exception
     */
    public function generateTransportOrderPickUpAuthorization(int $locale): static
    {
        $transportOrder = $this->getTransportOrder();

        $localeService = (new LocaleService())->checkChangeToLocale($locale);

        switch ($transportOrder->vehicle_type->value) {
            case TransportableType::Vehicles->value:
                if ($transportOrder->vehicles->isEmpty()) {
                    throw new Exception(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                $selectedTransportables = $transportOrder->vehicles()
                    ->withTrashed()
                    ->select('id', 'make_id', 'vehicle_model_id', 'engine_id', 'vin', 'nl_registration_number')
                    ->with(['make:id,name', 'vehicleModel:id,name', 'engine:id,name'])
                    ->get();

                break;
            case TransportableType::Pre_order_vehicles->value:
                if ($transportOrder->preOrderVehicles->isEmpty()) {
                    throw new Exception(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                $selectedTransportables = $transportOrder->preOrderVehicles()
                    ->withTrashed()
                    ->select('id', 'make_id', 'vehicle_model_id', 'engine_id')
                    ->with(['make:id,name', 'vehicleModel:id,name', 'engine:id,name'])
                    ->get();

                break;
            case TransportableType::Service_vehicles->value:
                if ($transportOrder->serviceVehicles->isEmpty()) {
                    throw new Exception(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                $selectedTransportables = $transportOrder->serviceVehicles()
                    ->withTrashed()
                    ->select('id', 'make_id', 'vehicle_model_id', 'vin', 'nl_registration_number')
                    ->with(['make:id,name', 'vehicleModel:id,name', 'variant:id,name'])
                    ->get();

                break;
            default:
                throw new Exception();
        }

        $transportOrder->load([
            'creator.company' => function ($query) {
                $query->select(
                    'id',
                    'name',
                    'address',
                    'iban',
                    'swift_or_bic',
                    'bank_name',
                    'vat_number',
                    'kvk_number',
                    'country',
                    'city',
                    'vat_number',
                )
                    ->withTrashed();
            },
            'pickUpLocation:id,address',
            'transportCompany' => function ($query) {
                $query->select('id', 'name', 'address', 'postal_code', 'city', 'country', 'vat_number', 'debtor_number_accounting_system')->withTrashed();
            },
        ]);

        $authCompany = auth()->user()->company->load(['logisticsContact:id,full_name,email,mobile']);

        $companyPdfAssets = CompanyService::getPdfAssets();

        $pdfService = new PdfService(
            'templates/transport-orders/pick-up-authorization',
            [
                'transportOrder'                          => $transportOrder,
                'selectedTransportables'                  => $selectedTransportables,
                'authCompany'                             => $authCompany,
                'headerQuoteTransportAndDeclarationImage' => $companyPdfAssets['pdf_header_quote_transport_and_declaration_image'],
                'signatureImage'                          => $companyPdfAssets['pdf_signature_image'],
            ],
            [
                'fontDir' => storage_path('fonts'),
            ]
        );

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
        )
                    ->generate(), "pick-up-authorization-{$transportOrder->id}", 'pdf');
        $transportOrder->saveWithFile($generatedPdf, 'generatedPickupAuthorizationPdf');

        $localeService->setOriginalLocale();

        return $this;
    }

    /**
     * Generate pick-up authorization for the transport order based on the language provided.
     *
     * @param int $locale
     * @param string $type
     * @return TransportOrderService
     * @throws Exception
     */
    public function generateTransportRequestOrTransportOrderPdf(int $locale, string $type): static
    {
        $localeService = (new LocaleService())->checkChangeToLocale($locale);

        $transportOrder = $this->getTransportOrder();

        switch ($transportOrder->vehicle_type->value) {
            case TransportableType::Vehicles->value:
                if ($transportOrder->vehicles->isEmpty()) {
                    throw new Exception(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                $selectedTransportables = $transportOrder->vehicles()
                    ->withTrashed()
                    ->select('id', 'creator_id', 'make_id', 'vehicle_model_id', 'vin', 'supplier_company_id')
                    ->with([
                        'creator',
                        'make:id,name',
                        'vehicleModel:id,name',
                        'transportOrders',
                        'supplierCompany:id,logistics_times,logistics_remarks,logistics_contact_id',
                        'supplierCompany.logisticsContact:id,full_name',
                        'salesOrder:id,customer_company_id',
                        'salesOrder.customerCompany:id,logistics_times,logistics_remarks,logistics_contact_id',
                        'salesOrder.customerCompany.logisticsContact:id,full_name',
                    ])
                    ->get();

                break;
            case TransportableType::Pre_order_vehicles->value:
                if ($transportOrder->preOrderVehicles->isEmpty()) {
                    throw new Exception(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                $selectedTransportables = $transportOrder->preOrderVehicles()
                    ->withTrashed()
                    ->select('id', 'creator_id', 'make_id', 'vehicle_model_id', 'supplier_company_id')
                    ->with([
                        'creator', 'make:id,name', 'vehicleModel:id,name',
                        'supplierCompany:id,logistics_times,logistics_remarks,logistics_contact_id',
                        'supplierCompany.logisticsContact:id,full_name',
                    ])
                    ->get();

                break;
            case TransportableType::Service_vehicles->value:
                if ($transportOrder->serviceVehicles->isEmpty()) {
                    throw new Exception(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                $selectedTransportables = $transportOrder->serviceVehicles()
                    ->withTrashed()
                    ->select('id', 'creator_id', 'make_id', 'vehicle_model_id', 'vin')
                    ->with([
                        'creator', 'make:id,name', 'vehicleModel:id,name',
                        'serviceOrder.customerCompany:id,logistics_times,logistics_remarks,logistics_contact_id',
                        'serviceOrder.customerCompany.logisticsContact:id,full_name',
                    ])
                    ->get();

                break;
            default:
                throw new Exception();
        }

        $transportOrder->load([
            'pickUpLocation:id,address', 'deliveryLocation:id,address',
            'transportCompany' => function ($query) {
                $query->select(
                    'id',
                    'name',
                    'address',
                    'postal_code',
                    'city',
                    'country',
                    'vat_number',
                )->withTrashed();
            },
            'pickUpCompany:id,logistics_times,logistics_remarks,logistics_contact_id',
            'pickUpCompany.logisticsContact:id,full_name',
            'deliveryCompany:id,logistics_times,logistics_remarks,logistics_contact_id',
            'deliveryCompany.logisticsContact:id,full_name',
        ]);

        $this->setTransportOrder($transportOrder);

        $companyPdfAssets = CompanyService::getPdfAssets();

        $pdfService = new PdfService(
            "templates/transport-orders/{$type}",
            [
                'transportOrder'                          => $transportOrder,
                'selectedTransportablesCount'             => $selectedTransportables->count(),
                'addressesArray'                          => $this->generateAddressesArray($selectedTransportables),
                'headerQuoteTransportAndDeclarationImage' => $companyPdfAssets['pdf_header_quote_transport_and_declaration_image'],
                'signatureImage'                          => $companyPdfAssets['pdf_signature_image'],
            ],
            [
                'fontDir' => storage_path('fonts'),
            ]
        );

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
        )
                ->generate(), "{$type}-{$transportOrder->id}", 'pdf');
        $transportOrder->saveWithFile($generatedPdf, 'generatedTransportRequestOrTransportOrderPdf');

        $localeService->setOriginalLocale();

        return $this;
    }

    /**
     * Generate pick up authorization for the transport order based on the language provided.
     *
     * @param int $locale
     * @return TransportOrderService
     * @throws TransportOrderException
     */
    public function generateStickervel(int $locale): static
    {
        $transportOrder = $this->getTransportOrder();

        $localeService = (new LocaleService())->checkChangeToLocale($locale);

        switch ($transportOrder->vehicle_type->value) {
            case TransportableType::Vehicles->value:
                if ($transportOrder->vehicles->isEmpty()) {
                    throw new Exception(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                $selectedTransportables = $transportOrder->vehicles()
                    ->withTrashed()
                    ->select('id', 'make_id', 'vehicle_model_id', 'vin', 'specific_exterior_color', 'kilometers')
                    ->with([
                        'creator', 'make:id,name', 'vehicleModel:id,name',
                        'salesOrder:id,customer_company_id',
                        'salesOrder.customerCompany:id,name',
                    ])->get();

                foreach ($selectedTransportables as $vehicle) {
                    $vehicle->customerName = $vehicle->salesOrder?->first()?->customerCompany?->name;
                }

                break;
            case TransportableType::Service_vehicles->value:
                if ($transportOrder->serviceVehicles->isEmpty()) {
                    throw new Exception(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                $selectedTransportables = $transportOrder->serviceVehicles()
                    ->withTrashed()
                    ->select('id', 'make_id', 'vehicle_model_id', 'vin', 'kilometers')
                    ->with(['creator', 'make:id,name', 'vehicleModel:id,name',
                        'serviceOrder:id,customer_company_id',
                        'serviceOrder.customerCompany:id,name',
                    ])->get();

                foreach ($selectedTransportables as $vehicle) {
                    $vehicle->customerName = $vehicle->serviceOrder?->first()?->customerCompany?->name;
                }

                break;
            default:
                throw new Exception();
        }

        foreach ($selectedTransportables as $vehicle) {
            if (! $vehicle->vin) {
                throw new TransportOrderException(__('One of the vehicles does not have VIN.'));
            }

            $companyPdfAssets = CompanyService::getPdfAssets();

            $pdfService = new PdfService(
                'templates/transport-orders/stickervel',
                [
                    'transportOrder'        => $transportOrder,
                    'vehicle'               => $vehicle,
                    'lastFourVinCharacters' => substr($vehicle->vin, -4) ?? '',
                    'stickerImage'          => $companyPdfAssets['pdf_sticker_image'],
                ],
                [
                    'fontDir' => storage_path('fonts'),
                ]
            );

            $generatedPdf = UploadHelper::uploadGeneratedFile($pdfService->generate(), "vehicle-{$vehicle->id}-stickers", 'pdf');
            $transportOrder->saveWithFile($generatedPdf, 'generatedStickervelPdf');
        }

        $localeService->setOriginalLocale();

        return $this;
    }

    /**
     * Return datatable of Transport Orders by provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getTransportOrdersDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(TransportOrder::$defaultSelectFields)
        ))
            ->setRelation('transportCompany', ['id', 'name'])
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('status', __('Status'), true, true)
            ->setColumn('vehicle_type', __('Vehicle Type'), true, true)
            ->setColumn('transport_type', __('Transport Type'), true, true)
            ->setColumn('transportCompany.name', __('Transport Company'), true, true)
            ->setColumn('total_transport_price', __('Total Transport Price'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', TransportOrderStatus::class)
            ->setEnumColumn('transport_type', TransportType::class)
            ->setEnumColumn('vehicle_type', TransportableType::class)
            ->setPriceColumn('total_transport_price');

        return $dataTable;
    }
}
