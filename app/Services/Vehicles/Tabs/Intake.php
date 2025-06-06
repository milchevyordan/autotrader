<?php

declare(strict_types=1);

namespace App\Services\Vehicles\Tabs;

use App\Interfaces\DataTableProviderContract;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;

class Intake implements DataTableProviderContract
{
    public function getDataTable(): DataTable
    {
        return (new DataTable(
            Vehicle::inThisCompany()
                ->select('id', 'make_id', 'vehicle_model_id', 'vin')
                ->whereHas('transportOrderInbound')
        ))
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            // ->setRelation('workflow.finishedStepsManagement')
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('transportOrderInbound.deliveryLocation')
            ->setRelation('transportOrderInbound.statuses')
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', 'VIN', true, true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('transportOrderInbound.deliveryLocation.address', __('Current Location'), true)
            ->setColumn('transportOrderInbound.statuses', __('Vehicle Intake'))
            ->setColumn('workflow.hasUploadedVehicleIntakeForm', __('Intake Status'))
            ->setColumn('transportOrderInbound.statuses', __('Vehicle Inside'))
            ->run();
    }
}
