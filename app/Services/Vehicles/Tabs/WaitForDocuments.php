<?php

declare(strict_types=1);

namespace App\Services\Vehicles\Tabs;

use App\Enums\Transmission;
use App\Interfaces\DataTableProviderContract;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;
use App\Services\Workflow\WorkflowService;

class WaitForDocuments implements DataTableProviderContract
{
    public function getDataTable(): DataTable
    {
        return (new DataTable(
            Vehicle::inThisCompany()
                ->select('id', 'supplier_company_id', 'make_id', 'vehicle_model_id', 'transmission', 'vin')
        ))
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('transportOrderInbound')
            ->setRelation('transportOrderInbound.statuses')
            ->setRelation('salesOrder.customerCompany', ['id', 'name'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', 'VIN', true, true)
            ->setColumn('transportOrderInbound.statuses', __('Vehicle Inside'))
            ->setColumn('supplierCompany.name', __('Supplier'), true, true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('transmission', __('Transmission'), true, true)
            ->setColumn('salesOrder.customerCompany.name', __('End User / Customer'), true)
            ->setEnumColumn('transmission', Transmission::class)
            ->run(config('app.default.pageResults', 10), function ($model) {
                // WorkflowService::filterWorkFlowNotFinishedSteps($model, ['hasReceivedOriginalDocuments']);

                return $model;
            });
    }
}
