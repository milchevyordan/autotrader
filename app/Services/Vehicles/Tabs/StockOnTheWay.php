<?php

declare(strict_types=1);

namespace App\Services\Vehicles\Tabs;

use App\Enums\PurchaseOrderStatus;
use App\Enums\Transmission;
use App\Enums\TransportOrderStatus;
use App\Interfaces\DataTableProviderContract;
use App\Models\Vehicle;
use App\Services\DataTable\DataTable;

class StockOnTheWay implements DataTableProviderContract
{
    public function getDataTable(): DataTable
    {
        return (new DataTable(
            Vehicle::inThisCompany()
                ->whereHas('transportOrderInbound', function ($query) {
                    $query->whereNot('status', TransportOrderStatus::Cmr_waybill);
                })
                ->whereHas('purchaseOrder', function ($query) {
                    $query->where('status', PurchaseOrderStatus::Completed);
                })
                ->select('id', 'make_id', 'vehicle_model_id', 'engine_id', 'variant_id', 'transmission', 'kilometers', 'specific_exterior_color', 'vin')
        ))
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setRelation('workflow.finishedStepsManagement')
            ->setRelation('purchaseOrder.purchaser', ['id', 'full_name'])
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('engine', ['id', 'name'])
            ->setRelation('variant', ['id', 'name'])
            ->setRelation('calculation', ['id', 'vehicleable_id', 'vehicleable_type', 'total_purchase_price'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', 'VIN', true, true)
            ->setColumn('purchaseOrder.purchaser.full_name', __('Medewerker NCT / Company Purchaser'), true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('engine.name', __('Engine'), true, true)
            ->setColumn('variant.name', __('Variant'), true, true)
            ->setColumn('transmission', __('Transmission'), true, true)
            ->setColumn('kilometers', __('Kilometers'), true, true)
            ->setColumn('deel_one', __('Deel 1'))
            ->setColumn('specific_exterior_color', __('Color'), true, true)
            ->setColumn('workflow.documentsInbound', __('Documents Inbound'))
            ->setColumn('calculation.total_purchase_price', __('Total Purchase Price'), true, true)
            ->setColumn('nl_registration_number', __('NL Registration Number'), true, true)
            ->setPriceColumn('calculation.total_purchase_price')
            ->setEnumColumn('transmission', Transmission::class)
            ->run();
    }
}
