<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Enums\ServiceLevelType;
use App\Models\Quote;

use App\Models\ServiceLevel;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\RoleService;
use App\Support\CurrencyHelper;
use Database\Seeders\Traits\WeekInputDates;
use Illuminate\Support\Facades\DB;

class QuoteSeeder extends BaseSeeder
{
    use WeekInputDates;

    /**
     * Array of users that have the permission to create quotes.
     *
     * @var array
     */
    private array $userCanCreateQuoteIds;

    /**
     * Array holding vehicle ids mapped by creator.
     *
     * @var array
     */
    private array $vehiclesMappedByCreator;

    /**
     * Create a new QuoteSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userCanCreateQuoteIds = User::whereHas('roles.permissions', function ($query) {
            $query->where('name', 'create-quote');
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
        $quoteId = 1;

        foreach ($this->userCanCreateQuoteIds as $userId => $companyId) {
            $serviceLevels = ServiceLevel::whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
                ?->where('type', ServiceLevelType::System)->with(['items', 'additionalServices'])->get();

            $sellerIds = User::where('company_id', $companyId)->role(RoleService::getMainCompanyRoles()->pluck('id'))->pluck('id');

            for ($i = 0; $i <= $this->faker->numberBetween(20, 30); $i++) {
                $serviceLevel = $this->faker->randomElement($serviceLevels);

                $vehiclesCount = 0;
                if (isset($this->vehiclesMappedByCreator[$userId])) {
                    $usedVehicleIds = [];
                    for ($j = 0; $j < $this->faker->numberBetween(3, 5); $j++) {
                        $vehicleId = $this->faker->randomElement($this->vehiclesMappedByCreator[$userId]);
                        if (in_array($vehicleId, $usedVehicleIds, true)) {
                            continue;
                        }

                        $usedVehicleIds[] = $vehicleId;

                        $vehicleablesInserts[] = [
                            'vehicle_id'       => $vehicleId,
                            'vehicleable_id'   => $quoteId,
                            'vehicleable_type' => Quote::class,
                        ];

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
                            'orderable_type' => Quote::class,
                            'orderable_id'   => $quoteId,
                            'sale_price'     => $itemSalePrice,
                            'in_output'      => $item->pivot->in_output,
                        ];

                        $itemsAndServicesPurchasePrice += CurrencyHelper::convertCurrencyToUnits($item->purchase_price);
                        $itemsAndServicesSalePrice += $itemSalePrice;
                    }

                    foreach ($serviceLevel->additionalServices as $service) {
                        $servicePurchasePrice = CurrencyHelper::convertCurrencyToUnits($service->purchase_price);
                        $serviceSalePrice = CurrencyHelper::convertCurrencyToUnits($service->sale_price);

                        $orderServicesInserts[] = [
                            'orderable_type'   => Quote::class,
                            'orderable_id'     => $quoteId,
                            'name'             => $service->name,
                            'purchase_price'   => $servicePurchasePrice,
                            'sale_price'       => $serviceSalePrice,
                            'in_output'        => $service->in_output,
                            'is_service_level' => true,
                        ];

                        $itemsAndServicesPurchasePrice += $servicePurchasePrice;
                        $itemsAndServicesSalePrice += $serviceSalePrice;
                    }
                }

                $isDownPaymentTrue = $this->faker->boolean();

                $isBrutto = $this->faker->boolean();
                $totalSalesPriceIncludeVat = $vehiclesCount * 210600;
                $totalSalesPriceExcludeVat = 210600 * $vehiclesCount;
                $itemsAndServicesPurchasePrice *= $vehiclesCount;
                $allItemsAndServicesSalesPrice = $itemsAndServicesSalePrice * $vehiclesCount;

                $preparedArrayData[] = [
                    'id'                              => $quoteId,
                    'creator_id'                      => $userId,
                    'seller_id'                       => $this->faker->randomElement($sellerIds),
                    'service_level_id'                => $serviceLevel->id ?? null,
                    'delivery_week'                   => $this->getWeekInputDates(),
                    'payment_condition'               => $this->faker->randomElement(PaymentCondition::values()),
                    'discount_in_output'              => $this->faker->boolean,
                    'discount'                        => $this->faker->boolean ? $this->faker->numberBetween(-1000, -1) : null,
                    'damage'                          => $this->faker->randomElement(Damage::values()),
                    'is_brutto'                       => $isBrutto,
                    'calculation_on_quote'            => $this->faker->boolean(),
                    'transport_included'              => $this->faker->boolean(),
                    'type_of_sale'                    => $this->faker->randomElement(ImportOrOriginType::values()),
                    'vat_deposit'                     => $this->faker->boolean(),
                    'vat_percentage'                  => 21,
                    'down_payment'                    => $isDownPaymentTrue,
                    'down_payment_amount'             => $isDownPaymentTrue ? $this->faker->numberBetween(500, 5000) : null,
                    'total_costs'                     => $itemsAndServicesPurchasePrice * $vehiclesCount,
                    'total_sales_price_service_items' => $allItemsAndServicesSalesPrice,
                    'total_sales_margin'              => 200000 * $vehiclesCount,
                    'total_sales_price_exclude_vat'   => $totalSalesPriceExcludeVat,
                    'total_sales_price_include_vat'   => $totalSalesPriceIncludeVat,
                    'total_quote_price_exclude_vat'   => $isBrutto ? ($totalSalesPriceIncludeVat + $allItemsAndServicesSalesPrice) : ($totalSalesPriceExcludeVat + $allItemsAndServicesSalesPrice),
                    'total_quote_price'               => $isBrutto ? ($totalSalesPriceIncludeVat + $allItemsAndServicesSalesPrice) : ($totalSalesPriceExcludeVat + $allItemsAndServicesSalesPrice),
                    'additional_info_conditions'      => $this->faker->sentence(),
                    'email_text'                      => $this->faker->sentence(),
                    'created_at'                      => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                ];

                $quoteId++;
            }
        }

        DB::table('quotes')->insert($preparedArrayData);
        DB::table('order_items')->insert($orderItemsInserts);
        DB::table('order_services')->insert($orderServicesInserts);
        DB::table('vehicleables')->insert($vehicleablesInserts);
    }
}
