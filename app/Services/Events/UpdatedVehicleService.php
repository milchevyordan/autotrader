<?php

namespace App\Services\Events;

use App\Enums\NationalEuOrWorldType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Vehicle;
use App\Support\CurrencyHelper;

class UpdatedVehicleService
{
    private Vehicle $vehicle;

    private array $validatedRequest;

    private array $oldPricesPurchaseOrder;

    private array $oldPricesSalesOrder;

    public function __construct(Vehicle $vehicle, array $validatedRequest)
    {
        $this->vehicle = $vehicle;
        $this->validatedRequest = $validatedRequest;
        $this->oldPricesPurchaseOrder = [
            'fee_intermediate'     => $vehicle->calculation->fee_intermediate,
            'net_purchase_price'   => $vehicle->calculation->net_purchase_price,
            'transport_inbound'    => $vehicle->calculation->transport_inbound,
            'vat'                  => $vehicle->calculation->vat,
            'bpm'                  => $vehicle->calculation->bpm,
            'total_purchase_price' => $vehicle->calculation->total_purchase_price,
        ];
        $this->oldPricesSalesOrder = [
            'total_purchase_price'           => $vehicle->calculation->total_purchase_price,
            'costs_of_damages'               => $vehicle->calculation->costs_of_damages,
            'transport_inbound'              => $vehicle->calculation->transport_inbound,
            'fee_intermediate'               => $vehicle->calculation->fee_intermediate,
            'sales_price_net'                => $vehicle->calculation->sales_price_net,
            'sales_price_incl_vat_or_margin' => $vehicle->calculation->sales_price_incl_vat_or_margin,
            'sales_margin'                   => $vehicle->calculation->sales_margin,
            'leges_vat'                      => $vehicle->calculation->leges_vat,
            'rest_bpm_indication'            => $vehicle->calculation->rest_bpm_indication,
        ];
    }

    /**
     * Check and sync purchase order and sales order to latest changes in vehicles
     *
     * @return void
     */
    public function updateModules(): void
    {
        $this->checkAndUpdatePurchaseOrder();
        $this->checkAndUpdateSalesOrder();
    }

    /**
     * Check for changes in prices
     *
     * @param array $oldPrices
     * @param array $newPrices
     * @return bool
     */
    private function checkForChanges(array $oldPrices, array $newPrices): bool
    {
        foreach ($newPrices as $key => $newValue) {
            $oldValue = $oldPrices[$key];

            if (($oldValue === null || $oldValue === '') && ($newValue === null || $newValue === '')) {
                continue;
            }

            if ($oldValue != $newValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if purchase order is before approved and sync summary financial information
     *
     * @return void
     */
    private function checkAndUpdatePurchaseOrder(): void
    {
        if (! $this->checkForChanges($this->oldPricesPurchaseOrder, array_intersect_key($this->validatedRequest, $this->oldPricesPurchaseOrder))) {
            return;
        }

        $purchaseOrder = $this->vehicle
            ->purchaseOrder()
            ->select('id', 'status', 'currency_rate', 'type')
            ->with([
                'vehicles:id',
                'vehicles.calculation:vehicleable_type,vehicleable_id,fee_intermediate,net_purchase_price,transport_inbound,vat,bpm,total_purchase_price',
            ])
            ->first();

        if (! $purchaseOrder || $purchaseOrder->status->value > PurchaseOrderStatus::Approved->value) {
            return;
        }

        $sum = [
            'fee_intermediate'     => 0,
            'net_purchase_price'   => 0,
            'transport_inbound'    => 0,
            'vat'                  => 0,
            'bpm'                  => 0,
            'total_purchase_price' => 0,
        ];
        foreach ($purchaseOrder->vehicles as $vehicle) {
            $sum['fee_intermediate'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->fee_intermediate);
            $sum['net_purchase_price'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->net_purchase_price);
            $sum['transport_inbound'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->transport_inbound);
            $sum['vat'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->vat);
            $sum['bpm'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->bpm);
            $sum['total_purchase_price'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->total_purchase_price);
        }

        $totalPurchasePrice = CurrencyHelper::convertUnitsToCurrency($sum['total_purchase_price']);

        $purchaseOrder->update([
            'total_fee_intermediate_supplier'  => CurrencyHelper::convertUnitsToCurrency($sum['fee_intermediate']),
            'total_purchase_price_exclude_vat' => CurrencyHelper::convertUnitsToCurrency($sum['net_purchase_price']),
            'total_transport'                  => CurrencyHelper::convertUnitsToCurrency($sum['transport_inbound']),
            'total_vat'                        => CurrencyHelper::convertUnitsToCurrency($sum['vat']),
            'bpm'                              => $purchaseOrder->type != NationalEuOrWorldType::EU->value ? CurrencyHelper::convertUnitsToCurrency($sum['bpm']) : null,
            'total_purchase_price'             => $totalPurchasePrice,
            'total_purchase_price_include_vat' => $totalPurchasePrice,
            'total_purchase_price_eur'         => CurrencyHelper::convertUnitsToCurrency($sum['total_purchase_price'] * ($purchaseOrder->currency_rate ?? 0)),
        ]);
    }

    /**
     * Check if sales order is before approved and sync summary financial information
     *
     * @return void
     */
    private function checkAndUpdateSalesOrder(): void
    {
        if (! $this->checkForChanges($this->oldPricesSalesOrder, array_intersect_key($this->validatedRequest, $this->oldPricesSalesOrder))) {
            return;
        }

        $salesOrder = $this->vehicle
            ->salesOrder()
            ->select('id', 'status', 'total_sales_price_exclude_vat', 'discount', 'is_brutto', 'vat_percentage', 'total_registration_fees')
            ->with([
                'vehicles:id',
                'vehicles.calculation:vehicleable_type,vehicleable_id,total_purchase_price,costs_of_damages,transport_inbound,fee_intermediate,sales_price_net,sales_price_incl_vat_or_margin,sales_margin,leges_vat,rest_bpm_indication',
                'orderItems:id,orderable_type,orderable_id,sale_price', 'orderItems.item:id,purchase_price', 'orderServices:orderable_type,orderable_id,purchase_price,sale_price',
            ])
            ->first();

        if (! $salesOrder || $salesOrder->status->value > SalesOrderStatus::Approved->value) {
            return;
        }

        $sum = [
            'total_purchase_price'           => 0,
            'costs_of_damages'               => 0,
            'transport_inbound'              => 0,
            'fee_intermediate'               => 0,
            'sales_price_net'                => 0,
            'sales_price_incl_vat_or_margin' => 0,
            'sales_margin'                   => 0,
            'leges_vat'                      => 0,
            'rest_bpm_indication'            => 0,
        ];
        foreach ($salesOrder->vehicles as $vehicle) {
            $sum['total_purchase_price'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->total_purchase_price);
            $sum['costs_of_damages'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->costs_of_damages);
            $sum['transport_inbound'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->transport_inbound);
            $sum['fee_intermediate'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->fee_intermediate);
            $sum['sales_price_net'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->sales_price_net);
            $sum['sales_price_incl_vat_or_margin'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->sales_price_incl_vat_or_margin);
            $sum['sales_margin'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->sales_margin);
            $sum['leges_vat'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->leges_vat);
            $sum['rest_bpm_indication'] += CurrencyHelper::convertCurrencyToUnits($vehicle->calculation->rest_bpm_indication);
        }

        $vehiclesCount = $salesOrder->vehicles->count();

        $prices = [
            'all_items_and_services_purchase_price' => 0,
            'all_items_and_services_sale_price'     => 0,
            'total_vat_units'                       => 0,
            'total_bpm_units'                       => 0,
            'total_sales_price'                     => 0,
        ];

        foreach ($salesOrder->orderItems as $orderItem) {
            $prices['all_items_and_services_purchase_price'] += CurrencyHelper::convertCurrencyToUnits($orderItem->item->purchase_price);
            $prices['all_items_and_services_sale_price'] += CurrencyHelper::convertCurrencyToUnits($orderItem->sale_price);
        }
        foreach ($salesOrder->orderServices as $orderService) {
            $prices['all_items_and_services_purchase_price'] += CurrencyHelper::convertCurrencyToUnits($orderService->purchase_price);
            $prices['all_items_and_services_sale_price'] += CurrencyHelper::convertCurrencyToUnits($orderService->sale_price);
        }

        $prices['total_sales_price_exclude_vat_with_items_units'] = $sum['sales_price_net'] + ($prices['all_items_and_services_sale_price'] + CurrencyHelper::convertCurrencyToUnits($salesOrder->discount)) * $vehiclesCount;

        if ($salesOrder->is_brutto) {
            $prices['total_vat_units'] = $prices['total_sales_price_exclude_vat_with_items_units'] * ($salesOrder->vat_percentage / 100);
            $prices['total_bpm_units'] = $sum['rest_bpm_indication'];
            $prices['total_sales_price'] = $prices['total_sales_price_exclude_vat_with_items_units'] + $prices['total_vat_units'] + $prices['total_bpm_units'] + $sum['leges_vat'];
        }

        $salesOrder->update([
            'total_vehicles_purchase_price'   => CurrencyHelper::convertUnitsToCurrency($sum['total_purchase_price']),
            'total_costs'                     => CurrencyHelper::convertUnitsToCurrency($sum['costs_of_damages'] + $sum['transport_inbound'] + ($prices['all_items_and_services_purchase_price'] * $vehiclesCount)),
            'total_sales_price_service_items' => CurrencyHelper::convertUnitsToCurrency($prices['all_items_and_services_sale_price'] * $vehiclesCount),
            'total_fee_intermediate_supplier' => CurrencyHelper::convertUnitsToCurrency($sum['fee_intermediate']),
            'total_sales_price_exclude_vat'   => CurrencyHelper::convertUnitsToCurrency($sum['sales_price_net']),
            'total_sales_price_include_vat'   => CurrencyHelper::convertUnitsToCurrency($sum['sales_price_incl_vat_or_margin']),
            'total_sales_margin'              => CurrencyHelper::convertUnitsToCurrency($sum['sales_margin']),
            'total_registration_fees'         => CurrencyHelper::convertUnitsToCurrency($sum['leges_vat']),
            'total_sales_excl_vat_with_items' => CurrencyHelper::convertUnitsToCurrency($prices['total_sales_price_exclude_vat_with_items_units']),
            'total_vat'                       => CurrencyHelper::convertUnitsToCurrency($prices['total_vat_units']),
            'total_bpm'                       => CurrencyHelper::convertUnitsToCurrency($prices['total_bpm_units']),
            'total_sales_price'               => CurrencyHelper::convertUnitsToCurrency($prices['total_sales_price']),
        ]);
    }
}
