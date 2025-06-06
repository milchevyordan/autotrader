<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Coc;
use App\Enums\Currency;
use App\Enums\PurchaseOrderStatus;
use App\Enums\VehicleStock;
use App\Exceptions\PurchaseOrderException;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Calculation;
use App\Models\PurchaseOrder;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;
use App\Services\DataTable\RawOrdering;
use App\Services\Files\FileManager;
use App\Services\Files\UploadHelper;
use App\Services\Pdf\Canvas\CanvasImageRenderer;
use App\Services\Pdf\Canvas\CanvasTextRenderer;
use App\Services\Pdf\Pagination\Paginator;
use App\Services\Pdf\PdfService;
use App\Services\Vehicles\SystemVehicleService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PurchaseOrderService extends Service
{
    /**
     * The purchase order model.
     *
     * @var PurchaseOrder
     */
    public PurchaseOrder $purchaseOrder;

    /**
     * DataTable of vehicles datatable structure.
     *
     * @var DataTable<Vehicle>
     */
    private DataTable $vehicleDataTable;

    /**
     * Array holding vehicle ids that will be automatically selected in creation.
     *
     * @var array
     */
    private array $queryVehicleIds;

    /**
     * Collection of all the vehicles for the selected purchase order.
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
     * Create a new PurchaseOrderService instance.
     */
    public function __construct()
    {
        $this->setPurchaseOrder(new PurchaseOrder());
    }

    /**
     * Collection of DataTable<PurchaseOrder>.
     *
     * @return DataTable
     */
    public function getIndexMethodTable(): DataTable
    {
        return (new DataTable(
            PurchaseOrder::inThisCompany()->with(['statuses'])->select(PurchaseOrder::$defaultSelectFields)
        ))
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('purchaser', ['id', 'full_name'])
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('supplierCompany.name', __('Supplier Company'), true, true)
            ->setColumn('purchaser.full_name', __('Company Purchaser'), true)
            ->setTimestamps()
            ->setEnumColumn('status', PurchaseOrderStatus::class);
    }

    /**
     * Get the value of vehicleDataTable structure.
     *
     * @return DataTable<Vehicle>
     */
    public function getVehicleDataTable(): DataTable
    {
        if (! isset($this->vehicleDataTable)) {
            $this->setVehicleDataTable();
        }

        return $this->vehicleDataTable;
    }

    /**
     * Set the value of the vehicleDataTable structure.
     *
     * @return void
     */
    private function setVehicleDataTable(): void
    {
        $this->vehicleDataTable = (new DataTable(
            Vehicle::inThisCompany()
                ->withTrashed()
                ->select(array_merge(Vehicle::$defaultSelectFields, ['expected_date_of_availability_from_supplier', 'supplier_company_id', 'coc']))
        ))
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('engine', ['id', 'name'])
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('calculation', Calculation::$purchaseOrderSelectFields)
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', __('VIN'), true, true)
            ->setColumn('supplierCompany.name', __('Supplier'), true, true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('engine.name', __('Engine'), true, true)
            ->setColumn('hp', __('HP'), true, true)
            ->setColumn('calculation.currency_exchange_rate', __('Currency exchange rate'), true, true)
            ->setColumn('calculation.original_currency', __('Original Currency'), true, true)
            ->setColumn('calculation.net_purchase_price', __('Purchase price').' '.(new CacheService())->getUserCompanyCurrency(Auth::user()), true, true)
            ->setColumn('calculation.fee_intermediate', __('Fee intermediate'), true, true)
            ->setColumn('calculation.total_purchase_price', __('Purchase price'), true, true)
            ->setColumn('calculation.sales_price_total', __('Sales Price Total'), true, true)
            ->setColumn('calculation.bpm', __('BPM'), true, true)
            ->setColumn('expected_date_of_availability_from_supplier', __('Expected date of availability'), true, true)
            ->setColumn('coc', __('COC'), true, true)
            ->setEnumColumn('original_currency', Currency::class)
            ->setEnumColumn('coc', Coc::class)
            ->setPriceColumn('total_purchase_price')
            ->setPriceColumn('sales_price_total');
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
     * Get the vehicle datatable shown in create form.
     *
     * @return DataTable
     */
    public function getCreateVehicles(): DataTable
    {
        $selectedSupplierCompanyId = request(null)->input('supplier_company_id');

        $queryVehicleIds = $this->getQueryVehicleIds();
        $rawOrdering = $queryVehicleIds ? new RawOrdering('FIELD(id, '.implode(',', $queryVehicleIds).') DESC') : null;

        $dataTable = $this->getVehicleDataTable();

        if ($rawOrdering) {
            $dataTable->setRawOrdering($rawOrdering);
        }

        return $dataTable->run(10, function ($model) use ($selectedSupplierCompanyId) {
            $query = $model->withoutTrashed()->whereDoesntHave('purchaseOrder');

            return $selectedSupplierCompanyId ?
                $query->whereHas('supplierCompany', function ($subQuery) use ($selectedSupplierCompanyId) {
                    $subQuery->where('id', $selectedSupplierCompanyId);
                }) : $query;
        });
    }

    /**
     * Get the model of the purchaseOrder.
     *
     * @return PurchaseOrder
     */
    private function getPurchaseOrder(): PurchaseOrder
    {
        return $this->purchaseOrder;
    }

    /**
     * Set the model of the purchaseOrder.
     *
     * @param  PurchaseOrder $purchaseOrder
     * @return self
     */
    public function setPurchaseOrder(PurchaseOrder $purchaseOrder): self
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    /**
     * Purchase order creation.
     *
     * @param StorePurchaseOrderRequest $request
     * @return self
     */
    public function createPurchaseOrder(StorePurchaseOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->fill($validatedRequest);
        $purchaseOrder->creator_id = auth()->id();

        $purchaseOrder->save();
        $purchaseOrder->sendInternalRemarks($validatedRequest);

        $purchaseOrder->vehicles()->sync($validatedRequest['vehicleIds'] ?? []);

        UploadHelper::handleFileUploads($purchaseOrder, $validatedRequest, $this->fileTypes);
        $this->setPurchaseOrder($purchaseOrder);

        return $this;
    }

    /**
     * Get the vehicles datatable shown in edit form.
     *
     * @return DataTable<Vehicle>
     */
    public function getEditVehicles(): DataTable
    {
        $userHasSearched = request(null)->input('filter.global');
        $purchaseOrder = $this->getPurchaseOrder();
        $selectedSupplierCompanyId = request(null)->input('supplier_company_id', $purchaseOrder->supplier_company_id);

        $dataTable = $this->getVehicleDataTable();

        if ($selectedVehiclesIds = $this->getSelectedVehicles()->pluck('id')->toArray()) {
            $dataTable->setRawOrdering(new RawOrdering('FIELD(vehicles.id, '.implode(',', $selectedVehiclesIds).') DESC'));
        }

        return $dataTable->run(config('app.default.pageResults', 10), function ($model) use ($userHasSearched, $selectedSupplierCompanyId, $purchaseOrder) {
            $vehiclesToShow = $model->whereHas('purchaseOrder', function ($query) use ($purchaseOrder) {
                $query->where('purchase_orders.id', $purchaseOrder->id);
            });

            return (! $userHasSearched || PurchaseOrderStatus::Concept != $purchaseOrder->status) ? $vehiclesToShow : $vehiclesToShow->orWhere(function ($query) use ($selectedSupplierCompanyId) {
                return $query->withoutTrashed()->whereDoesntHave('purchaseOrder')
                    ->whereHas('supplierCompany', function ($subQuery) use ($selectedSupplierCompanyId) {
                        $subQuery->where('id', $selectedSupplierCompanyId);
                    });
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
        $this->selectedVehicles = $this->getPurchaseOrder()
            ->vehicles()
            ->withTrashed()
            ->select(
                'id',
                'make_id',
                'vehicle_model_id'
            )
            ->with(['make:id,name', 'vehicleModel:id,name', 'calculation'])
            ->get();
    }

    /**
     * Purchase order update.
     *
     * @param UpdatePurchaseOrderRequest $request
     * @return self
     */
    public function updatePurchaseOrder(UpdatePurchaseOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $purchaseOrder = $this->getPurchaseOrder();

        $purchaseOrder->update($validatedRequest);
        $purchaseOrder->sendInternalRemarks($validatedRequest);

        $purchaseOrder->vehicles()->sync($validatedRequest['vehicleIds'] ?? []);

        UploadHelper::handleFileUploads($purchaseOrder, $validatedRequest, $this->fileTypes);
        $this->setPurchaseOrder($purchaseOrder);

        return $this;
    }

    /**
     * Update the status of the purchase order.
     *
     * @param UpdateStatusRequest $request
     * @return PurchaseOrderService
     * @throws PurchaseOrderException
     */
    public function updatePurchaseOrderStatus(UpdateStatusRequest $request): self
    {
        $validatedRequest = $request->validated();

        $purchaseOrder = $this->getPurchaseOrder();

        $purchaseOrder->load('vehicles:id');

        switch ($validatedRequest['status']) {
            case PurchaseOrderStatus::Submitted->value:
                if (! auth()->user()->can('submit-purchase-order')) {
                    throw new PurchaseOrderException(__('You do not have the permission.'));
                }

                if ($purchaseOrder->vehicles->isEmpty()) {
                    throw new PurchaseOrderException(__('Not selected any vehicles, need to select at least one Vehicle.'));
                }

                break;
            case PurchaseOrderStatus::Approved->value:
            case PurchaseOrderStatus::Rejected->value:
                if (! auth()->user()->can('approve-purchase-order')) {
                    throw new PurchaseOrderException(__('You do not have the permission.'));
                }

                break;
            case PurchaseOrderStatus::Sent_to_supplier->value:
                $localeService = (new LocaleService())->checkChangeToLocale($validatedRequest['locale']);
                $fileManager = new FileManager();

                $purchaseOrder->load([
                    'creator.company:id,name,address,postal_code,city,country,vat_number,kvk_number,logistics_times,logistics_remarks,email,phone',
                    'creator.company.addresses',
                    'supplier:id,full_name',
                    'supplierCompany' => function ($query) {
                        $query->select('id', 'name', 'postal_code', 'city', 'country', 'vat_number')->withTrashed();
                    },
                    'intermediary:id,full_name',
                    'intermediaryCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                    'purchaser:id,full_name',
                    'vehicles' => function ($query) {
                        $query->withTrashed();
                    },
                    'vehicles.make:id,name', 'vehicles.vehicleModel:id,name', 'vehicles.variant:id,name',
                    'vehicles.engine:id,name', 'vehicles.calculation:'.implode(',', Calculation::$pdfSelectFields),
                ]);
                $currencyService = new CacheService();

                $coc = false;
                foreach ($purchaseOrder->vehicles as $vehicle) {
                    if (in_array($vehicle->coc?->name, ['Yes', 'Requested', 'Unknown'], true)) {
                        $coc = true;
                    }
                }

                $companyPdfAssets = CompanyService::getPdfAssets();

                $pdfService = new PdfService('templates/purchase-order', [
                    'purchaseOrder'                    => $purchaseOrder,
                    'defaultCurrency'                  => $currencyService->getUserCompanyCurrency(Auth::user()),
                    'headerPrePurchaseSalesOrderImage' => $companyPdfAssets['pdf_header_pre_purchase_sales_order_image'],
                    'signatureImage'                   => $companyPdfAssets['pdf_signature_image'],
                    'coc'                              => $coc,
                ], [
                    'fontDir' => storage_path('fonts'),
                ]);

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
                )->generate(), "purchase-order-{$purchaseOrder->id}", 'pdf');

                $fileToReplace = $purchaseOrder->files()->where('section', 'generatedPdf')->first();
                $fileManager->deleteIfExists($fileToReplace);
                $purchaseOrder->saveWithFile($generatedPdf, 'generatedPdf');

                $localeService->setOriginalLocale();

                break;
            case PurchaseOrderStatus::Uploaded_signed_contract->value:
                if ($purchaseOrder->getGroupedFiles(['contractSignedFiles'])['contractSignedFiles']->isEmpty()) {
                    throw new PurchaseOrderException(__('Not uploaded signed contract, need to upload signed contract.'));
                }

                break;
            case PurchaseOrderStatus::Down_payment_done->value:
                if (! $purchaseOrder->down_payment || $purchaseOrder->down_payment_amount <= 0) {
                    throw new PurchaseOrderException(__('Not saved down payment, need to enter down payment.'));
                }

                break;
            default:
                break;
        }

        $purchaseOrder->status = $validatedRequest['status'];
        $purchaseOrder->save();

        if (1 != $validatedRequest['status']) {
            $purchaseOrder->statuses()->updateOrCreate(['status' => $validatedRequest['status']], ['created_at' => now()]);
        }

        return $this;
    }

    /**
     * Return datatable of Purchase Orders by the provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getPurchaseOrdersDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(PurchaseOrder::$defaultSelectFields)
        ))
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('purchaser', ['id', 'full_name'])
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('status', __('Status'), true, true)
            ->setColumn('supplierCompany.name', __('Supplier Company'), true, true)
            ->setColumn('purchaser.full_name', __('Company Purchaser'), true)
            ->setTimestamps()
            ->setEnumColumn('status', PurchaseOrderStatus::class);

        return $dataTable;
    }
}
