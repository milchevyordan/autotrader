<?php

declare(strict_types=1);

namespace App\Services\Vehicles\Tabs;

use App\Enums\Transmission;
use App\Interfaces\DataTableProviderContract;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;

class PlanningForTransport implements DataTableProviderContract
{
    public function getDataTable(): DataTable
    {
        return (new DataTable(
            Vehicle::inThisCompany()
                ->select('id', 'vin', 'make_id', 'vehicle_model_id', 'supplier_company_id', 'transmission', 'vin')
                ->whereHas('transportOrders')
        ))
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('documentLines')
            ->setRelation('transportOrders')
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', __('VIN'), true, true)
            ->setColumn('transportOrders', __('Transport Orders'))
            ->setColumn('supplierCompany.name', __('Supplier'), true, true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('transmission', __('Transmission'), true, true)
            ->setColumn('vin', 'VIN', true, true)
            ->setColumn('document_lines', __('Document Line Price Include VAT'))
            ->setEnumColumn('transmission', Transmission::class)
            ->setPriceColumn('calculation.sales_price_total')
            ->run();
    }
}
