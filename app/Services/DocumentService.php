<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentableType;
use App\Enums\DocumentStatus;
use App\Enums\OwnershipStatus;
use App\Enums\PaymentCondition;
use App\Enums\SalesOrderStatus;
use App\Enums\ServiceOrderStatus;
use App\Enums\WorkOrderStatus;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Document;
use App\Models\Ownership;
use App\Models\PreOrderVehicle;
use App\Models\SalesOrder;
use App\Models\ServiceOrder;
use App\Models\ServiceVehicle;
use App\Models\Vehicle;
use App\Models\WorkOrder;
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
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class DocumentService extends Service
{
    private const TEMPLATES_MAP = [
        DocumentStatus::Pro_forma->value      => 'templates/documents/pro-forma',
        DocumentStatus::Create_invoice->value => 'templates/documents/invoice',
        DocumentStatus::Credit_invoice->value => 'templates/documents/credit-invoice',
    ];

    /**
     * The document model.
     *
     * @var Document
     */
    public Document $document;

    private int $selectedDocumentableType;

    /**
     * DataTable of the documentables datatable shown in edit form.
     *
     * @var null|DataTable
     */
    private null|DataTable $documentablesDataTable;

    /**
     * Collection of all the documentables for the selected document.
     *
     * @var Collection
     */
    private Collection $selectedDocumentables;

    /**
     * Id that will be automatically selected in create.
     *
     * @var int
     */
    private int $queryId;

    /**
     * Create a new DocumentService instance.
     */
    public function __construct()
    {
        $this->setDocument(new Document());
    }

    /**
     * Return documents in this company dataTable used it index page.
     *
     * @return DataTable
     */
    public function getIndexMethodDataTable(): DataTable
    {
        return (new DataTable(
            Document::inThisCompany()->with([
                'statuses',
            ])->select(Document::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('documentable_type', __('Invoiceable Type'), true, true)
            ->setColumn('payment_condition', __('Payment Condition'), true, true)
            ->setColumn('total_price_include_vat', __('Total Price'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', DocumentStatus::class)
            ->setEnumColumn('documentable_type', DocumentableType::class)
            ->setEnumColumn('payment_condition', PaymentCondition::class)
            ->setPriceColumn('total_price_include_vat');
    }

    /**
     * Set the model of the document.
     *
     * @param  Document $document
     * @return self
     */
    public function setDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Set the model of the document.
     *
     * @return int
     */
    public function getSelectedDocumentableType(): int
    {
        if (! isset($this->selectedDocumentableType)) {
            $this->setSelectedDocumentableType();
        }

        return $this->selectedDocumentableType;
    }

    /**
     * Return documents in this company dataTable used it index page.
     *
     * @return DocumentService
     */
    public function setSelectedDocumentableType(): static
    {
        $this->selectedDocumentableType = (int) request(null)->input('documentable_type', $this->getDocument()->documentable_type?->value) ?? (int) request(null)->input('filter.documentable_type');

        return $this;
    }

    /**
     * Get the model of the document.
     *
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * Get the value of queryId.
     *
     * @return int
     */
    public function getQueryId(): int
    {
        if (! isset($this->queryId)) {
            $this->setQueryId();
        }

        return $this->queryId;
    }

    /**
     * Set the value of queryId.
     *
     * @return void
     */
    private function setQueryId(): void
    {
        $this->queryId = (int) request()->input('filter.id');
    }

    /**
     * Document creation.
     *
     * @param StoreDocumentRequest $request
     * @return self
     */
    public function createDocument(StoreDocumentRequest $request): self
    {
        $validatedRequest = $request->validated();

        $document = new Document();
        $document->fill($validatedRequest);
        $document->creator_id = auth()->id();
        $document->save();

        $relationMethod = $document->typeRelationMap[$validatedRequest['documentable_type']];

        $document->{$relationMethod}()->attach($validatedRequest['documentableIds'] ?? []);
        $document->lines()->createMany($validatedRequest['lines'] ?? []);

        $document->sendInternalRemarks($validatedRequest);

        $this->setDocument($document);

        return $this;
    }

    public function duplicateDocument(Document $document): self
    {
        $authId = Auth::id();
        $relationMethod = $document->typeRelationMap[$document->documentable_type->value];
        $originDocument = $document;
        $document->load([
            $relationMethod,
            'lines',
        ]);
        $documentables = $originDocument->{$relationMethod};

        $clonedDocument = $originDocument->replicate(['status']);
        $clonedDocument->save();

        $ownership = new Ownership([
            'user_id'      => $authId,
            'ownable_id'   => $clonedDocument->id,
            'ownable_type' => $clonedDocument::class,
            'status'       => OwnershipStatus::Accepted,
        ]);
        $ownership->creator_id = $authId;
        $clonedDocument->ownerships()->save($ownership);

        $clonedDocument->{$relationMethod}()->attach($documentables->pluck('id'));

        $clonedDocument->lines()->createMany($originDocument->lines->toArray());

        $this->setDocument($clonedDocument);

        return $this;
    }

    /**
     * Load the Documentables dataTable shown in edit form.
     *
     * @return void
     * @throws Exception
     */
    private function initDocumentablesDataTable(): void
    {
        $document = $this->getDocument();

        $userHasSearched = request(null)->input('filter.global');
        $selectedDocumentableType = $this->getSelectedDocumentableType();

        if (! $selectedDocumentableType) {
            $this->setDocumentablesDataTable(null);

            return;
        }

        $selectedDocumentableIds = $this->getSelectedDocumentables()->pluck('id')->toArray();
        $queryId = $this->getQueryId();

        switch ($selectedDocumentableType) {
            case DocumentableType::Pre_order_vehicle->value:
                $dataTable = (new PreOrderVehicleService())->getIndexMethodTable(
                    ['calculation' => ['vehicleable_type', 'vehicleable_id', 'vat_percentage', 'vat', 'sales_price_total', 'sales_price_net']],
                    true
                )->setRelation('calculation');

                break;
            case DocumentableType::Vehicle->value:
                $dataTable = (new SystemVehicleService())->getIndexMethodTable(
                    ['calculation' => ['vehicleable_type', 'vehicleable_id', 'vat_percentage', 'vat', 'sales_price_total', 'sales_price_net']],
                    true
                )->setRelation('calculation');

                break;
            case DocumentableType::Service_vehicle->value:
                $dataTable = (new ServiceVehicleService())->getIndexMethodTable(true);

                break;
            case DocumentableType::Sales_order_down_payment->value:
                $dataTable = (new SalesOrderService())->getIndexMethodDataTable(['vehicles:id'], true);

                break;
            case DocumentableType::Sales_order->value:
                $dataTable = (new SalesOrderService())->getIndexMethodDataTable(['orderServices', 'orderItems', 'vehicles:id'], true);

                break;
            case DocumentableType::Service_order->value:
                $dataTable = (new ServiceOrderService())->getIndexMethodDataTable(['orderServices', 'orderItems'], true);

                break;
            case DocumentableType::Work_order->value:
                $dataTable = (new WorkOrderService())->getIndexMethodDataTable(['tasks' => ['work_order_id', 'name', 'actual_price']]);

                break;
            default:
                throw new Exception('Type not provided or invalid', 404);
                break;
        }

        $table = $dataTable->getBuilder()->getModel()->getTable();
        if ($selectedDocumentableIds) {
            $rawOrdering = new RawOrdering("FIELD({$table}.id, ".implode(',', $selectedDocumentableIds).') DESC');
        } elseif ($queryId) {
            $rawOrdering = new RawOrdering("FIELD({$table}.id, {$queryId}) DESC");
        }

        if (isset($rawOrdering)) {
            $dataTable->setRawOrdering($rawOrdering);
        }

        switch ($selectedDocumentableType) {
            case DocumentableType::Sales_order_down_payment->value:
            case DocumentableType::Sales_order->value:
                $dataTable->run(config('app.default.pageResults', 10), function ($model) use ($queryId, $selectedDocumentableIds, $selectedDocumentableType, $document, $userHasSearched, $table) {
                    if ($userHasSearched || $queryId) {
                        return $model->withoutTrashed()
                            ->where('status', DocumentableType::Sales_order_down_payment->value == $selectedDocumentableType ? SalesOrderStatus::Ready_for_down_payment_invoice->value : SalesOrderStatus::Completed->value)
                            ->whereDoesntHave('documents', function ($subQuery) use ($selectedDocumentableType) {
                                $subQuery->where('documents.documentable_type', $selectedDocumentableType);
                            });
                    }

                    if ($document->id) {

                        return $model->whereIn("{$table}.id", $selectedDocumentableIds);
                    }

                    return $model->whereNull("{$table}.id");
                });

                break;
            case DocumentableType::Service_order->value:
                $dataTable = (new ServiceOrderService())->getIndexMethodDataTable(['orderServices', 'orderItems'], true)
                    ->run(config('app.default.pageResults', 10), function ($model) use ($queryId, $selectedDocumentableIds, $document, $userHasSearched, $table) {
                        if ($userHasSearched || $queryId) {
                            return $model->withoutTrashed()
                                ->where('status', ServiceOrderStatus::Completed->value)
                                ->whereDoesntHave('documents');
                        }

                        if ($document->id) {
                            return $model->whereIn("{$table}.id", $selectedDocumentableIds);
                        }

                        return $model->whereNull("{$table}.id");
                    });

                break;
            case DocumentableType::Work_order->value:
                $dataTable = (new WorkOrderService())->getIndexMethodDataTable(['tasks' => ['work_order_id', 'name', 'actual_price']])
                    ->run(config('app.default.pageResults', 10), function ($model) use ($queryId, $selectedDocumentableIds, $document, $userHasSearched, $table) {
                        if ($userHasSearched || $queryId) {
                            return $model->withoutTrashed()
                                ->where('status', WorkOrderStatus::Completed->value)
                                ->whereDoesntHave('documents');
                        }

                        if ($document->id) {
                            return $model->whereIn("{$table}.id", $selectedDocumentableIds);
                        }

                        return $model->whereNull("{$table}.id");
                    });

                break;
            default:
                $dataTable->run(config('app.default.pageResults', 10), function ($model) use ($queryId, $selectedDocumentableIds, $document, $userHasSearched, $table) {
                    if ($userHasSearched || $queryId) {
                        return $model
                            ->withoutTrashed()
                            ->whereDoesntHave('documents');
                    }

                    if ($document->id) {
                        return $model->whereIn("{$table}.id", $selectedDocumentableIds);
                    }

                    return $model->whereNull("{$table}.id");
                });

                break;
        }

        $this->setDocumentablesDataTable($dataTable);
    }

    /**
     * Get the Documentables dataTable shown in edit form.
     *
     * @return null|DataTable
     * @throws Exception
     */
    public function getDocumentableDataTable(): null|DataTable
    {
        if (! isset($this->documentablesDataTable)) {
            $this->initDocumentablesDataTable();
        }

        return $this->documentablesDataTable;
    }

    /**
     * Get the sales order vehicles table.
     *
     * @return null|\Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getTableVehicles(): null|\Illuminate\Database\Eloquent\Collection
    {
        if (! in_array($this->getSelectedDocumentableType(), [DocumentableType::Sales_order->value, DocumentableType::Sales_order_down_payment->value])) {
            return null;
        }

        if (request()->input('vehicle_ids')) {
            $vehicleIds = request()->input('vehicle_ids');
        } else {
            $document = $this->getDocument();
            $document->load([
                'salesOrders:id',
                'salesOrders.vehicles:id',
            ]);

            $vehicleIds = $document->salesOrders->pluck('vehicles')->flatten()->pluck('id');
        }

        return Vehicle::inThisCompany()->withTrashed()->whereIn('id', $vehicleIds)
            ->select(array_merge(Vehicle::$defaultSelectFields, [
                'vin', 'fuel', 'kilometers', 'first_registration_date', 'specific_exterior_color',
                'supplier_company_id', 'expected_date_of_availability_from_supplier', 'expected_leadtime_for_delivery_from',
                'expected_leadtime_for_delivery_to', 'image_path', 'stock',
            ]))->with([
                'make:id,name', 'calculation', 'vehicleModel:id,name', 'engine:id,name', 'creator',
                'workflow:id,vehicleable_type,vehicleable_id', 'supplier:id,company_id', 'supplierCompany:id,name',
            ])->get();
    }

    /**
     * Set the documentables dataTable shown in edit form.
     *
     * @param  null|DataTable $documentablesDataTable
     * @return self
     */
    private function setDocumentablesDataTable(null|DataTable $documentablesDataTable): self
    {
        $this->documentablesDataTable = $documentablesDataTable;

        return $this;
    }

    /**
     * Load the value of selectedDocumentables.
     *
     * @return void
     */
    public function setSelectedDocumentables(): void
    {
        $document = $this->getDocument();

        if (! $document->id) {
            $this->selectedDocumentables = collect();

            return;
        }

        $this->selectedDocumentables = $document->{$document->typeRelationMap[$document->documentable_type->value]}()->withTrashed()->get();
    }

    /**
     * Get the value of selectedDocumentables.
     *
     * @return Collection
     */
    public function getSelectedDocumentables(): Collection
    {
        if (! isset($this->selectedDocumentables)) {
            $this->setSelectedDocumentables();
        }

        return $this->selectedDocumentables;
    }

    /**
     * Document update.
     *
     * @param UpdateDocumentRequest $request
     * @return self
     */
    public function updateDocument(UpdateDocumentRequest $request): self
    {
        $validatedRequest = $request->validated();

        $document = $this->getDocument();

        $changeLoggerService = new ChangeLoggerService($document, ['lines', 'ownerships']);

        $document->update($validatedRequest);
        $document->sendInternalRemarks($validatedRequest);

        $relationMethod = $document->typeRelationMap[$validatedRequest['documentable_type']];
        $document->{$relationMethod}()->sync($validatedRequest['documentableIds'] ?? []);

        $this->syncLines($validatedRequest['lines'] ?? []);

        $this->setDocument($document);

        if ($validatedRequest['paid_at']) {
            $document->status = DocumentStatus::Paid->value;
            $document->save();
            $document->statuses()->updateOrCreate(['status' => DocumentStatus::Paid->value], ['created_at' => now()]);
        }

        $changeLoggerService->logChanges($document);

        return $this;
    }

    /**
     * Load the relations for the connected to the modules section
     *
     * @return Document
     */
    public function loadRelations(): Document
    {
        $document = $this->getDocument();

        $document->load([
            'ownerships.user:id,full_name',
            'customerCompany:id,email',
            'customer:id,full_name,company_id,email',
            'lines',
            'documentables',
            'internalRemarks', 'changeLogs',
            'files', 'mails', 'statuses',
        ]);

        switch ($document->documentable_type->value) {
            case DocumentableType::Pre_order_vehicle->value:
                $document->load([
                    'preOrderVehicles' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'preOrderVehicles.preOrder' => function ($query) {
                        $query->withTrashed()->select('id', 'pre_order_vehicle_id');
                    },
                    'preOrderVehicles.transportOrders' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                ]);
                break;

            case DocumentableType::Vehicle->value:
                $document->load([
                    'vehicles' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'vehicles.purchaseOrder' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'vehicles.salesOrder' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'vehicles.transportOrders' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'vehicles.workflow:id,vehicleable_type,vehicleable_id',
                    'vehicles.workOrder' => function ($query) {
                        $query->withTrashed()->select('id', 'vehicleable_type', 'vehicleable_id');
                    },
                    'vehicles.quotes' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                ]);
                break;

            case DocumentableType::Service_vehicle->value:
                $document->load([
                    'serviceVehicles' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'serviceVehicles.serviceOrder' => function ($query) {
                        $query->withTrashed()->select('id', 'service_vehicle_id');
                    },
                    'serviceVehicles.transportOrders' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'serviceVehicles.workflow:id,vehicleable_type,vehicleable_id',
                    'serviceVehicles.workOrder' => function ($query) {
                        $query->withTrashed()->select('id', 'vehicleable_type', 'vehicleable_id');
                    },
                ]);
                break;

            case DocumentableType::Sales_order_down_payment->value:
            case DocumentableType::Sales_order->value:
                $document->load([
                    'salesOrders' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'salesOrders.vehicles' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'salesOrders.vehicles.purchaseOrder' => function ($query) {
                        $query->withTrashed()->select('id', 'deleted_at');
                    },
                    'salesOrders.vehicles.transportOrders' => function ($query) {
                        $query->withTrashed()->select('id', 'transportable_type', 'deleted_at');
                    },
                    'salesOrders.vehicles.workflow:id,vehicleable_type,vehicleable_id',
                    'salesOrders.vehicles.workOrder' => function ($query) {
                        $query->withTrashed()->select('id', 'vehicleable_type', 'vehicleable_id', 'deleted_at');
                    },
                    'salesOrders.vehicles.quotes' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                ]);
                break;

            case DocumentableType::Service_order->value:
                $document->load([
                    'serviceOrders.serviceVehicle' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'serviceOrders.serviceVehicle.transportOrders' => function ($query) {
                        $query->withTrashed()->select('id');
                    },
                    'serviceOrders.serviceVehicle.workflow:id,vehicleable_type,vehicleable_id',
                    'serviceOrders.serviceVehicle.workOrder' => function ($query) {
                        $query->withTrashed()->select('id', 'vehicleable_type', 'vehicleable_id');
                    },
                ]);
                break;
        }

        $this->setDocument($document);

        return $document;
    }

    /**
     * Update the document status.
     *
     * @param UpdateStatusRequest $request
     * @return DocumentService
     * @throws Exception
     */
    public function updateDocumentStatus(UpdateStatusRequest $request): self
    {
        $validatedRequest = $request->validated();

        $document = $this->getDocument();
        $changeLoggerService = new ChangeLoggerService($document);
        $document->status = $validatedRequest['status'];

        switch ($validatedRequest['status']) {
            case DocumentStatus::Approved->value:
            case DocumentStatus::Rejected->value:
                if (! auth()->user()->can('approve-document')) {
                    throw new Exception(__('You do not have the permission'));
                }

                break;
            case DocumentStatus::Create_invoice->value:
                $document->number = 'V'.now()->format('y').$document->id;
            case DocumentStatus::Pro_forma->value:
            case DocumentStatus::Credit_invoice->value:
                $localeService = (new LocaleService())->checkChangeToLocale($validatedRequest['locale']);
                $generatedPdf = $this->generatePdf();
                $fileName = $this->generateFileName($document);

                $documents = UploadHelper::uploadGeneratedFile($generatedPdf, "{$fileName}-{$document->id}", 'pdf');
                $document->saveWithFile($documents);
                $localeService->setOriginalLocale();

                break;
            case DocumentStatus::Paid->value:
                if (! $document->paid_at) {
                    throw new Exception(__('You need to enter payment date'));
                }

                break;
        }

        $document->save();

        if (1 != $validatedRequest['status']) {
            $document->statuses()->updateOrCreate(['status' => $validatedRequest['status']], ['created_at' => now()]);
        }

        $changeLoggerService->logChanges($document);

        return $this;
    }

    /**
     * Update lines relation.
     *
     * @param       $lines
     * @return void
     */
    private function syncLines($lines): void
    {
        $document = $this->getDocument();
        $existingLineIds = collect($lines)->pluck('id')->filter();
        $document->lines()->whereNotIn('id', $existingLineIds)->delete();

        foreach ($lines as &$line) {
            $line['document_id'] = $document->id;
            $line['price_exclude_vat'] = CurrencyHelper::convertCurrencyToUnits($line['price_exclude_vat']);
            $line['vat'] = CurrencyHelper::convertCurrencyToUnits($line['vat']);
            $line['price_include_vat'] = CurrencyHelper::convertCurrencyToUnits($line['price_include_vat']);
        }

        $document->lines()->upsert($lines, ['id'], ['document_id', 'name', 'vat_percentage', 'price_exclude_vat', 'vat', 'price_include_vat', 'documentable_id', 'type']);
    }

    private function getPdfDocumentLogo()
    {

        $images = Auth::user()?->company?->getGroupedImages(['documents_pdf_logo']);
        $newPath = $images['gray']->first()?->path ?? null;

        return $newPath ? '/storage/'.$newPath : CompanyService::getUserCompanyLogo(Auth::user());
    }

    /**
     * Generate and save pdf to system on sent to customer status change.
     *
     * @return string
     * @throws Exception
     */
    private function generatePdf(): string
    {
        $document = $this->getDocument();

        $document->load([
            'creator:id,full_name,company_id',
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
            'customer:id,full_name',
            'customerCompany' => function ($query) {
                $query->select('id', 'name', 'address', 'postal_code', 'city', 'country', 'vat_number', 'debtor_number_accounting_system')->withTrashed();
            },
            'lines',
        ]);

        switch ($document->documentable_type->value) {
            case DocumentableType::Pre_order_vehicle->value:
                $selectedDocumentables = $document->preOrderVehicles()
                    ->withTrashed()
                    ->select(array_merge(
                        array_diff(PreOrderVehicle::$defaultSelectFields, ['updated_at', 'created_at']),
                        [
                            'pre_order_vehicles.updated_at', 'pre_order_vehicles.created_at', 'specific_exterior_color',
                            'vehicle_reference', 'vehicle_status', 'configuration_number', 'komm_number', 'vehicle_model_free_text',
                            'variant_free_text', 'engine_free_text', 'transmission_free_text',
                        ]
                    ))
                    ->with(['make:id,name', 'vehicleModel:id,name', 'engine:id,name', 'variant:id,name', 'creator', 'calculation:vehicleable_type,vehicleable_id,rest_bpm_indication,leges_vat'])
                    ->get();

                break;
            case DocumentableType::Vehicle->value:
                $selectedDocumentables = $document->vehicles()
                    ->withTrashed()
                    ->select(array_merge(
                        array_diff(Vehicle::$defaultSelectFields, ['updated_at', 'created_at']),
                        [
                            'vehicles.updated_at', 'vehicles.created_at', 'specific_exterior_color', 'vin', 'kilometers', 'nl_registration_number',
                            'vehicle_model_free_text', 'variant_free_text', 'engine_free_text', 'transmission_free_text',
                        ]
                    ))
                    ->with(['make:id,name', 'vehicleModel:id,name', 'engine:id,name', 'variant:id,name', 'creator', 'calculation:vehicleable_type,vehicleable_id,rest_bpm_indication,leges_vat'])
                    ->get();

                break;
            case DocumentableType::Service_vehicle->value:
                $selectedDocumentables = $document->serviceVehicles()
                    ->withTrashed()
                    ->select(array_merge(
                        array_diff(ServiceVehicle::$defaultSelectFields, ['updated_at', 'created_at']),
                        ['service_vehicles.updated_at', 'service_vehicles.created_at', 'current_registration', 'first_registration_date', 'kilometers']
                    ))
                    ->with(['make:id,name', 'vehicleModel:id,name', 'engine:id,name', 'variant:id,name', 'creator'])
                    ->get();

                break;
            case DocumentableType::Sales_order_down_payment->value:
            case DocumentableType::Sales_order->value:
                $selectedDocumentables = $document->salesOrders()
                    ->withTrashed()
                    ->select(SalesOrder::$pdfSelectFields)
                    ->with([
                        'serviceLevel:id,name',
                    ])->get();

                break;
            case DocumentableType::Service_order->value:
                $selectedDocumentables = $document->serviceOrders()
                    ->withTrashed()
                    ->select(array_merge(
                        array_diff(ServiceOrder::$defaultSelectFields, ['created_at', 'updated_at']),
                        ['service_orders.created_at', 'service_orders.updated_at']
                    ))
                    ->with([
                        'creator',
                    ])->get();

                break;
            case DocumentableType::Work_order->value:
                $selectedDocumentables = $document->workOrders()
                    ->withTrashed()
                    ->select(array_merge(
                        array_diff(WorkOrder::$defaultSelectFields, ['created_at', 'updated_at']),
                        ['work_orders.created_at', 'work_orders.updated_at']
                    ))
                    ->with(['creator'])->get();

                break;
            default:
                throw new Exception();
        }

        $templatePath = $this->getPdfTemplateByDocumentStatus($document->status);

        $companyPdfAssets = CompanyService::getPdfAssets();

        $pdfService = new PdfService(
            $templatePath,
            [
                'document'              => $document,
                'totalBpmAndLeges'      => $this->calculateBpmAndLeges($selectedDocumentables, $document->documentable_type->value),
                'selectedDocumentables' => $selectedDocumentables,
                'headerDocumentsImage'  => $companyPdfAssets['pdf_header_documents_image'],
                'signatureImage'        => $companyPdfAssets['pdf_signature_image'],
            ],
            [
                'fontDir' => storage_path('fonts'),
            ]
        );

        return $pdfService->setPaginator(new Paginator(x: 40, y: 810))
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
            )->generate();
    }

    /**
     * Calculate the total rest_bpm_indication and leges_vat shown in the pdf
     *
     * @param Collection $selectedDocumentables
     * @param $documentableType
     * @return string[]
     */
    private function calculateBpmAndLeges(Collection $selectedDocumentables, $documentableType): array
    {
        if (! in_array($documentableType, [DocumentableType::Pre_order_vehicle->value, DocumentableType::Vehicle->value])) {
            return [
                'totalBpm'   => '',
                'totalLeges' => '',
            ];
        }

        $totalBpm = 0;
        $totalLeges = 0;
        foreach ($selectedDocumentables as $selectedDocumentable) {
            $totalBpm += CurrencyHelper::convertCurrencyToUnits($selectedDocumentable->calculation->rest_bpm_indication);
            $totalLeges += CurrencyHelper::convertCurrencyToUnits($selectedDocumentable->calculation->leges_vat);
        }

        $totalBpm = $totalBpm == 0 ? '' : CurrencyHelper::convertUnitsToCurrency($totalBpm);
        $totalLeges = $totalLeges == 0 ? '' : CurrencyHelper::convertUnitsToCurrency($totalLeges);

        return [
            'totalBpm'   => $totalBpm,
            'totalLeges' => $totalLeges,
        ];
    }

    private function generateFileName(Document $document): false|string
    {
        $templatePath = $this->getPdfTemplateByDocumentStatus($document->status);

        $templatePathArr = explode('/', $templatePath);

        return end($templatePathArr);
    }

    /**
     * Return dataTable of Documents by provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getDocumentsDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(Document::$defaultSelectFields)
        ))
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('status', __('Status'), true, true)
            ->setColumn('documentable_type', __('Document Type'), true, true)
            ->setColumn('payment_condition', __('Payment Condition'), true, true)
            ->setColumn('total_price_include_vat', __('Total Price'), true, true)
            ->setColumn('created_at', __('Date'), true, true)
            ->setEnumColumn('status', DocumentStatus::class)
            ->setEnumColumn('Invoicing type', DocumentableType::class)
            ->setEnumColumn('payment_condition', PaymentCondition::class)
            ->setDateColumn('created_at', 'd.m.Y H:i')
            ->setPriceColumn('total_price_include_vat');

        return $dataTable;
    }

    private function getPdfTemplateByDocumentStatus(DocumentStatus $documentStatus)
    {
        if (! isset(self::TEMPLATES_MAP[$documentStatus->value])) {
            throw new InvalidArgumentException('Invalid document type');
        }

        return self::TEMPLATES_MAP[$documentStatus->value];
    }
}
