<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderTaskStatus;
use App\Enums\WorkOrderType;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\StoreWorkOrdersFromVehicleRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Models\ServiceVehicle;
use App\Models\Vehicle;
use App\Models\Workflow;
use App\Models\WorkOrder;
use App\Services\DataTable\DataTable;
use App\Services\Files\UploadHelper;
use App\Support\CurrencyHelper;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class WorkOrderService extends Service
{
    /**
     * The workOrder model.
     *
     * @var WorkOrder
     */
    public WorkOrder $workOrder;

    /**
     * Create a new WorkOrderService instance.
     */
    public function __construct()
    {
        $this->setWorkOrder(new WorkOrder());
    }

    /**
     * Return work orders in this company datatable shown in index page and document create and edit pages.
     *
     * @param  null|array $additionalRelations
     * @param  ?bool      $withTrashed
     * @return DataTable
     */
    public function getIndexMethodDataTable(?array $additionalRelations = [], ?bool $withTrashed = false): DataTable
    {
        $dataTable = (new DataTable(
            WorkOrder::inThisCompany()
                ->when($withTrashed, function ($query) {
                    return $query->withTrashed();
                })
                ->select(WorkOrder::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('type', __('Type'), true, true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('total_price', __('Total Price'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', WorkOrderStatus::class)
            ->setEnumColumn('type', WorkOrderType::class)
            ->setPriceColumn('total_price');

        foreach ($additionalRelations as $relation => $relatedColumns) {
            $dataTable->setRelation($relation, $relatedColumns);
        }

        return $dataTable;
    }

    /**
     * Get the value of $this->workflowDataTable.
     *
     * @return DataTable<Workflow>
     */
    public function getVehicleDataTable(): DataTable
    {
        $selectedType = request(null)->input('type', $this->getWorkOrder()->type?->value);

        $builder = WorkOrderType::Service_vehicle->value == $selectedType ?
            ServiceVehicle::inThisCompany() :
            Vehicle::inThisCompany();

        return (new DataTable(
            $builder
                ->withTrashed()
                ->select('id', 'creator_id', 'make_id', 'vehicle_model_id', 'vin', 'created_at', 'updated_at', 'deleted_at')
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('variant', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('creator')
            ->setRelation('workflow', ['id', 'vehicleable_id', 'vehicleable_type'])
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', 'VIN', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setTimestamps();
    }

    /**
     * Get the workflow datatable shown in create form.
     *
     * @return DataTable
     */
    public function getCreateMethodVehicles(): DataTable
    {
        $userHasSearched = request(null)->input('filter.global');
        $selectedVehicleId = request()->input('filter.id');

        return $this->getVehicleDataTable()
            ->run(config('app.default.pageResults', 10), function ($model) use ($userHasSearched, $selectedVehicleId) {
                if (! $userHasSearched && ! $selectedVehicleId) {
                    return $model->whereNull('id');
                }

                if ($selectedVehicleId) {
                    return $model->whereDoesntHave('workOrder')->withoutTrashed()->where('id', $selectedVehicleId);
                }

                if ($userHasSearched) {
                    return $model->whereDoesntHave('workOrder')->withoutTrashed();
                }

            });
    }

    /**
     * Get the workflow datatable shown in create form.
     *
     * @return DataTable
     */
    public function getEditMethodVehicles(): DataTable
    {
        $workOrder = $this->getWorkOrder();

        return $this->getVehicleDataTable()
            ->run(config('app.default.pageResults', 10), function ($model) use ($workOrder) {
                return $model->where('id', $workOrder->vehicleable_id);
            });
    }

    /**
     * Set the model of the work order.
     *
     * @param  WorkOrder $workOrder
     * @return self
     */
    public function setWorkOrder(WorkOrder $workOrder): self
    {
        $this->workOrder = $workOrder;

        return $this;
    }

    /**
     * Get the model of the work order.
     *
     * @return WorkOrder
     */
    private function getWorkOrder(): WorkOrder
    {
        return $this->workOrder;
    }

    /**
     * Work order creation.
     *
     * @param StoreWorkOrderRequest $request
     * @return self
     * @throws Exception
     */
    public function createWorkOrder(StoreWorkOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $workOrder = new WorkOrder();
        $workOrder->fill($validatedRequest);
        switch ($validatedRequest['type']) {
            case WorkOrderType::Vehicle->value:
                $workOrder->vehicleable_type = Vehicle::class;

                break;
            case WorkOrderType::Service_vehicle->value:
                $workOrder->vehicleable_type = ServiceVehicle::class;

                break;
            default:
                throw new Exception(__('Type is required.'));
        }

        $workOrder->creator_id = auth()->id();

        $workOrder->save();

        $uploadedFiles = UploadHelper::uploadMultipleFiles($validatedRequest, 'files');
        $workOrder->saveWithFiles($uploadedFiles, 'files');
        $workOrder->sendInternalRemarks($validatedRequest);

        $this->setWorkOrder($workOrder);

        return $this;
    }

    /**
     * Work order update.
     *
     * @param UpdateWorkOrderRequest $request
     * @return self
     */
    public function updateWorkOrder(UpdateWorkOrderRequest $request): self
    {
        $validatedRequest = $request->validated();

        $workOrder = $this->getWorkOrder();

        $workOrder->update($validatedRequest);

        $uploadedFiles = UploadHelper::uploadMultipleFiles($validatedRequest, 'files');
        $workOrder->saveWithFiles($uploadedFiles, 'files');

        $workOrder->sendInternalRemarks($validatedRequest);

        return $this;
    }

    /**
     * Return datatable of Work Orders by provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getWorkOrdersDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(WorkOrder::$defaultSelectFields)
        ))
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('type', __('Type'), true, true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('total_price', __('Total Price'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', WorkOrderStatus::class)
            ->setEnumColumn('type', WorkOrderType::class)
            ->setPriceColumn('total_price');

        return $dataTable;
    }

    /**
     * Update the work order status.
     *
     * @param UpdateStatusRequest $request
     * @return WorkOrderService
     * @throws Exception
     */
    public function updateWorkOrderStatus(UpdateStatusRequest $request): self
    {
        $validatedRequest = $request->validated();

        $workOrder = $this->getWorkOrder();

        switch ($validatedRequest['status']) {
            case WorkOrderStatus::Completed->value:
                if (! $workOrder->tasks->every(function ($task) {
                    return $task->status->value == WorkOrderTaskStatus::Completed->value;
                })) {
                    throw new Exception(__('All tasks need to be completed before changing to this status'));
                }

                break;
            default:
                break;
        }

        $workOrder->status = $validatedRequest['status'];
        $workOrder->save();

        if (1 != $validatedRequest['status']) {
            $workOrder->statuses()->updateOrCreate(['status' => $validatedRequest['status']], ['created_at' => now()]);
        }

        return $this;
    }

    /**
     * Create work order from vehicle
     *
     * @param StoreWorkOrdersFromVehicleRequest $request
     * @return int
     */
    public function createWorkOrderFromVehicle(StoreWorkOrdersFromVehicleRequest $request): int
    {
        $validatedRequest = $request->validated();

        $workOrder = new WorkOrder([
            'type'           => $validatedRequest['type'],
            'vehicleable_id' => $validatedRequest['vehicleable_id'],
        ]);

        $workOrder->vehicleable_type = $validatedRequest['type'] == WorkOrderType::Vehicle->value ? Vehicle::class : ServiceVehicle::class;
        $workOrder->creator_id = auth()->id();

        $workOrder->save();

        OwnershipService::createAuthOwnership($workOrder);

        return $workOrder->id;
    }

    /**
     * Update work order price based on tasks price.
     *
     * @return void
     */
    public function workOrderTaskUpdated(): void
    {
        $workOrder = $this->getWorkOrder();

        $tasks = $workOrder->tasks;

        $completedTasksCount = $tasks->filter(function ($task) {
            return $task->status->value == WorkOrderTaskStatus::Completed->value;
        })->count();

        $newStatus = $completedTasksCount && ($completedTasksCount == $tasks->count()) ? WorkOrderStatus::Completed->value : WorkOrderStatus::Open->value;
        if ($newStatus != $workOrder->status->value) {
            $workOrder->status = $newStatus;
            if (1 != $newStatus) {
                $workOrder->statuses()->updateOrCreate(['status' => $newStatus], ['created_at' => now()]);
            }
        }

        $workOrder->total_price = CurrencyHelper::convertUnitsToCurrency($tasks->sum(function ($workOrderTask) {
            return CurrencyHelper::convertCurrencyToUnits($workOrderTask->actual_price);
        }));

        $workOrder->save();
    }
}
