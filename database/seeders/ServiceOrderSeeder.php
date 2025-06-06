<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Models\Make;
use App\Models\ServiceOrder;
use App\Support\CurrencyHelper;
use Illuminate\Support\Facades\DB;

class ServiceOrderSeeder extends BaseSeeder
{
    /**
     * Array of vehicle model ids mapped by make.
     *
     * @var array
     */
    private array $serviceOrderInserts;

    private array $orderItemsInserts;

    private array $orderServicesInserts;

    private int $serviceOrderId = 1;

    /**
     * Create a new ServiceOrderSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $companyAdministratorId) {
            $this->getInserts($companyId, $companyAdministratorId);
        }

        DB::table('service_orders')->insert($this->serviceOrderInserts);
        DB::table('order_items')->insert($this->orderItemsInserts ?? []);
        DB::table('order_services')->insert($this->orderServicesInserts ?? []);
    }

    private function getInserts(int $companyId, int $creatorId): void
    {
        $crmData = CrmDataSeeder::getForOrder($companyId);

        $customerIds = $crmData['customerIds'];
        $mappedByCompanyServiceLevels = $crmData['mappedByCompanyServiceLevels'];

        for ($i = 0; $i < $this->faker->numberBetween(30, 50); $i++) {
            $customerCompanyId = $this->faker->randomElement($customerIds->keys());

            $serviceLevel = isset($mappedByCompanyServiceLevels[$customerCompanyId]) && $this->faker->boolean() ?
                $this->faker->randomElement($mappedByCompanyServiceLevels[$customerCompanyId]) :
                $this->faker->randomElement($crmData['systemServiceLevels']);

            $this->serviceOrderInserts[] = [
                'id'                  => $this->serviceOrderId,
                'creator_id'          => $creatorId,
                'customer_company_id' => $customerCompanyId,
                'customer_id'         => $customerIds[$customerCompanyId],
                'seller_id'           => $this->faker->randomElement($crmData['sellerIds']),
                'service_level_id'    => $serviceLevel->id ?? null,
                'type_of_service'     => $this->faker->randomElement(ImportOrOriginType::values()),
                'payment_condition'   => $this->faker->randomElement(PaymentCondition::values()),
                'status'              => 1,
                'created_at'          => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            ];

            if ($serviceLevel) {
                foreach ($serviceLevel->items as $item) {
                    $this->orderItemsInserts[] = [
                        'id'             => $item->id,
                        'orderable_type' => ServiceOrder::class,
                        'orderable_id'   => $this->serviceOrderId,
                        'sale_price'     => CurrencyHelper::convertCurrencyToUnits($item->sale_price),
                        'in_output'      => $item->pivot->in_output,
                        'created_at'     => $item->created_at,
                    ];
                }

                foreach ($serviceLevel->additionalServices as $service) {
                    $this->orderServicesInserts[] = [
                        'orderable_type'   => ServiceOrder::class,
                        'orderable_id'     => $this->serviceOrderId,
                        'name'             => $service->name,
                        'purchase_price'   => CurrencyHelper::convertCurrencyToUnits($service->purchase_price),
                        'sale_price'       => CurrencyHelper::convertCurrencyToUnits($service->sale_price),
                        'in_output'        => $service->in_output,
                        'is_service_level' => true,
                        'created_at'       => $service->created_at,
                    ];
                }
            }

            $this->serviceOrderId++;
        }
    }
}
