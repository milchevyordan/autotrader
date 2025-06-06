<?php

declare(strict_types=1);

namespace App\Services\Vehicles\Tabs;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Interfaces\DataTableProviderContract;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;

class Entered implements DataTableProviderContract
{
    public function getDataTable(): DataTable
    {
        return (new DataTable(
            Vehicle::inThisCompany()
                ->select('id', 'make_id', 'vehicle_model_id', 'supplier_company_id', 'vin')
                ->whereHas('transportOrders', function ($transportOrderQuery) {
                    $transportOrderQuery
                        ?->where('transport_type', TransportType::Inbound)
                        ->where('status', '<', TransportOrderStatus::Cmr_waybill->value);
                })
        ))
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('transportOrders')
            ->setRelation('documentLines')
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', 'VIN', true, true)
            ->setColumn('supplierCompany.name', __('Supplier'), true, true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('transportOrders', __('Transport Orders'))
            ->setColumn('document_lines', __('Document Line Price Include VAT'))
            ->run();
    }
}
