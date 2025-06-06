<?php

declare(strict_types=1);

namespace App\Services\Vehicles\Tabs;

use App\Interfaces\DataTableProviderContract;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;

class FilterPage implements DataTableProviderContract
{
    public function getDataTable(): DataTable
    {
        return (new DataTable(
            Vehicle::inThisCompany()
                ->select('id', 'vin', 'make_id', 'vehicle_model_id', 'nl_registration_number')
        ))
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            // ->setRelation('workflow.finishedStepsManagement')
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('transportOrderInbound.deliveryLocation')
            ->setRelation('salesOrder.customerCompany', ['id', 'name'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', __('VIN'), true, true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('salesOrder.customerCompany.name', __('End User / Customer'), true)
            ->setColumn('workflow.hasUploadedBpmDeclaration', __('Uploaded Bpm Declaration'))
            ->setColumn('nl_registration_number', __('NL Registration Number'), true, true)
            ->setColumn('transportOrderInbound.deliveryLocation.address', __('Current Location'), true)
            ->run();
    }
}
