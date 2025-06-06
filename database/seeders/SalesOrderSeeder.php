<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use App\Models\Statusable;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\SalesOrderService;
use App\Support\CurrencyHelper;
use Database\Seeders\Traits\WeekInputDates;
use Illuminate\Support\Facades\DB;

class SalesOrderSeeder extends BaseSeeder
{
    use WeekInputDates;

    /**
     * Array of users that have the permission to create sales orders.
     *
     * @var array
     */
    private array $userCanCreateSalesOrderIds;

    /**
     * Array of vehicles mapped by their creator.
     *
     * @var array
     */
    private array $vehiclesMappedByCreator;

    /**
     * Create a new SalesOrderSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userCanCreateSalesOrderIds = User::whereHas('roles.permissions', function ($query) {
            $query->where('name', 'create-sales-order');
        })->pluck('company_id', 'id')->all();

        $this->vehiclesMappedByCreator = Vehicle::mapByColumn('creator_id');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $preparedArrayData = [];
        $orderItemsInserts = [];
        $orderServicesInserts = [];
        $vehicleablesInserts = [];

        $salesOrderId = 1;
        $readyForDownPaymentInvoiceIds = [];
        foreach ($this->userCanCreateSalesOrderIds as $userId => $companyId) {

            $crmData = CrmDataSeeder::getForOrder($companyId);

            $customerIds = $crmData['customerIds'];
            $mappedByCompanyServiceLevels = $crmData['mappedByCompanyServiceLevels'];

            for ($i = 0; $i < $this->faker->numberBetween(3, 7); $i++) {
                $customerCompanyId = $this->faker->randomElement($customerIds->keys());

                $serviceLevel = isset($mappedByCompanyServiceLevels[$customerCompanyId]) && $this->faker->boolean() ?
                    $this->faker->randomElement($mappedByCompanyServiceLevels[$customerCompanyId]) :
                    $this->faker->randomElement($crmData['systemServiceLevels']);

                $isVatDepositTrue = $this->faker->boolean();

                $isDownPaymentTrue = $this->faker->boolean();

                $status = SalesOrderStatus::Concept->value;
                $vehiclesCount = 0;
                if ($isDownPaymentTrue && isset($this->vehiclesMappedByCreator[$userId])) {
                    foreach ($this->vehiclesMappedByCreator[$userId] as $key => $vehicleId) {
                        if ($vehiclesCount >= 2) {
                            break;
                        }

                        // remove vehicleId from array
                        unset($this->vehiclesMappedByCreator[$userId][$key]);

                        $vehicleablesInserts[] = [
                            'vehicle_id'       => $vehicleId,
                            'vehicleable_id'   => $salesOrderId,
                            'vehicleable_type' => SalesOrder::class,
                        ];
                        $status = SalesOrderStatus::Ready_for_down_payment_invoice->value;
                        $readyForDownPaymentInvoiceIds[] = $salesOrderId;

                        $vehiclesCount++;
                    }
                }

                $itemsAndServicesPurchasePrice = 0;
                $itemsAndServicesSalePrice = 0;

                if ($serviceLevel) {
                    foreach ($serviceLevel->items as $item) {
                        $itemSalePrice = CurrencyHelper::convertCurrencyToUnits($item->sale_price);

                        $orderItemsInserts[] = [
                            'id'             => $item->id,
                            'orderable_type' => SalesOrder::class,
                            'orderable_id'   => $salesOrderId,
                            'sale_price'     => $itemSalePrice,
                            'in_output'      => $item->pivot->in_output,
                            'created_at'     => $item->created_at,
                        ];

                        $itemsAndServicesPurchasePrice += CurrencyHelper::convertCurrencyToUnits($item->purchase_price);
                        $itemsAndServicesSalePrice += $itemSalePrice;
                    }

                    foreach ($serviceLevel->additionalServices as $service) {
                        $servicePurchasePrice = CurrencyHelper::convertCurrencyToUnits($service->purchase_price);
                        $serviceSalePrice = CurrencyHelper::convertCurrencyToUnits($service->sale_price);

                        $orderServicesInserts[] = [
                            'orderable_type'   => SalesOrder::class,
                            'orderable_id'     => $salesOrderId,
                            'name'             => $service->name,
                            'purchase_price'   => $servicePurchasePrice,
                            'sale_price'       => $serviceSalePrice,
                            'in_output'        => $service->in_output,
                            'is_service_level' => true,
                            'created_at'       => $service->created_at,
                        ];

                        $itemsAndServicesPurchasePrice += $servicePurchasePrice;
                        $itemsAndServicesSalePrice += $serviceSalePrice;
                    }
                }

                $isBrutto = $this->faker->boolean();
                $totalSalesPriceIncludeVat = $vehiclesCount * 210600;
                $allItemsAndServicesSalesPrice = $itemsAndServicesSalePrice * $vehiclesCount;
                $totalSalesPriceExcludeVat = 210600 * $vehiclesCount;

                $preparedArrayData[] = [
                    'id'                              => $salesOrderId,
                    'creator_id'                      => $userId,
                    'customer_company_id'             => $customerCompanyId,
                    'customer_id'                     => $customerIds[$customerCompanyId],
                    'seller_id'                       => $this->faker->randomElement($crmData['sellerIds']),
                    'service_level_id'                => $serviceLevel->id ?? null,
                    'status'                          => $status,
                    'type_of_sale'                    => $this->faker->randomElement(ImportOrOriginType::values()),
                    'transport_included'              => $this->faker->boolean(),
                    'vat_deposit'                     => $isVatDepositTrue,
                    'vat_percentage'                  => $isVatDepositTrue ? 21 : null,
                    'down_payment'                    => $isDownPaymentTrue,
                    'down_payment_amount'             => $isDownPaymentTrue ? $this->faker->numberBetween(500, 5000) : null,
                    'currency_rate'                   => 1,
                    'is_brutto'                       => $isBrutto,
                    'calculation_on_sales_order'      => $this->faker->boolean(),
                    'total_costs'                     => $itemsAndServicesPurchasePrice * $vehiclesCount,
                    'total_sales_price_service_items' => $allItemsAndServicesSalesPrice,
                    'reference'                       => $this->faker->sentence(),
                    'currency'                        => $this->faker->numberBetween(1, 6),
                    'total_sales_price_include_vat'   => $totalSalesPriceIncludeVat,
                    'total_sales_margin'              => 200000 * $vehiclesCount,
                    'total_sales_price_exclude_vat'   => $totalSalesPriceExcludeVat,
                    'total_sales_price'               => $isBrutto ? ($totalSalesPriceIncludeVat + $allItemsAndServicesSalesPrice) : ($totalSalesPriceExcludeVat + $allItemsAndServicesSalesPrice),
                    'delivery_week'                   => $this->getWeekInputDates(),
                    'payment_condition'               => $this->faker->randomElement(PaymentCondition::values()),
                    'discount_in_output'              => $this->faker->boolean,
                    'discount'                        => $this->faker->boolean ? $this->faker->numberBetween(-1000, -1) : null,
                    'damage'                          => $this->faker->randomElement(Damage::values()),
                    'number'                          => 'SO'.$salesOrderId,
                    'created_at'                      => $this->safeDateTimeBetween('-1 years'),
                    'updated_at'                      => date('Y-m-d H:i:s'),
                ];

                $salesOrderId++;
            }
        }

        DB::table('sales_orders')->insert($preparedArrayData);
        DB::table('order_items')->insert($orderItemsInserts);
        DB::table('order_services')->insert($orderServicesInserts);
        DB::table('vehicleables')->insert($vehicleablesInserts);

        $this->generatePdf($readyForDownPaymentInvoiceIds, SalesOrderStatus::Ready_for_down_payment_invoice->value);
    }

    /**
     * On seed generate sales order pdf and save it to system.
     *
     * @param  array $ids
     * @param  int   $highestStatus
     * @return void
     */
    public static function generatePdf(array $ids, int $highestStatus): void
    {
        $statuses = SalesOrderStatus::values();

        foreach (SalesOrder::whereIn('id', $ids)->get() as $salesOrder) {
            (new SalesOrderService())->setSalesOrder($salesOrder)->generatePdf();

            $statusInserts = [];
            foreach ($statuses as $status) {
                if (1 == $status || $highestStatus < $status) {
                    continue;
                }

                $statusInserts[] = new Statusable(['status' => $status]);
            }

            $salesOrder->statuses()->saveMany($statusInserts);
        }
    }
}
