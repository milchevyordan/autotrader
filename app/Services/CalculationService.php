<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Calculation;
use App\Models\SalesOrder;
use App\Models\Vehicle;
use App\Support\CurrencyHelper;

class CalculationService extends Service
{
    /**
     * Update.
     *
     * @param  SalesOrder $salesOrder
     * @return void
     */
    public static function updateCalculation(SalesOrder $salesOrder): void
    {
        Calculation::upsert(
            self::getUpdatedVehicleCalculations($salesOrder),
            ['vehicleable_type', 'vehicleable_id'],
            ['purchase_cost_items_services', 'sale_price_net_including_services_and_products', 'sale_price_services_and_products', 'discount', 'sales_price_incl_vat_or_margin', 'vat', 'sales_price_total']
        );
    }

    /**
     * Calculate vehicle Purchase costs Items and Services and Margin on Items and Services.
     *
     * @param  SalesOrder $salesOrder
     * @return array
     */
    private static function getUpdatedVehicleCalculations(SalesOrder $salesOrder): array
    {
        $salesOrder->load([
            'orderItems',
            'orderServices',
            'vehicles:id',
            'vehicles.calculation:vehicleable_type,vehicleable_id,sales_price_net,vat_percentage,rest_bpm_indication,leges_vat',
        ]);

        $vehiclesCount = $salesOrder->vehicles->count();
        $salesPriceServiceItemsPerVehicleUnits =
            CurrencyHelper::convertCurrencyToUnits($salesOrder->total_sales_price_service_items) /
            $vehiclesCount;

        $purchasePriceOrderItemsTotal = $salesOrder->orderItems->sum(function ($orderItem) {
            return CurrencyHelper::convertCurrencyToUnits($orderItem->item?->purchase_price);
        });

        $purchasePriceOrderServicesTotal = $salesOrder->orderServices->sum(function ($orderService) {
            return CurrencyHelper::convertCurrencyToUnits($orderService->purchase_price);
        });

        $purchaseCostItemsServices = $purchasePriceOrderItemsTotal + $purchasePriceOrderServicesTotal;

        $discountUnits = CurrencyHelper::convertCurrencyToUnits($salesOrder->discount);

        $calculationUpsert = [];
        foreach ($salesOrder->vehicles as $vehicle) {
            $priceNetUnits =
                CurrencyHelper::convertCurrencyToUnits(
                    $vehicle->calculation->sales_price_net,
                ) + $discountUnits + $salesPriceServiceItemsPerVehicleUnits;
            $vatUnits = $priceNetUnits * ($vehicle->calculation->vat_percentage / 100);
            $bpmUnits = CurrencyHelper::convertCurrencyToUnits(
                $vehicle->calculation->rest_bpm_indication,
            );
            $regFeesUnits = CurrencyHelper::convertCurrencyToUnits(
                $vehicle->calculation->leges_vat,
            );

            $priceBrutto = $priceNetUnits + $vatUnits + $bpmUnits + $regFeesUnits;

            $calculationUpsert[] = [
                'vehicleable_type'                               => Vehicle::class,
                'vehicleable_id'                                 => $vehicle->id,
                'purchase_cost_items_services'                   => $purchaseCostItemsServices,
                'sale_price_services_and_products'               => $salesPriceServiceItemsPerVehicleUnits,
                'discount'                                       => $discountUnits,
                'sale_price_net_including_services_and_products' => $priceNetUnits,
                'vat'                                            => $vatUnits,
                'sales_price_incl_vat_or_margin'                 => $priceNetUnits + $vatUnits,
                'sales_price_total'                              => $priceBrutto,
            ];
        }

        return $calculationUpsert;
    }
}
