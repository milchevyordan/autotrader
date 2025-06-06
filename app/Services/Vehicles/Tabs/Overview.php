<?php

declare(strict_types=1);

namespace App\Services\Vehicles\Tabs;

use App\Enums\Transmission;
use App\Interfaces\DataTableProviderContract;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;

class Overview implements DataTableProviderContract
{
    public function getDataTable(): DataTable
    {
        return (new DataTable(
            Vehicle::inThisCompany()
                ->select(
                    'id',
                    'vin',
                    'supplier_company_id',
                    'make_id',
                    'vehicle_model_id',
                    'engine_id',
                    'transmission',
                    'nl_registration_number',
                    'option',
                )
        ))
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('engine', ['id', 'name'])
            ->setRelation('transportOrders')
            ->setRelation('transportOrderInbound.deliveryLocation')
            ->setRelation('transportOrderInbound.statuses')
            ->setRelation('salesOrder.customerCompany', ['id', 'name'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', __('VIN'), true, true)
            ->setColumn('supplierCompany.name', __('Supplier'), true, true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('engine.name', __('Engine'), true, true)
            ->setColumn('transmission', __('Transmission'), true, true)
            ->setColumn('salesOrder.customerCompany.name', __('End User / Customer'), true)
            ->setColumn('transportOrderInbound.statuses', __('Vehicle Inside'))
            ->setColumn('workflow.hasReceivedOriginalDocuments', __('Received Original Documents'))
            ->setColumn('workflow.hasPassedSampleInspection', __('Passed Sample Inspection'))
            ->setColumn('workflow.examination', __('Examination'))
            ->setColumn('workflow.hasUploadedBpmDeclaration', __('Uploaded Bpm Declaration'))
            ->setColumn('workflow.hasSentBpmInvoice', __('Sent Bpm Invoice'))
            ->setColumn('nl_registration_number', __('NL Registration Number'), true, true)
            ->setColumn('transportOrders', __('Transport Orders'))
            ->setColumn('transportOrderInbound.deliveryLocation.address', __('Current Location'), true)
            ->setColumn('option', __('Options'), true, true)
            ->setColumn('number_of_vehicles', __('Number Of Vehicles'))
            ->setEnumColumn('transmission', Transmission::class)
            ->run();
    }
}
