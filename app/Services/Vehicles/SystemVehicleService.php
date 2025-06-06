<?php

declare(strict_types=1);

namespace App\Services\Vehicles;

use App\Enums\DocumentStatus;
use App\Enums\FuelType;
use App\Enums\MailType;
use App\Enums\OwnershipStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuoteStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\TransportOrderStatus;
use App\Exceptions\VehicleException;
use App\Http\Requests\IdLocaleRequest;
use App\Http\Requests\StoreVehicleDuplicationRequest;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Calculation;
use App\Models\Company;
use App\Models\Document;
use App\Models\Engine;
use App\Models\Make;
use App\Models\Ownership;
use App\Models\PreOrderVehicle;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\SalesOrder;
use App\Models\Statusable;
use App\Models\TransportOrder;
use App\Models\User;
use App\Models\Variant;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Models\VehicleTransferToken;
use App\Models\Workflow;
use App\Notifications\DatabaseNotification;
use App\Notifications\EmailNotification;
use App\Services\ChangeLoggerService;
use App\Services\CompanyService;
use App\Services\DataTable\DataTable;
use App\Services\Events\UpdatedVehicleService;
use App\Services\Files\FileManager;
use App\Services\Files\UploadHelper;
use App\Services\Images\ImageManager;
use App\Services\LocaleService;
use App\Services\MailService;
use App\Services\OwnershipService;
use App\Services\Pdf\Canvas\CanvasImageRenderer;
use App\Services\Pdf\Pagination\Paginator;
use App\Services\Pdf\PdfService;
use App\Services\Vehicles\Tabs\Entered;
use App\Services\Vehicles\Tabs\FilterPage;
use App\Services\Vehicles\Tabs\Inspect;
use App\Services\Vehicles\Tabs\Intake;
use App\Services\Vehicles\Tabs\OnRoute;
use App\Services\Vehicles\Tabs\Overview;
use App\Services\Vehicles\Tabs\PlanningForTransport;
use App\Services\Vehicles\Tabs\StockIndoor;
use App\Services\Vehicles\Tabs\StockOnTheWay;
use App\Services\Vehicles\Tabs\WaitForDocuments;
use App\Services\Vehicles\Tabs\WaitForInspectionPhotos;
use App\Services\Workflow\WorkflowService;
use App\Support\DateHelper;
use App\Support\JsonHelper;
use App\Support\ModelHelper;
use App\Support\StringHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class SystemVehicleService extends BaseVehicleService
{
    /**
     * The vehicle model.
     *
     * @var Vehicle
     */
    public Vehicle $vehicle;

    /**
     * Array holding all tabs used in management page.
     *
     * @const string[]
     */
    private const array TABS = [
        'overview'                => Overview::class,
        'planningForTransport'    => PlanningForTransport::class,
        'onRoute'                 => OnRoute::class,
        'entered'                 => Entered::class,
        'waitForDocuments'        => WaitForDocuments::class,
        'intake'                  => Intake::class,
        'waitForInspectionPhotos' => WaitForInspectionPhotos::class,
        'inspect'                 => Inspect::class,
        'stockIndoor'             => StockIndoor::class,
        'stockOnTheWay'           => StockOnTheWay::class,
        'filterPage'              => FilterPage::class,
    ];

    /**
     * Array holding mapped Model class to its corresponding status.
     *
     * @var string[]
     */
    private array $modelEnumMap = [
        SalesOrder::class     => SalesOrderStatus::class,
        PurchaseOrder::class  => PurchaseOrderStatus::class,
        Quote::class          => QuoteStatus::class,
        TransportOrder::class => TransportOrderStatus::class,
        Document::class       => DocumentStatus::class,
    ];

    /**
     * Array holding mapped Model class to its corresponding route.
     *
     * @var string[]
     */
    private array $modelRouteMap = [
        SalesOrder::class     => 'sales-orders.edit',
        PurchaseOrder::class  => 'purchase-orders.edit',
        Quote::class          => 'quotes.edit',
        TransportOrder::class => 'transport-orders.edit',
        Document::class       => 'documents.edit',
        Workflow::class       => 'workflows.show',
    ];

    /**
     * Array holding mapped Model class to its corresponding Name.
     *
     * @var array|string[]
     */
    private array $modelNameMap = [
        SalesOrder::class     => 'Sales Order',
        PurchaseOrder::class  => 'Purchase Order',
        Quote::class          => 'Quote',
        TransportOrder::class => 'Transport Order',
        Document::class       => 'Document',
        Workflow::class       => 'Workflow',
    ];

    /**
     * Create a new SystemVehicleService instance.
     */
    public function __construct()
    {
        $this->setVehicle(new Vehicle());
    }

    /**
     * Get management page method based on param.
     *
     * @param  string    $tab
     * @return DataTable
     */
    public function getManagementDataTable(string $tab): DataTable
    {
        if (! array_key_exists($tab, self::TABS)) {
            throw new InvalidArgumentException("Invalid tab class: {$tab}");
        }

        $className = $this->getTabs()[$tab];

        return (new $className())->getDataTable();
    }

    /**
     * Vehicle creation.
     *
     * @param StoreVehicleRequest $request
     * @return self
     */
    public function createSystemVehicle(StoreVehicleRequest $request): self
    {
        $validatedRequest = $request->validated();

        $vehicle = $this::createVehicle(new Vehicle(), $validatedRequest);

        $this->setVehicle($vehicle);
        $this->saveIdentificationCode();

        $vehicle->sendInternalRemarks($validatedRequest);
        $this->setVehicle($vehicle);

        return $this;
    }

    /**
     * Vehicle update.
     *
     * @param UpdateVehicleRequest $request
     * @return self
     */
    public function updateVehicle(UpdateVehicleRequest $request): self
    {
        $validatedRequest = $request->validated();

        $vehicle = $this->getVehicle();

        $vehicleUpdatedNamespace = WorkflowService::getWorkflowCompanyNameSpace($vehicle->creator->company).'\\Events\\VehicleUpdated';
        if (class_exists($vehicleUpdatedNamespace)) {
            $vehicleUpdated = new $vehicleUpdatedNamespace($vehicle, $validatedRequest);
            $vehicleUpdated->run();
        }

        $changeLoggerService = new ChangeLoggerService($vehicle, ['ownerships'], ['is_ready_to_be_sold']);

        $vehicle
            ->saveWithImages(UploadHelper::uploadMultipleImages($validatedRequest, 'internalImages'), 'internalImages')
            ->saveWithImages(UploadHelper::uploadMultipleImages($validatedRequest, 'externalImages'), 'externalImages')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'internalFiles'), 'internalFiles')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'externalFiles'), 'externalFiles');

        $vehicle->update($validatedRequest);
        $this->saveIdentificationCode();

        $updatedVehicleService = new UpdatedVehicleService($vehicle, $validatedRequest);
        $vehicle->calculation->update($validatedRequest);
        $updatedVehicleService->updateModules();

        $vehicle->sendInternalRemarks($validatedRequest);

        $changeLoggerService->logChanges($vehicle);

        $this->setVehicle($vehicle);

        return $this;
    }

    /**
     * Duplicate pre-order or normal vehicle.
     *
     * @param PreOrderVehicle|Vehicle $originVehicle
     * @param StoreVehicleDuplicationRequest $request
     * @param array $propsToEscape
     * @return array
     */
    public function duplicate(PreOrderVehicle|Vehicle $originVehicle, StoreVehicleDuplicationRequest $request, array $propsToEscape = []): array
    {
        $validatedRequest = $request->validated();

        $fileManager = new FileManager();

        $originImages = $originVehicle->images()->get()->toArray();
        $originFiles = $originVehicle->files()->get()->toArray();

        if ($originVehicle instanceof PreOrderVehicle) {
            $originVehicle->identification_code = $this->generateIdentificationCode($originVehicle);
            $propsToEscape = array_merge($propsToEscape, ['make', 'vehicle_model', 'variant', 'engine']);
        } else {
            foreach ($validatedRequest as $key => $value) {
                if (! in_array($key, ['duplications', 'id'], true) && ! $value) {
                    $propsToEscape[] = $key;
                }
            }
        }

        $vehicleTemplate = Arr::except($originVehicle->toArray(), $propsToEscape);
        if (isset($vehicleTemplate['expected_date_of_availability_from_supplier'])) {
            $vehicleTemplate['expected_date_of_availability_from_supplier'] = JsonHelper::convertArrayToJson($vehicleTemplate['expected_date_of_availability_from_supplier']);
        }

        $newVehiclesData = [];
        for ($i = 1; $i <= $validatedRequest['duplications']; $i++) {
            $newVehiclesData[] = $vehicleTemplate;
        }

        Vehicle::insert($newVehiclesData);

        $lastId = Vehicle::orderByDesc('id')->first()->id;
        $insertedIds = range($lastId - count($newVehiclesData) + 1, $lastId);

        $images = [];
        $files = [];
        foreach ($insertedIds as $id) {
            $copiedImages = $fileManager->copyMultipleFiles($originImages);
            $copiedFiles = $fileManager->copyMultipleFiles($originFiles);

            foreach ($copiedImages as $image) {
                $images[] = [
                    'original_name'  => $image['original_name'],
                    'unique_name'    => $image['unique_name'],
                    'path'           => $image['path'],
                    'order'          => $image['order'],
                    'section'        => $image['section'],
                    'size'           => $image['size'],
                    'imageable_id'   => $id,
                    'imageable_type' => Vehicle::class,
                ];
            }

            foreach ($copiedFiles as $file) {
                $files[] = [
                    'original_name' => $file['original_name'],
                    'unique_name'   => $file['unique_name'],
                    'path'          => $file['path'],
                    'order'         => $file['order'],
                    'section'       => $file['section'],
                    'size'          => $file['size'],
                    'fileable_id'   => $id,
                    'fileable_type' => Vehicle::class,
                ];
            }
        }

        $originCalculation = $originVehicle->calculation;

        $vehicleCalculations = [];
        $ownerships = [];
        $authUser = Auth::user();
        $userId = $authUser->id;
        foreach ($insertedIds as $createdVehicleId) {
            $duplicatedCalculation = $originCalculation;

            $duplicatedCalculation->id = null;
            $duplicatedCalculation->vehicleable_type = Vehicle::class;
            $duplicatedCalculation->vehicleable_id = $createdVehicleId;
            if (! $validatedRequest['sales_price_total']) {
                $duplicatedCalculation->sales_price_total = null;
            }

            $vehicleCalculations[] = $duplicatedCalculation->getAttributes();

            $ownerships[] = [
                'creator_id'   => $userId,
                'user_id'      => $userId,
                'ownable_type' => Vehicle::class,
                'ownable_id'   => $createdVehicleId,
                'status'       => OwnershipStatus::Accepted,
            ];
        }

        Calculation::insert($vehicleCalculations);
        Ownership::insert($ownerships);

        DB::table('images')->insert($images);
        DB::table('files')->insert($files);

        return $insertedIds;
    }

    /**
     * Create sales order with that vehicle.
     *
     * @return int
     */
    public function createSalesOrder(): int
    {
        $vehicle = $this->getVehicle();
        $vehicle->load(['calculation']);

        $newDeliveryWeek = DateHelper::adjustDateRanges($vehicle->expected_date_of_availability_from_supplier, $vehicle->expected_leadtime_for_delivery_from, $vehicle->expected_leadtime_for_delivery_to);

        $salesOrder = new SalesOrder([
            'total_sales_price'               => '' == $vehicle->calculation->total_purchase_price ? null : $vehicle->calculation->total_purchase_price,
            'total_fee_intermediate_supplier' => '' == $vehicle->calculation->fee_intermediate ? null : $vehicle->calculation->fee_intermediate,
            'total_sales_price_exclude_vat'   => '' == $vehicle->calculation->sales_price_net ? null : $vehicle->calculation->sales_price_net,
            'total_vat'                       => '' == $vehicle->calculation->vat ? null : $vehicle->calculation->vat,
            'total_bpm'                       => '' == $vehicle->calculation->bpm ? null : $vehicle->calculation->bpm,
            'total_sales_price_include_vat'   => '' == $vehicle->calculation->sales_price_total ? null : $vehicle->calculation->sales_price_total,
            'delivery_week'                   => $newDeliveryWeek,
        ]);
        $salesOrder->creator_id = auth()->id();
        $salesOrder->save();

        OwnershipService::createAuthOwnership($salesOrder);

        (new VehicleableService($salesOrder))->syncVehicles([
            [
                'vehicle_id'    => $vehicle->id,
                'delivery_week' => $newDeliveryWeek,
            ],
        ]);

        return $salesOrder->id;
    }

    /**
     * Create sales order with that vehicle.
     *
     * @param IdLocaleRequest $request
     * @return SystemVehicleService
     */
    public function createQuotePdf(IdLocaleRequest $request): self
    {
        $validatedRequest = $request->validated();

        $vehicle = $this->getVehicle();
        $vehicle->load(['images' => function ($imagesQuery) {
            return $imagesQuery->where('section', 'externalImages');
        },
            'calculation',
            'engine:id,name',
            'vehicleModel:id,name',
            'make:id,name',
            'variant:id,name',
            'creator',
        ]);

        $localeService = (new LocaleService())->checkChangeToLocale($validatedRequest['locale']);
        ImageManager::setModelImagesBase64($vehicle);

        $companyPdfAssets = CompanyService::getPdfAssets();

        $pdfService = new PdfService(
            'templates/quote-invitations/quote-single',
            [
                'quote'                                   => null,
                'vehicle'                                 => $vehicle,
                'company'                                 => auth()->user()->company,
                'attributes'                              => ModelHelper::getVehicleAttributes(),
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
            )->generate(), "quote-{$this->generateIdentificationCode($vehicle, true)}", 'pdf');
        $vehicle->saveWithFile($generatedPdf, 'internalFiles');

        $localeService->setOriginalLocale();

        return $this;
    }

    /**
     * Vehicle advanced search.
     *
     * @param       $query
     * @return void
     */
    public static function advancedSearch(&$query): void
    {
        $request = request(null);

        // Joins example - Requires slot in Vue.js
        $engineName = $request->input('filter.columns.engine');

        if ($engineName) {
            $query->whereHas('engine', function ($query) use ($engineName) {
                $query->where('name', 'LIKE', "%{$engineName}%");
            });
        }

        $modelName = $request->input('filter.columns.model');

        if ($modelName) {
            $query->whereHas('vehicleModel', function ($query) use ($modelName) {
                $query->where('name', 'LIKE', "%{$modelName}%");
            });
        }

        $brandName = $request->input('filter.columns.make');

        if ($brandName) {
            $query->whereHas('make', function ($query) use ($brandName) {
                $query->where('name', 'LIKE', "%{$brandName}%");
            });
        }

        // Using custom fields
        $minKw = $request->input('filter.columns.minkw');

        if ($minKw) {
            $query->where('kw', '>=', $minKw);
        }

        $maxKw = $request->input('filter.columns.maxkw');

        if ($maxKw) {
            $query->where('kw', '<=', $maxKw);
        }

        $minHp = $request->input('filter.columns.minhp');

        if ($minHp) {
            $query->where('hp', '>=', $minHp);
        }

        $maxHp = $request->input('filter.columns.maxhp');

        if ($maxHp) {
            $query->where('hp', '<=', $maxHp);
        }
    }

    /**
     * Save identification code shown on top of page.
     *
     * @return void
     */
    private function saveIdentificationCode(): void
    {
        $vehicle = $this->getVehicle();

        $vehicle->identification_code = $this->generateIdentificationCode($vehicle);
        $vehicle->save();
    }

    /**
     * Generate identification code shown in top of page.
     *
     * @param         $vehicle
     * @param bool $short
     * @return string
     */
    private function generateIdentificationCode($vehicle, $short = false): string
    {
        $parts = [];

        if (! $short) {
            $vinLastSix = empty($vehicle->vin) ? null : substr($vehicle->vin, -6);
            if ($vinLastSix) {
                $parts[] = $vinLastSix;
            }
            if ($vehicle->vehicle_reference) {
                $parts[] = $vehicle->vehicle_reference;
            }
        }

        if ($vehicle->make) {
            $parts[] = $vehicle->make->name;
        }
        if ($vehicle->vehicleModel) {
            $parts[] = $vehicle->vehicleModel->name;
        }
        if ($vehicle->variant) {
            $parts[] = $vehicle->variant->name;
        }
        if ($vehicle->engine) {
            $parts[] = $vehicle->engine->name;
        }

        $generatedString = implode(' ', $parts);

        return $short ? preg_replace('/[^a-zA-Z0-9]+/', '', $generatedString) : $generatedString;
    }

    /**
     * Return vehicles dataTable.
     *
     * @param  null|array $additionalRelations
     * @param  null|bool  $withTrashed
     * @param  ?array     $additionalSelectFields
     * @return DataTable
     */
    public function getIndexMethodTable(?array $additionalRelations = [], ?bool $withTrashed = false, ?array $additionalSelectFields = []): DataTable
    {
        $dataTable = (new DataTable(
            Vehicle::inThisCompany()
                ->when($withTrashed, function ($query) {
                    return $query->withTrashed();
                })
                ->select(array_merge(Vehicle::$defaultSelectFields, $additionalSelectFields))
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('engine', ['id', 'name'])
            ->setRelation('creator')
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setRelation('userFollows')
            ->setRelation('purchaseOrder', ['id', 'status', 'created_at'])
            ->setRelation('purchaseOrder.statuses')
            ->setRelation('salesOrder', ['id', 'status', 'down_payment', 'created_at'])
            ->setRelation('salesOrder.statuses')
            ->setRelation('transportOrderInbound', ['id', 'status', 'transport_company_use', 'created_at'])
            ->setRelation('transportOrderInbound.statuses')
            ->setRelation('transportOrderOutbound', ['id', 'status', 'transport_company_use', 'created_at'])
            ->setRelation('transportOrderOutbound.statuses')
            ->setRelation('documents', ['id', 'status', 'created_at'])
            ->setRelation('documents.statuses')
            ->setRelation('quotes', ['id', 'status'])
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', __('VIN'), true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('engine.name', __('Engine'), true, true)
            ->setColumn('kw', __('KW'), true, true)
            ->setColumn('hp', __('HP'), true, true)
            ->setColumn('co2_wltp', __('WLTP'), true, true)
            ->setColumn('calculation.net_purchase_price', __('P. Price ex VAT'), true)
            ->setColumn('calculation.fee_intermediate', __('Fee'), true, true)
            ->setColumn('calculation.costs_of_damages', __('Cost of damages'), true, true)
            ->setColumn('calculation.sales_price_net', __('Sales P. Net'), true, true)
            ->setColumn('calculation.transport_inbound', __('Transport inbound'), true, true)
            ->setColumn('calculation.transport_outbound', __('Transport outbound'), true, true)
            ->setColumn('calculation.bpm', __('BPM'), true, true)
            ->setColumn('calculation.leges_vat', __('Leges/VAT'), true, true)
            ->setColumn('calculation.sales_price_incl_vat_or_margin', __('Sales price incl. VAT'), true, true)
            ->setColumn('purchaseOrder', __('Purchase Order'), exportable: false)
            ->setColumn('salesOrder', __('Sales Order'), exportable: false)
            ->setColumn('transportOrderInbound', __('Transport Order Inbound'), exportable: false)
            ->setColumn('transportOrderOutbound', __('Transport Order Outbound'), exportable: false)
            ->setColumn('documents', __('Document'), exportable: false)
            ->setColumn('quotes', __('Quotes'), exportable: false)
            ->setTimestamps()
            ->advancedSearch(function ($query) {
                self::advancedSearch($query);
            });

        foreach ($additionalRelations as $relation => $relatedColumns) {
            $dataTable->setRelation($relation, $relatedColumns);
        }

        return $dataTable;
    }

    /**
     * Return vehicles datatable.
     *
     * @return DataTable
     */
    public function getSalesOrderAndQuoteIndexMethodTable(): DataTable
    {
        return (new DataTable(
            Vehicle::inThisCompany()->withTrashed()
                ->select(array_merge(Vehicle::$defaultSelectFields, [
                    'vin', 'fuel', 'kilometers', 'first_registration_date', 'specific_exterior_color',
                    'supplier_company_id', 'expected_date_of_availability_from_supplier', 'expected_leadtime_for_delivery_from',
                    'expected_leadtime_for_delivery_to', 'image_path', 'stock', 'damage_description',
                ]))
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('calculation')
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('engine', ['id', 'name'])
            ->setRelation('creator')
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setRelation('userFollows')
            ->setRelation('supplier', ['id', 'company_id'])
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', __('Vin'), true, true)
            ->setColumn('image_path', __('Image'), true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setTimestamps()
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('fuel', __('Fuel type'), true, true)
            ->setColumn('hp', __('HP'), true, true)
            ->setColumn('kilometers', __('Kilometers'), true, true)
            ->setColumn('first_registration_date', __('First Reg. Date'), true, true)
            ->setColumn('specific_exterior_color', __('Color'), true, true)
            ->setColumn('supplierCompany.name', __('Supplier'), true, true)
            ->setColumn('calculation.total_purchase_price', __('Purch. Price'), true, true)
            ->setColumn('calculation.costs_of_damages', __('Damages'), true, true)
            ->setColumn('calculation.transport_inbound', __('Transport'), true, true)
            ->setColumn('calculation.sales_margin', __('Margin'), true, true)
            ->setColumn('calculation.sales_price_net', __('Sales netto'), true, true)
            ->setColumn('calculation.discount', __('Discount'), true, true)
            ->setColumn('calculation.rest_bpm_indication', __('BPM'), true, true)
            ->setColumn('calculation.sales_price_total', __('Sales brutto'), true, true)
            ->setColumn('expected_date_of_availability_from_supplier', __('Available'), true, true)
            ->setColumn('expected_leadtime_for_delivery', __('Lead time'))
            ->setEnumColumn('fuel', FuelType::class);
    }

    /**
     * Return tabs array.
     *
     * @return string[]
     */
    public function getTabs(): array
    {
        return self::TABS;
    }

    /**
     * Get the model of the vehicle.
     *
     * @return Vehicle
     */
    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }

    /**
     * Set the model of the vehicle.
     *
     * @param  Vehicle $vehicle
     * @return self
     */
    public function setVehicle(Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Create vehicle transfer token and return it.
     *
     * @return string
     */
    public function getTransferVehicleLink(): string
    {
        $vehicle = $this->getVehicle();

        $token = Str::random(64);
        $vehicle->transferToken()->updateOrCreate(
            [],
            ['token' => $token]
        );

        $this->sendTransferLinkEmail($token);

        return $token;
    }

    private function sendTransferLinkEmail(string $token): void
    {
        $email = request()->email;
        if (! $email) {
            return;
        }

        $receivers = User::where('email', $email)->get()
            ->whenEmpty(fn () => Company::where('email', $email)->get());

        $message = auth()->user()->full_name.__(' is sending you transfer link for a vehicle.');
        $emailNorification = new EmailNotification(
            MailType::Transfer_vehicle->value,
            $message,
            route('vehicles.transfer', $token),
            __(' and transfer the vehicle to your company.'),
        );
        Notification::send($receivers, $emailNorification);

        Notification::send($receivers, new DatabaseNotification($message));

        (new MailService())->saveMailToSystem(
            $emailNorification->toMail($receivers[0])->render(),
            $receivers,
            $this->getVehicle(),
            'Email Notification'
        );
    }

    /**
     * Create vehicle transfer token and return it.
     *
     * @param string $token
     * @return SystemVehicleService
     * @throws VehicleException
     */
    public function transferVehicle(string $token): static
    {
        $transferToken = VehicleTransferToken::where('token', $token)->with([
            'vehicle', 'vehicle.creator', 'vehicle.make:id,name', 'vehicle.vehicleModel:id,name',
            'vehicle.variant:id,name', 'vehicle.engine:id,name',
        ])->first();
        if (! $transferToken) {
            throw new VehicleException(__('Vehicle is transferred or invalid token.'));
        }

        $originVehicle = $transferToken->vehicle;

        if ($originVehicle->creator->company_id == auth()->user()->company_id) {
            throw new VehicleException(__('Vehicle is already in your company.'));
        }

        if ($originVehicle->make_id) {
            $make = Make::inThisCompany()->where('name', $originVehicle->make->name)->first();

            if (! $make) {
                $make = new Make([
                    'name' => $originVehicle->make->name,
                ]);
                $make->creator_id = auth()->id();
                $make->save();
            }
        }

        if ($originVehicle->vehicle_model_id && isset($make)) {
            $vehicleModel = VehicleModel::inThisCompany()->where('name', $originVehicle->vehicleModel->name)->first();
            if (! $vehicleModel) {
                $vehicleModel = new VehicleModel([
                    'make_id' => $make->id,
                    'name'    => $originVehicle->vehicleModel->name,
                ]);
                $vehicleModel->creator_id = auth()->id();
                $vehicleModel->save();
            }
        }

        if ($originVehicle->variant_id && isset($make)) {
            $variant = Variant::inThisCompany()->where('name', $originVehicle->variant->name)->first();
            if (! $variant) {
                $variant = new Variant([
                    'creator_id' => auth()->id(),
                    'make_id'    => $make->id,
                    'name'       => $originVehicle->variant->name,
                ]);
                $variant->creator_id = auth()->id();
                $variant->save();
            }
        }

        if ($originVehicle->engine_id && isset($make) && $originVehicle->fuel) {
            $engine = Engine::inThisCompany()->where('name', $originVehicle->engine->name)->first();
            if (! $engine) {
                $engine = new Engine([
                    'creator_id' => auth()->id(),
                    'make_id'    => $make->id,
                    'fuel'       => $originVehicle->fuel,
                    'name'       => $originVehicle->engine->name,
                ]);
                $engine->creator_id = auth()->id();
                $engine->save();
            }
        }

        $vehicleTemplate = Arr::only($originVehicle->toArray(), [
            'current_registration',
            'type',
            'vehicle_model_free_text',
            'variant_free_text',
            'fuel',
            'engine_free_text',
            'transmission',
            'transmission_free_text',
            'body',
            'kw',
            'hp',
            'first_registration_date',
            'kilometers',
            'specific_exterior_color',
            'color_type',
            'factory_name_color',
            'specific_interior_color',
            'factory_name_interior',
            'interior_material',
            'co2_wltp',
            'co2_nedc',
            'gross_bpm_on_registration',
            'last_name_registration',
            'registration_valid_until',
            'first_registration_nl',
            'registration_nl',
            'registration_date_approval',
            'first_name_registration_nl',
            'nl_registration_number',
            'option',
            'damage_description',
            'warranty',
            'warranty_free_text',
            'navigation',
            'navigation_free_text',
            'app_connect',
            'app_connect_free_text',
            'panorama',
            'panorama_free_text',
            'headlights',
            'headlights_free_text',
            'digital_cockpit',
            'digital_cockpit_free_text',
            'cruise_control',
            'cruise_control_free_text',
            'keyless_entry',
            'keyless_entry_free_text',
            'air_conditioning',
            'air_conditioning_free_text',
            'pdc',
            'pdc_free_text',
            'highlight_1',
            'highlight_2',
            'highlight_3',
            'second_wheels',
            'second_wheels_free_text',
            'camera',
            'camera_free_text',
            'tow_bar',
            'tow_bar_free_text',
            'sports_seat',
            'sports_seat_free_text',
            'seats_electrically_adjustable',
            'seat_heating',
            'seat_heating_free_text',
            'seat_massage',
            'seat_massage_free_text',
            'optics',
            'optics_free_text',
            'tinted_windows',
            'tinted_windows_free_text',
            'sports_package',
            'sports_package_free_text',
            'highlight_4',
            'highlight_5',
            'highlight_6',
        ]);

        $vehicle = new Vehicle($vehicleTemplate);
        $vehicle->creator_id = auth()->id();
        $vehicle->make_id = $make->id ?? null;
        $vehicle->vehicle_model_id = $vehicleModel->id ?? null;
        $vehicle->variant_id = $variant->id ?? null;
        $vehicle->engine_id = $engine->id ?? null;
        $vehicle->save();

        $vehicle->calculation()->save(new Calculation());
        $fileManager = new FileManager();
        $copiedImages = $fileManager->copyMultipleFiles($originVehicle->getGroupedImages(['externalImages'])['externalImages']->toArray());
        $copiedFiles = $fileManager->copyMultipleFiles($originVehicle->getGroupedFiles(['externalFiles'])['externalFiles']->toArray());

        $images = [];
        $files = [];
        foreach ($copiedImages as $key => $image) {
            $images[] = [
                'original_name'  => $image['original_name'],
                'unique_name'    => $image['unique_name'],
                'path'           => $image['path'],
                'size'           => $image['size'],
                'section'        => 'externalImages',
                'order'          => $key + 1,
                'imageable_id'   => $vehicle->id,
                'imageable_type' => Vehicle::class,
            ];
        }

        foreach ($copiedFiles as $key => $file) {
            $files[] = [
                'original_name' => $file['original_name'],
                'unique_name'   => $file['unique_name'],
                'path'          => $file['path'],
                'size'          => $file['size'],
                'section'       => 'externalFiles',
                'order'         => $key + 1,
                'fileable_id'   => $vehicle->id,
                'fileable_type' => Vehicle::class,
            ];
        }

        DB::table('images')->insert($images);
        DB::table('files')->insert($files);

        Ownership::insert([
            'creator_id'   => auth()->id(),
            'user_id'      => auth()->id(),
            'ownable_type' => Vehicle::class,
            'ownable_id'   => $vehicle->id,
            'status'       => OwnershipStatus::Accepted,
        ]);

        $this->setVehicle($vehicle);
        $this->saveIdentificationCode();

        $transferToken->delete();

        return $this;
    }

    /**
     * Return all statuses changes and resource creations ordered chronologically.
     *
     * @param  int   $vehicleId
     * @return array
     */
    public function getVehicleTimelineItems(int $vehicleId): array
    {
        $returnArray = [];

        try {
            $vehicle = Vehicle::select('id')->findOrFail($vehicleId)->load([
                'transportOrders' => function ($query) {
                    $query->withTrashed()->select('id', 'transport_type', 'transport_orders.created_at');
                },
                'transportOrders.statuses',
                'documents' => function ($query) {
                    $query->withTrashed()->select('id', 'documents.created_at');
                },
                'documents.statuses',
                'salesOrder' => function ($query) {
                    $query->withTrashed()->select('id', 'sales_orders.created_at');
                },
                'salesOrder.statuses',
                'purchaseOrder' => function ($query) {
                    $query->withTrashed()->select('id', 'purchase_orders.created_at');
                },
                'purchaseOrder.statuses',
                'quotes' => function ($query) {
                    $query->withTrashed()->select('id', 'quotes.created_at');
                },
                'quotes.statuses',
                'workflow:id,vehicleable_type,vehicleable_id,created_at',
            ]);

            $this->setVehicle($vehicle);

            $items = collect();

            foreach (['transportOrders', 'documents', 'salesOrder', 'purchaseOrder', 'quotes'] as $relation) {
                $this->collectItems($relation, $items);
            }

            if ($vehicle->workflow) {
                $items->push($vehicle->workflow);
            }

            foreach ($items->sortBy('created_at') as $value) {
                if ($value instanceof Statusable) {
                    $statusName = StringHelper::replaceUnderscores($this->modelEnumMap[$value->statusable_type]::getCaseByValue((int) $value->status)->name);

                    $returnArray[] = [
                        'date'  => $value->created_at,
                        'title' => __('Change ').__($this->modelNameMap[$value->statusable_type]).' #'.$value->statusable_id.__(' status to ').$statusName,
                        'route' => $this->modelRouteMap[$value->statusable_type],
                        'id'    => $value->statusable_id,
                    ];
                } else {
                    $returnArray[] = [
                        'date'  => $value->created_at,
                        'title' => __('Create ').__($this->modelNameMap[$value::class]).' #'.$value->id,
                        'route' => $this->modelRouteMap[$value::class],
                        'id'    => $value->id,
                    ];
                }
            }

            return $returnArray;
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return $returnArray;
        }
    }

    /**
     * Helper function to skip code duplication and accumulate create status items.
     *
     * @param  string     $relation
     * @param  Collection $items
     * @return void
     */
    private function collectItems(string $relation, Collection &$items): void
    {
        $vehicle = $this->getVehicle();

        if ($vehicle->{$relation}) {
            $vehicle->{$relation}->each(function ($relationItem) use ($items) {
                $items->push($relationItem);
                $relationItem->statuses?->each(function ($status) use ($items) {
                    $items->push($status);
                });
            });
        }
    }

    /**
     * Return dataTable used in overview and following pages.
     *
     * @param            $query
     * @return DataTable
     */
    public function overviewAndFollowing($query): DataTable
    {
        return (new DataTable(
            $query->select('id', 'vin', 'creator_id', 'make_id', 'vehicle_model_id', 'created_at', 'updated_at')
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('purchaseOrder', ['id', 'status', 'created_at'])
            ->setRelation('purchaseOrder.statuses')
            ->setRelation('salesOrder', ['id', 'status', 'down_payment', 'created_at'])
            ->setRelation('salesOrder.statuses')
            ->setRelation('transportOrderInbound', ['id', 'status', 'transport_company_use', 'created_at'])
            ->setRelation('transportOrderInbound.statuses')
            ->setRelation('transportOrderOutbound', ['id', 'status', 'transport_company_use', 'created_at'])
            ->setRelation('transportOrderOutbound.statuses')
            ->setRelation('documents', ['id', 'status', 'created_at'])
            ->setRelation('documents.statuses')
            ->setRelation('quotes', ['id', 'status'])
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', __('VIN'), true, true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setTimestamps()
            ->setColumn('purchaseOrder', __('Purchase Order'), false, true)
            ->setColumn('salesOrder', __('Sales Order'), false, true)
            ->setColumn('transportOrderInbound', __('Transport Order Inbound'), false, true)
            ->setColumn('transportOrderOutbound', __('Transport Order Outbound'), false, true)
            ->setColumn('purchaseOrder', __('Purchase Order'))
            ->setColumn('salesOrder', __('Sales Order'))
            ->setColumn('transportOrderInbound', __('Transport Order Inbound'))
            ->setColumn('transportOrderOutbound', __('Transport Order Outbound'))
            ->setColumn('documents', __('Document'))
            ->setColumn('quotes', __('Quotes'))
            ->run();
    }

    /**
     * Return datatable of Vehicles by provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getVehiclesDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(Vehicle::$defaultSelectFields)
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('engine', ['id', 'name'])
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('engine.name', __('Engine'), true, true)
            ->setColumn('kw', __('KW'), true, true)
            ->setColumn('hp', __('HP'), true, true)
            ->setColumn('co2_wltp', __('WLTP'), true, true)
            ->setColumn('created_at', __('Date'), true, true)
            ->setDateColumn('created_at', 'd.m.Y H:i');

        return $dataTable;
    }

    /**
     * Return vehicle with its data.
     *
     * @return null|Vehicle
     */
    public static function getVehicleInformation(): ?Vehicle
    {
        $vehicleId = request()->input('vehicle_id');

        if (! $vehicleId) {
            return null;
        }

        return Vehicle::withTrashed()->select([
            'id', 'fuel', 'co2_wltp', 'co2_nedc', 'created_at',
        ])->with('calculation')->find($vehicleId);
    }
}
