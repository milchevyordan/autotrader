<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyAddressType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\ServiceOrderStatus;
use App\Enums\TransportableType;
use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\CompanyAddress;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\ServiceOrder;
use App\Models\ServiceVehicle;
use App\Models\Statusable;
use App\Models\TransportOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkflowProcess;
use App\Services\CacheService;
use App\Services\Files\UploadHelper;
use App\Services\Pdf\PdfService;
use App\Support\CurrencyHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends BaseSeeder
{
    private array $purchaseOrderIdsToUpdate = [];

    private array $salesOrderIdsToUpdate = [];

    private array $vehicleablesInserts = [];

    private array $workflowInserts = [];

    private array $transportablesInserts = [];

    private array $transportOrderIdsToUpdate = [];

    private array $serviceOrderUpserts = [];

    private $logo;

    /**
     * Create a new WorkflowSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->logo = (new CacheService())->getLogo();
    }

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        // for every company
        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $companyAdministratorId) {
            $usersCanCreateWorkflow = User::where('company_id', $companyId)
                ->whereHas('roles.permissions', function ($query) {
                    $query->where('name', 'create-workflow');
                })->get(['id'])->pluck('id')->all();

            if (empty($usersCanCreateWorkflow)) {
                continue;
            }

            $this->createVehicleWorkflows($companyId, $usersCanCreateWorkflow);
            $this->createServiceVehicleWorkflows($companyId, $usersCanCreateWorkflow);
        }

        DB::table('vehicleables')->insert($this->vehicleablesInserts);
        DB::table('transportables')->insert($this->transportablesInserts);
        DB::table('workflows')->insert($this->workflowInserts);

        $this->completePurchaseOrders();
        $this->completeSalesOrders();
        $this->completeTransportOrders();
        $this->completeServiceOrders();
    }

    private function createVehicleWorkflows($companyId, $usersCanCreateWorkflow): void
    {
        $vehiclesIdsMapped = self::mapBySupplier(Vehicle::whereHas('creator', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->whereDoesntHave('purchaseOrder')
            ->whereDoesntHave('salesOrder')
            ->limit(30));

        $purchaseOrderIdsMapped = self::mapBySupplier(PurchaseOrder::whereHas('creator', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->where('down_payment', true)
            ->limit(30));

        $salesOrderIds = SalesOrder::whereHas('creator', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->where('down_payment', true)
            ->where('status', SalesOrderStatus::Concept->value)
            ->limit(30)
            ->get(['id'])->pluck('id')->all();

        $workflowProcessIds = WorkflowProcess::where('company_id', $companyId)->get(['id'])->pluck('id')->all();

        foreach ($vehiclesIdsMapped as $supplierId => $vehicleIds) {
            foreach ($vehicleIds as $vehicleId) {
                $purchaseOrderId = null;
                if (! empty($purchaseOrderIdsMapped[$supplierId])) {
                    $purchaseOrderId = $this->faker->randomElement($purchaseOrderIdsMapped[$supplierId]);
                    // Remove the used purchase order ID from the available IDs
                    $key = array_search($purchaseOrderId, $purchaseOrderIdsMapped[$supplierId], true);
                    if ($key !== false) {
                        unset($purchaseOrderIdsMapped[$supplierId][$key]);
                    }
                } else {
                    continue;
                }

                // Select a random sales order ID if available
                $salesOrderId = null;
                if (! empty($salesOrderIds)) {
                    $salesOrderId = $this->faker->randomElement($salesOrderIds);
                    // Remove the used sales order ID from the available IDs
                    $key = array_search($salesOrderId, $salesOrderIds, true);
                    if ($key !== false) {
                        unset($salesOrderIds[$key]);
                    }
                } else {
                    continue;
                }

                $this->purchaseOrderIdsToUpdate[] = $purchaseOrderId;
                $this->salesOrderIdsToUpdate[] = $salesOrderId;

                $this->vehicleablesInserts[] = [
                    'vehicle_id'       => $vehicleId,
                    'vehicleable_id'   => $purchaseOrderId,
                    'vehicleable_type' => PurchaseOrder::class,
                ];

                $this->vehicleablesInserts[] = [
                    'vehicle_id'       => $vehicleId,
                    'vehicleable_id'   => $salesOrderId,
                    'vehicleable_type' => SalesOrder::class,
                ];

                $this->workflowInserts[] = [
                    'creator_id'          => $this->faker->randomElement($usersCanCreateWorkflow),
                    'vehicleable_type'    => Vehicle::class,
                    'vehicleable_id'      => $vehicleId,
                    'workflow_process_id' => $this->faker->randomElement($workflowProcessIds),
                    'created_at'          => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                    'updated_at'          => now(),
                ];
            }
        }
    }

    private function createServiceVehicleWorkflows($companyId, $usersCanCreateWorkflow): void
    {
        $serviceVehicleIds = ServiceVehicle::whereHas('creator', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->limit(10)->pluck('id')->all();

        $serviceOrderIds = ServiceOrder::whereHas('creator', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->limit(10)->pluck('id')->all();

        $transportOrderIds = TransportOrder::whereHas('creator', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->where('vehicle_type', TransportableType::Service_vehicles->value)
            ?->where('transport_type', '!=', TransportType::Other->value)
            ->limit(10)->pluck('id')->all();

        $workflowProcessIds = WorkflowProcess::where('company_id', $companyId)->get(['id'])->pluck('id')->all();

        foreach ($serviceVehicleIds as $serviceVehicleId) {
            // Select a random service order ID if available
            $serviceOrderId = null;
            if (! empty($serviceOrderIds)) {
                $serviceOrderId = $this->faker->randomElement($serviceOrderIds);
                // Remove the used service order ID from the available IDs
                $key = array_search($serviceOrderId, $serviceOrderIds, true);
                if ($key !== false) {
                    unset($serviceOrderIds[$key]);
                }
            } else {
                continue;
            }

            $transportOrderId = null;
            if (! empty($transportOrderIds)) {
                $transportOrderId = $this->faker->randomElement($transportOrderIds);
                // Remove the used transport order ID from the available IDs
                $key = array_search($transportOrderId, $transportOrderIds, true);
                if ($key !== false) {
                    unset($transportOrderIds[$key]);
                }
            } else {
                continue;
            }

            $this->serviceOrderUpserts[] = [
                'id'                 => $serviceOrderId,
                'status'             => ServiceOrderStatus::Completed->value,
                'service_vehicle_id' => $serviceVehicleId,
            ];

            $this->transportOrderIdsToUpdate[] = $transportOrderId;

            $this->transportablesInserts[] = [
                'transport_order_id' => $transportOrderId,
                'transportable_type' => ServiceVehicle::class,
                'transportable_id'   => $serviceVehicleId,
            ];

            $this->workflowInserts[] = [
                'creator_id'          => $this->faker->randomElement($usersCanCreateWorkflow),
                'vehicleable_type'    => ServiceVehicle::class,
                'vehicleable_id'      => $serviceVehicleId,
                'workflow_process_id' => $this->faker->randomElement($workflowProcessIds),
                'created_at'          => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                'updated_at'          => now(),
            ];
        }
    }

    private function completePurchaseOrders(): void
    {
        PurchaseOrder::whereIn('id', $this->purchaseOrderIdsToUpdate)->update([
            'status' => PurchaseOrderStatus::Completed->value,
        ]);

        foreach (PurchaseOrder::whereIn('id', $this->purchaseOrderIdsToUpdate)->with([
            'supplier:id,full_name,company_id',
            'supplier.company' => function ($query) {
                $query->select('id', 'name')->withTrashed();
            },
            'intermediary:id,full_name,company_id',
            'intermediary.company' => function ($query) {
                $query->select('id', 'name')->withTrashed();
            },
            'purchaser:id,full_name',
            'creator:id,company_id',
            'creator.company' => function ($query) {
                $query->select('id', 'name', 'default_currency')->withTrashed();
            },
            'vehicles:id,creator_id,make_id,vehicle_model_id,engine_id',
            'vehicles.creator', 'vehicles.make:id,name', 'vehicles.vehicleModel:id,name',
            'vehicles.engine:id,name', 'vehicles.calculation:vehicleable_type,vehicleable_id,total_purchase_price,sales_price_total,original_currency',
        ])->get() as $purchaseOrder) {
            $this->insertStatuses($purchaseOrder, PurchaseOrderStatus::values());

            $pdfService = new PdfService('templates/purchase-order', [
                'purchaseOrder'   => $purchaseOrder,
                'defaultCurrency' => $purchaseOrder->creator->company->default_currency->value,
                'logo'            => $this->logo,
            ]);

            $uploadedPdf = UploadHelper::uploadGeneratedFile($pdfService->generate(), "purchase-order-{$purchaseOrder->id}", 'pdf');
            $purchaseOrder->saveWithFile($uploadedPdf, 'generatedPdf');
        }
    }

    private function completeSalesOrders(): void
    {
        $salesOrders = SalesOrder::whereIn('id', $this->salesOrderIdsToUpdate)
            ->with(['orderItems', 'orderServices:orderable_type,orderable_id,purchase_price,sale_price'])
            ->get();

        foreach ($salesOrders as $salesOrder) {
            $totalCosts = 0;
            $totalSalesPriceServiceItems = 0;

            foreach ($salesOrder->orderItems as $orderItem) {
                $totalCosts += CurrencyHelper::convertCurrencyToUnits($orderItem->item?->purchase_price);
                $totalSalesPriceServiceItems += CurrencyHelper::convertCurrencyToUnits($orderItem->sale_price);
            }

            foreach ($salesOrder->orderServices as $orderService) {
                $totalCosts += CurrencyHelper::convertCurrencyToUnits($orderService->purchase_price);
                $totalSalesPriceServiceItems += CurrencyHelper::convertCurrencyToUnits($orderService->sale_price);
            }

            $salesOrder->total_costs = CurrencyHelper::convertUnitsToCurrency($totalCosts);
            $salesOrder->total_sales_price_service_items = CurrencyHelper::convertUnitsToCurrency($totalSalesPriceServiceItems);
            $salesOrder->status = SalesOrderStatus::Completed->value;
            $salesOrder->total_sales_price_exclude_vat = CurrencyHelper::convertUnitsToCurrency(210600);
            $salesOrder->total_sales_price_include_vat = CurrencyHelper::convertUnitsToCurrency(210600);
            $salesOrder->total_sales_margin = CurrencyHelper::convertUnitsToCurrency(200000);
            $salesOrder->total_sales_price = CurrencyHelper::convertUnitsToCurrency(210600 + $totalSalesPriceServiceItems);

            $salesOrder->save();
        }

        (new SalesOrderSeeder())->generatePdf($this->salesOrderIdsToUpdate, SalesOrderStatus::Completed->value);
    }

    private function completeTransportOrders(): void
    {
        foreach (TransportOrder::whereIn('id', $this->transportOrderIdsToUpdate)->get() as $transportOrder) {
            $transportOrder->status = TransportOrderStatus::Cmr_waybill->value;
            $transportOrder->save();

            $this->insertStatuses($transportOrder, TransportOrderStatus::values());
        }

        foreach (TransportOrder::whereIn('id', $this->transportOrderIdsToUpdate)->where('transport_company_use', true)
            ->with([
                'transporter:id,full_name,company_id',
                'transportCompany' => function ($query) {
                    $query->select('id', 'name')->withTrashed();
                },
            ])->get() as $transportOrder) {
            $selectedTransportables = $transportOrder->serviceVehicles()
                ->select('id', 'creator_id', 'make_id', 'vehicle_model_id', 'vin')
                ->with(['creator', 'make:id,name', 'vehicleModel:id,name'])
                ->get();

            $companyAddresses = CompanyAddress::where('type', CompanyAddressType::Logistics->value)
                ->where('company_id', $transportOrder->transportCompany?->id)
                ->pluck('id', 'address')->toArray();

            $pdfService = new PdfService(
                'templates/transport-orders/transport-order',
                [
                    'transportOrder'         => $transportOrder,
                    'selectedTransportables' => $selectedTransportables,
                    'companyAddresses'       => $companyAddresses,
                    'logo'                   => $this->logo,
                ]
            );

            $generatedPdf = UploadHelper::uploadGeneratedFile($pdfService->generate(), "transport-order-{$transportOrder->id}", 'pdf');
            $transportOrder->saveWithFile($generatedPdf, 'generatedPdf');
        }
    }

    private function completeServiceOrders(): void
    {
        foreach ($this->serviceOrderUpserts as $update) {
            $serviceOrder = ServiceOrder::find($update['id']);
            $this->insertStatuses($serviceOrder, ServiceOrderStatus::values());

            if ($serviceOrder) {
                $serviceOrder->update([
                    'status'             => $update['status'],
                    'service_vehicle_id' => $update['service_vehicle_id'],
                ]);
            }
        }
    }

    private function mapBySupplier($query): array
    {
        $mappedArray = [];

        $data = $query
            ->select('supplier_id', 'id')
            ->get();

        foreach ($data as $row) {
            $keyId = $row->supplier_id;
            $valueId = $row->id;

            if (! isset($mappedArray[$keyId])) {
                $mappedArray[$keyId] = [];
            }

            $mappedArray[$keyId][] = $valueId;
        }

        return $mappedArray;
    }

    private function insertStatuses(Model $model, $statuses): void
    {
        $statusInserts = [];
        foreach ($statuses as $status) {
            if (1 == $status) {
                continue;
            }

            $statusInserts[] = new Statusable(['status' => $status]);
        }

        $model->statuses()->saveMany($statusInserts);
    }
}
