<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CompanyType;
use App\Enums\ImportEuOrWorldType;
use App\Enums\PreOrderStatus;
use App\Http\Requests\StorePreOrderRequest;
use App\Http\Requests\UpdatePreOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Calculation;
use App\Models\Company;
use App\Models\PreOrder;
use App\Models\PreOrderVehicle;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;
use App\Services\DataTable\RawOrdering;
use App\Services\Files\FileManager;
use App\Services\Files\UploadHelper;
use App\Services\Pdf\Canvas\CanvasImageRenderer;
use App\Services\Pdf\Canvas\CanvasTextRenderer;
use App\Services\Pdf\Pagination\Paginator;
use App\Services\Pdf\PdfService;
use App\Support\ModelHelper;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;

class PreOrderService extends Service
{
    /**
     * The pre-order model.
     *
     * @var PreOrder
     */
    public PreOrder $preOrder;

    /**
     * Collection of all the companies for concrete company.
     *
     * @var Collection<Company>
     */
    private Collection $companies;

    /**
     * Collection of all the companies mapped to their default currency.
     *
     * @var Collection<Company>
     */
    private Collection $defaultCurrencies;

    /**
     * DataTable of the vehicles datatable structure.
     *
     * @var DataTable<Vehicle>
     */
    private DataTable $vehiclesDataTable;

    /**
     * Vehicle id that will be automatically selected in create.
     *
     * @var int
     */
    private int $queryVehicleId;

    /**
     * Array holding all file types.
     *
     * @var array|string[]
     */
    private array $fileTypes = [
        'files',
        'contractUnsignedFiles',
        'contractSignedFiles',
        'creditCheckFiles',
        'viesFiles',
    ];

    /**
     * Create a new PreOrderService instance.
     */
    public function __construct()
    {
        $this->setPreOrder(new PreOrder());
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
     * Loads companies and currencies.
     *
     * @return void
     */
    private function loadCompaniesAndCurrencies(): void
    {
        $companiesBuilder = Company::crmCompanies(CompanyType::Supplier->value)
            ->inThisCompany()
            ->select('id', 'name', 'default_currency');

        $defaultCurrencies = $companiesBuilder->get()->pluck('default_currency', 'id');
        $companies = (new MultiSelectService($companiesBuilder))->dataForSelect();

        $this->setCompanies($companies);
        $this->setDefaultCurrencies($defaultCurrencies);
    }

    /**
     * Get the value of companies.
     *
     * @return Collection
     */
    public function getCompanies(): Collection
    {
        if (! isset($this->companies)) {
            $this->loadCompaniesAndCurrencies();
        }

        return $this->companies;
    }

    /**
     * Set the value of companies.
     *
     * @param  Collection $companies
     * @return void
     */
    private function setCompanies(Collection $companies): void
    {
        $this->companies = $companies;
    }

    /**
     * Get the value of default currencies.
     *
     * @return Collection
     */
    public function getDefaultCurrencies(): Collection
    {
        if (! isset($this->defaultCurrencies)) {
            $this->loadCompaniesAndCurrencies();
        }

        return $this->defaultCurrencies;
    }

    /**
     * Set the value of default currencies.
     *
     * @param  Collection $defaultCurrencies
     * @return void
     */
    private function setDefaultCurrencies(Collection $defaultCurrencies): void
    {
        $this->defaultCurrencies = $defaultCurrencies;
    }

    /**
     * Get the vehicles datatable shown in create form.
     *
     * @return DataTable
     */
    public function getCreateVehicles(): DataTable
    {
        $userHasSearched = request(null)->input('filter.global');

        $dataTable = $this->getVehiclesDataTable();

        if ($queryVehicleId = $this->getQueryVehicleId()) {
            $dataTable->setRawOrdering(new RawOrdering("FIELD(pre_order_vehicles.id, {$queryVehicleId}) DESC"));
        }

        return $dataTable->run(10, function ($model) use ($userHasSearched) {
            return $userHasSearched ? $model->withoutTrashed()->whereDoesntHave('preOrder') : $model->withoutTrashed();
        });
    }

    /**
     * Get the vehicle datatable shown in edit form.
     *
     * @return DataTable
     */
    public function getEditVehicles(): DataTable
    {
        $userHasSearched = request(null)->input('filter.global');
        $preOrder = $this->getPreOrder();

        return $this->getVehiclesDataTable()
            ->run(config('app.default.pageResults', 10), function ($model) use ($preOrder, $userHasSearched) {
                return (! $preOrder->pre_order_vehicle_id && $userHasSearched) ? $model->where(function ($query) {
                    $query->withoutTrashed()->whereDoesntHave('preOrder');
                }) : $model->where('id', $preOrder->pre_order_vehicle_id);
            });
    }

    /**
     * Get the vehicles datatable structure.
     *
     * @return DataTable
     */
    public function getVehiclesDataTable(): DataTable
    {
        if (! isset($this->vehiclesDataTable)) {
            $this->setVehiclesDataTable();
        }

        return $this->vehiclesDataTable;
    }

    /**
     * Set the vehicles datatable structure.
     *
     * @return void
     */
    private function setVehiclesDataTable(): void
    {
        $this->vehiclesDataTable = (new DataTable(
            PreOrderVehicle::inThisCompany()
                ->withTrashed()
                ->select(PreOrderVehicle::$defaultSelectFields)
        ))
            ->setRelation('engine', ['id', 'name'])
            ->setRelation('make', ['id', 'name'])
            ->setRelation('variant', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('calculation')
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('engine.name', __('Engine'), true, true)
            ->setColumn('calculation.total_purchase_price', __('Purchase Price'), true, true)
            ->setColumn('calculation.sales_price_total', __('Sales Price Total'), true, true)
            ->setTimestamps()
            ->setPriceColumn('total_purchase_price')
            ->setPriceColumn('sales_price_total');
    }

    /**
     * Get the model of the pre-order.
     *
     * @return PreOrder
     */
    private function getPreOrder(): PreOrder
    {
        return $this->preOrder;
    }

    /**
     * Set the model of the pre-order.
     *
     * @param  PreOrder $preOrder
     * @return self
     */
    public function setPreOrder(PreOrder $preOrder): self
    {
        $this->preOrder = $preOrder;

        return $this;
    }

    /**
     * Pre-order creation.
     *
     * @param StorePreOrderRequest $request
     * @return self
     */
    public function createPreOrder(StorePreOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $preOrder = new PreOrder();
        $preOrder->fill($validatedRequest);
        $preOrder->creator_id = auth()->id();
        $preOrder->save();

        $preOrder->sendInternalRemarks($validatedRequest);

        UploadHelper::handleFileUploads($preOrder, $validatedRequest, $this->fileTypes);
        $this->setPreOrder($preOrder);

        return $this;
    }

    /**
     * Pre-order update.
     *
     * @param UpdatePreOrderRequest $request
     * @return self
     */
    public function updatePreOrder(UpdatePreOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $preOrder = $this->getPreOrder();

        $changeLoggerService = new ChangeLoggerService($preOrder);

        $preOrder->update($validatedRequest);
        $preOrder->sendInternalRemarks($validatedRequest);

        UploadHelper::handleFileUploads($preOrder, $validatedRequest, $this->fileTypes);
        $this->setPreOrder($preOrder);

        $changeLoggerService->logChanges($preOrder);

        return $this;
    }

    /**
     * Update the status of the preorder.
     *
     * @param UpdateStatusRequest $request
     * @return PreOrderService
     * @throws Exception
     */
    public function updatePreOrderStatus(UpdateStatusRequest $request): self
    {
        $validatedRequest = $request->validated();

        $preOrder = $this->getPreOrder();
        $changeLoggerService = new ChangeLoggerService($preOrder);

        switch ($validatedRequest['status']) {
            case PreOrderStatus::Submitted->value:
                if (! auth()->user()->can('submit-pre-order')) {
                    throw new Exception(__('You do not have the permission.'));
                }

                if (! $preOrder->pre_order_vehicle_id) {
                    throw new Exception(__('Not selected any vehicles, need to select Vehicle.'));
                }

                break;
            case PreOrderStatus::Approved->value:
            case PreOrderStatus::Rejected->value:
                if (! auth()->user()->can('approve-pre-order')) {
                    throw new Exception(__('You do not have the permission.'));
                }

                break;
            case PreOrderStatus::Sent_to_supplier->value:
                $localeService = (new LocaleService())->checkChangeToLocale($validatedRequest['locale']);

                $preOrder->load([
                    'creator.company:id,name,address,postal_code,city,country,vat_number,kvk_number,logistics_times,logistics_remarks,email,phone',
                    'creator.company.addresses',
                    'supplier:id,full_name',
                    'supplierCompany' => function ($query) {
                        $query->select('id', 'name', 'address', 'postal_code', 'city', 'country', 'vat_number')->withTrashed();
                    },
                    'intermediary:id,full_name',
                    'intermediaryCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                    'purchaser:id,full_name',
                    'preOrderVehicle' => function ($query) {
                        $query->withTrashed();
                    },
                    'preOrderVehicle.engine:id,name', 'preOrderVehicle.make:id,name',
                    'preOrderVehicle.variant:id,name', 'preOrderVehicle.vehicleModel:id,name',
                    'preOrderVehicle.calculation:'.implode(',', Calculation::$pdfSelectFields),
                ]);

                $companyPdfAssets = CompanyService::getPdfAssets();

                $pdfService = new PdfService(
                    'templates/pre-order',
                    [
                        'preOrder'                         => $preOrder,
                        'attributes'                       => ModelHelper::getVehicleAttributes(),
                        'headerPrePurchaseSalesOrderImage' => $companyPdfAssets['pdf_header_pre_purchase_sales_order_image'],
                        'signatureImage'                   => $companyPdfAssets['pdf_signature_image'],
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
                    )->generate(), "pre-order-{$preOrder->id}", 'pdf');

                $fileToReplace = $preOrder->files()->where('section', 'generatedPdf')->first();
                $fileManager->deleteIfExists($fileToReplace);

                $preOrder->saveWithFile($generatedPdf, 'generatedPdf');

                $localeService->setOriginalLocale();

                break;
            case PreOrderStatus::Uploaded_signed_contract->value:
                if ($preOrder->getGroupedFiles(['contractSignedFiles'])['contractSignedFiles']->isEmpty()) {
                    throw new Exception(__('Not uploaded signed contract, need to upload signed contract.'));
                }

                break;
            case PreOrderStatus::Down_payment_done->value:
                if (! $preOrder->down_payment || $preOrder->down_payment_amount <= 0) {
                    throw new Exception(__('Not saved down payment, need to enter down payment.'));
                }

                break;
            default:
                break;
        }

        $preOrder->status = $validatedRequest['status'];
        $preOrder->save();

        if (1 != $validatedRequest['status']) {
            $preOrder->statuses()->updateOrCreate(['status' => $validatedRequest['status']], ['created_at' => now()]);
        }

        $changeLoggerService->logChanges($preOrder);

        return $this;
    }

    /**
     * Return dataTable of PreOrders by provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getPreOrdersDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(PreOrder::$defaultSelectFields)
        ))
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('purchaser', ['id', 'company_id'])
            ->setRelation('purchaser.company', ['id', 'name'])
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('status', __('Status'), true, true)
            ->setColumn('type', __('Purchase Type'), true, true)
            ->setColumn('supplierCompany.name', __('Supplier'), true, true)
            ->setColumn('purchaser.company.name', __('Purchaser'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', PreOrderStatus::class)
            ->setEnumColumn('type', ImportEuOrWorldType::class);

        return $dataTable;
    }
}
