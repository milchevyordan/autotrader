<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Enums\ServiceLevelType;
use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use App\Support\StringHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ServiceLevelSeeder extends BaseSeeder
{
    /**
     * Array of users that have the permission to create service levels.
     *
     * @var array
     */
    private array $userCanCreateServiceLevelIds;

    /**
     * Array of item ids mapped by creator.
     *
     * @var array
     */
    private array $itemMappedByCreator;

    /**
     * Array of company ids mapped by creator.
     *
     * @var array
     */
    private array $companiesMappedByCreator;

    private array $serviceLevelInserts = [];

    private array $itemServiceLevelInserts = [];

    private int $serviceLevelId = 1;

    /**
     * Items needed in default service level.
     *
     * @var Collection
     */
    private Collection $items;

    /**
     * Create a new ServiceLevelSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userCanCreateServiceLevelIds = $this->mapByColumn(User::whereHas('roles.permissions', function ($query) {
            $query->where('name', 'create-service-level');
        })->select('company_id', 'id')->get());

        $this->itemMappedByCreator = Item::mapByColumn('creator_id');

        $this->items = Item::whereIn('shortcode', [
            'Reconditioning, interior cleaning, showroomready',
            'Delivery to location',
            'Luxury licenceplates',
        ])->select('id', 'shortcode', 'creator_id')->get();

        $this->companiesMappedByCreator = Company::mapByColumn('creator_id');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyServiceLevelInserts = [];
        $additionalServicesInserts = [];

        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $companyAdministratorId) {
            $this->serviceLevelInserts[] = $this->prepareServiceLevelInsert($companyAdministratorId);
            $this->prepareItemServiceLevelInserts($this->items->where('creator_id', $companyAdministratorId));

            for ($i = 0; $i < $this->faker->numberBetween(10, 15); $i++) {
                $serviceLevelType = $this->faker->randomElement(ServiceLevelType::values());
                $serviceLevelClient = 2 == $serviceLevelType;

                $this->serviceLevelInserts[] = [
                    'id'                    => $this->serviceLevelId,
                    'creator_id'            => $companyAdministratorId,
                    'type'                  => $serviceLevelType,
                    'name'                  => $this->faker->name(),
                    'transport_included'    => $this->faker->boolean(),
                    'type_of_sale'          => $this->faker->randomElement(ImportOrOriginType::values()),
                    'damage'                => $this->faker->randomElement(Damage::values()),
                    'payment_condition'     => $this->faker->randomElement(PaymentCondition::values()),
                    'discount_in_output'    => $this->faker->boolean,
                    'discount'              => $this->faker->boolean ? $this->faker->numberBetween(-1000, -1) : null,
                    'rdw_company_number'    => $serviceLevelClient ? $this->faker->regexify('[A-Z]{2}\d{6}[A-Z0-9]{9}') : null,
                    'login_autotelex'       => $serviceLevelClient ? StringHelper::generateEmailFromName($this->faker->firstName().$this->faker->lastName()) : null,
                    'api_japie'             => $serviceLevelClient ? $this->faker->lexify('??????-??????-??????-??????') : null,
                    'bidder_name_autobid'   => $serviceLevelClient ? $this->faker->firstName().$this->faker->lastName() : null,
                    'bidder_number_autobid' => $serviceLevelClient ? $this->faker->phoneNumber() : null,
                    'api_vwe'               => $serviceLevelClient ? $this->faker->lexify('??????-??????-??????-??????') : null,
                    'created_at'            => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                    'updated_at'            => date('Y-m-d H:i:s'),
                ];

                for ($j = 0; $j < $this->faker->numberBetween(0, 5); $j++) {
                    $this->itemServiceLevelInserts[] = [
                        'item_id'          => $this->faker->randomElement($this->itemMappedByCreator[$companyAdministratorId]),
                        'service_level_id' => $this->serviceLevelId,
                        'in_output'        => $this->faker->boolean,
                    ];
                }

                if ($serviceLevelType == ServiceLevelType::Client->value) {
                    for ($j = 0; $j < $this->faker->numberBetween(1, 3); $j++) {
                        $companyServiceLevelInserts[] = [
                            'service_level_id' => $this->serviceLevelId,
                            'company_id'       => $this->faker->randomElement($this->companiesMappedByCreator[$this->faker->randomElement($this->userCanCreateServiceLevelIds[$companyId])]),
                        ];
                    }
                }

                for ($j = 0; $j < $this->faker->numberBetween(0, 5); $j++) {
                    $additionalServicesInserts[] = [
                        'service_level_id' => $this->serviceLevelId,
                        'name'             => $this->faker->name,
                        'purchase_price'   => $this->faker->numberBetween(1, 50000),
                        'sale_price'       => $this->faker->numberBetween(1, 50000),
                        'in_output'        => $this->faker->boolean(),
                    ];
                }

                $this->serviceLevelId++;
            }
        }

        DB::table('service_levels')->insertOrIgnore($this->serviceLevelInserts);
        DB::table('item_service_level')->insertOrIgnore($this->itemServiceLevelInserts);
        DB::table('company_service_level')->insertOrIgnore($companyServiceLevelInserts);
        DB::table('service_level_services')->insertOrIgnore($additionalServicesInserts);
    }

    /**
     * Default service level template.
     *
     * @param  int   $companyAdministratorId
     * @return array
     */
    public function prepareServiceLevelInsert(int $companyAdministratorId): array
    {
        return [
            'id'                    => $this->serviceLevelId,
            'creator_id'            => $companyAdministratorId,
            'type'                  => ServiceLevelType::System->value,
            'name'                  => 'Standard NL trade',
            'transport_included'    => $this->faker->boolean(),
            'type_of_sale'          => $this->faker->randomElement(ImportOrOriginType::values()),
            'damage'                => $this->faker->randomElement(Damage::values()),
            'payment_condition'     => $this->faker->randomElement(PaymentCondition::values()),
            'discount_in_output'    => $this->faker->boolean,
            'discount'              => -75000,
            'rdw_company_number'    => null,
            'login_autotelex'       => null,
            'api_japie'             => null,
            'bidder_name_autobid'   => null,
            'bidder_number_autobid' => null,
            'api_vwe'               => null,
            'created_at'            => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Return default service level items inserts.
     *
     * @param       $items
     * @return void
     */
    public function prepareItemServiceLevelInserts($items): void
    {
        foreach ($items as $item) {
            $this->itemServiceLevelInserts[] = [
                'item_id'          => $item->id,
                'service_level_id' => $this->serviceLevelId,
                'in_output'        => false,
            ];
        }
    }

    /**
     * Return a array where key is company id of user and value is array of users that are in that company.
     *
     * @param        $users
     * @return array
     */
    private function mapByColumn($users): array
    {
        $mappedArray = [];

        foreach ($users as $row) {
            $keyId = $row->company_id;
            $valueId = $row->id;

            if (! isset($mappedArray[$keyId])) {
                $mappedArray[$keyId] = [];
            }

            $mappedArray[$keyId][] = $valueId;
        }

        return $mappedArray;
    }
}
