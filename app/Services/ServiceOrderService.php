<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ServiceOrderStatus;
use App\Http\Requests\StoreServiceOrderRequest;
use App\Http\Requests\UpdateServiceOrderRequest;
use App\Models\ServiceOrder;
use App\Services\DataTable\DataTable;
use App\Services\DataTable\RawOrdering;
use App\Services\Files\UploadHelper;
use App\Services\Vehicles\ServiceVehicleService;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class ServiceOrderService extends Service
{
    /**
     * The service order model.
     *
     * @var ServiceOrder
     */
    public ServiceOrder $serviceOrder;

    /**
     * Vehicle id that will be automatically selected in create.
     *
     * @var int
     */
    private int $queryVehicleId;

    /**
     * Create a new ServiceOrderService instance.
     */
    public function __construct()
    {
        $this->setServiceOrder(new ServiceOrder());
    }

    /**
     * Service level creation.
     *
     * @param StoreServiceOrderRequest $request
     * @return self
     */
    public function createServiceOrder(StoreServiceOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $authId = auth()->id();

        $serviceOrder = new ServiceOrder($validatedRequest);
        $serviceOrder->creator_id = $authId;
        $serviceOrder->save();
        $serviceOrder->sendInternalRemarks($validatedRequest);

        $orderItemsToCreate = collect($validatedRequest['items'] ?? [])
            ->filter(function ($orderItem) {
                return $orderItem['shouldBeAdded'];
            })
            ->all();
        $serviceOrder->orderItems()->createMany($orderItemsToCreate);
        $serviceOrder->orderServices()->createMany($validatedRequest['additional_services'] ?? []);

        $serviceOrder
            ->saveWithImages(UploadHelper::uploadMultipleImages($validatedRequest, 'images'), 'images')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'files'), 'files')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'vehicleDocuments'), 'vehicleDocuments');

        $this->setServiceOrder($serviceOrder);

        return $this;
    }

    /**
     * Service level update.
     *
     * @param UpdateServiceOrderRequest $request
     * @return self
     */
    public function updateServiceOrder(UpdateServiceOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $serviceOrder = $this->getServiceOrder();

        $serviceOrder->update($validatedRequest);
        $serviceOrder->sendInternalRemarks($validatedRequest);

        $serviceOrder->orderServices()->delete();
        $serviceOrder->orderServices()->createMany($validatedRequest['additional_services'] ?? []);

        $serviceOrder->orderItems()->delete();
        $orderItemsToCreate = collect($validatedRequest['items'] ?? [])
            ->filter(function ($orderItem) {
                return $orderItem['shouldBeAdded'];
            })
            ->all();
        $serviceOrder->orderItems()->createMany($orderItemsToCreate);

        $serviceOrder
            ->saveWithImages(UploadHelper::uploadMultipleImages($validatedRequest, 'images'), 'images')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'files'), 'files')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'vehicleDocuments'), 'vehicleDocuments');

        $this->setServiceOrder($serviceOrder);

        return $this;
    }

    /**
     * Get the model serviceOrder.
     *
     * @return ServiceOrder
     */
    private function getServiceOrder(): ServiceOrder
    {
        return $this->serviceOrder;
    }

    /**
     * Set the model of the serviceOrder.
     *
     * @param  ServiceOrder $serviceOrder
     * @return self
     */
    public function setServiceOrder(ServiceOrder $serviceOrder): self
    {
        $this->serviceOrder = $serviceOrder;

        return $this;
    }

    /**
     * Return service orders in this company datatable shown in index page and document create and edit pages.
     *
     * @param  null|array $additionalRelations
     * @param  ?bool      $withTrashed
     * @return DataTable
     */
    public function getIndexMethodDataTable(?array $additionalRelations = [], ?bool $withTrashed = false): DataTable
    {
        $dataTable = (new DataTable(
            ServiceOrder::inThisCompany()
                ->when($withTrashed, function ($query) {
                    return $query->withTrashed();
                })
                ->select(ServiceOrder::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setRelation('customer', ['id', 'full_name'])
            ->setRelation('customerCompany', ['id', 'name'])
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true, true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('customerCompany.name', __('Customer Company'), true, true)
            ->setColumn('customer.full_name', __('Contact Person Customer'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', ServiceOrderStatus::class);

        foreach ($additionalRelations as $relation) {
            $dataTable->setRelation($relation);
        }

        return $dataTable;
    }

    /**
     * Get the vehicles datatable structure.
     *
     * @param  ?bool     $withTrashed
     * @return DataTable
     */
    public function getserviceVehiclesDataTable(?bool $withTrashed = false): DataTable
    {
        return ServiceVehicleService::getDataTable($withTrashed)
            ->setTimestamps();
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
     * Get vehicles datatable used in create form.
     *
     * @return DataTable
     */
    public function getCreateMethodVehiclesTable(): DataTable
    {
        $userHasSearched = request(null)->input('filter.global');

        $dataTable = $this->getserviceVehiclesDataTable();

        if ($queryVehicleId = $this->getQueryVehicleId()) {
            $dataTable->setRawOrdering(new RawOrdering("FIELD(service_vehicles.id, {$queryVehicleId}) DESC"));
        }

        return $dataTable->run(config('app.default.pageResults', 10), function ($model) use ($userHasSearched) {
            return ($userHasSearched) ?
                $model->withoutTrashed()->whereDoesntHave('serviceOrder') :
                $model->withoutTrashed();
        });
    }

    /**
     * Get vehicles datatable used in edit form.
     *
     * @return DataTable
     */
    public function getEditMethodVehiclesTable(): DataTable
    {
        $userHasSearched = request(null)->input('filter.global');
        $serviceOrder = $this->getServiceOrder();

        return $this->getserviceVehiclesDataTable(true)
            ->run(config('app.default.pageResults', 10), function ($model) use ($serviceOrder, $userHasSearched) {
                return ! $serviceOrder->service_vehicle_id && $userHasSearched ?
                    $model->withoutTrashed()->whereDoesntHave('serviceOrder') :
                    $model->withTrashed()->where('id', $serviceOrder->service_vehicle_id);
            });
    }

    /**
     * Update the service order status.
     *
     * @param  int                 $status
     * @return ServiceOrderService
     * @throws Exception
     */
    public function updateServiceOrderStatus(int $status): self
    {
        $serviceOrder = $this->getServiceOrder();

        switch ($status) {
            case ServiceOrderStatus::Submitted->value:
                if (! auth()->user()->can('submit-service-order')) {
                    throw new Exception(__('You do not have the permission.'));
                }

                if (! $serviceOrder->service_vehicle_id) {
                    throw new Exception(__('Not selected any vehicles, need to select Vehicle.'));
                }

                break;
            default:
                break;
        }

        $serviceOrder->status = $status;
        $serviceOrder->save();

        if (1 != $status) {
            $serviceOrder->statuses()->updateOrCreate(['status' => $status], ['created_at' => now()]);
        }

        return $this;
    }

    /**
     * Return datatable of Service Orders by provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getServiceOrdersDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(ServiceOrder::$defaultSelectFields)
        ))
            ->setRelation('customer', ['id', 'full_name'])
            ->setRelation('customerCompany', ['id', 'name'])
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('status', __('Status'), true, true)
            ->setColumn('customerCompany.name', __('Customer'), true)
            ->setColumn('customer.full_name', __('Contact Person Customer'), true)
            ->setTimestamps()
            ->setEnumColumn('status', ServiceOrderStatus::class);

        return $dataTable;
    }
}
