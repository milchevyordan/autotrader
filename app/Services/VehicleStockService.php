<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentableType;
use App\Enums\DocumentStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\VehicleStock;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class VehicleStockService extends Service
{
    private array $updateStatuses = [];

    public function handlePurchaseOrderStatusUpdate(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->status <= PurchaseOrderStatus::Concept->value) {
            return;
        }

        $purchaseOrder->vehicles->load([
            'purchaseOrder:id,status,total_payment_amount',
            'salesOrder:id',
            'documents',
            'documents.statuses',
        ]);

        $this->handleStockStatusChange($purchaseOrder->vehicles);
    }

    public function handleSalesOrderStatusUpdate(SalesOrder $salesOrder): void
    {
        if ($salesOrder->status <= SalesOrderStatus::Concept->value) {
            return;
        }

        $salesOrder->vehicles->load([
            'purchaseOrder:id,status,total_payment_amount',
            'salesOrder:id',
            'documents',
            'documents.statuses',
        ]);

        $this->handleStockStatusChange($salesOrder->vehicles);
    }

    /**
     * Handle the stock status change for a collection of vehicles.
     *
     * @param  Collection<Vehicle> $vehicles
     * @return void
     */
    private function handleStockStatusChange(Collection $vehicles): void
    {
        $statusVehicles = new SupportCollection();

        foreach ($vehicles as $vehicle) {
            $purchaseOrder = $vehicle->purchaseOrder->first();
            $salesOrder = $vehicle->salesOrder->first();

            if ($purchaseOrder && ! $salesOrder) {
                // Update stock status based on payment amount
                $status = $purchaseOrder->total_payment_amount
                    ? VehicleStock::Stock_pipeline
                    : VehicleStock::Stock;

                $statusVehicles->push(['status' => $status->value, 'id' => $vehicle->id]);
            }

            if ($purchaseOrder && $salesOrder) {
                $salesOrderInvoicePaid = $vehicle->documents
                    ->where('documentable_type', DocumentableType::Sales_order)
                    ->where('status', DocumentStatus::Paid->value)
                    ->first();

                $status = ($purchaseOrder->total_payment_amount && ! $salesOrderInvoicePaid)
                    ? VehicleStock::Financial_stock
                    : VehicleStock::Sold;

                $statusVehicles->push(['status' => $status->value, 'id' => $vehicle->id]);
            }
        }

        $groupedStatuses = $statusVehicles->groupBy('status');

        foreach ($groupedStatuses as $statusId => $vehicles) {

            $vehicleIds = $vehicles->pluck('id')->toArray();

            Vehicle::whereIn('id', $vehicleIds)
                ->update([
                    'stock' => $statusId,
                ]);
        }

    }
}
