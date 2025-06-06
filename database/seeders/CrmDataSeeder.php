<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyAddressType;
use App\Enums\CompanyType;
use App\Enums\Country;
use App\Enums\Currency;
use App\Enums\NationalEuOrWorldType;
use App\Enums\ServiceLevelType;
use App\Models\Company;
use App\Models\CompanyGroup;

use App\Models\ServiceLevel;
use App\Models\User;
use App\Services\RoleService;
use App\Support\StringHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CrmDataSeeder extends BaseSeeder
{
    /**
     * Collection of the users that can create crm companies.
     *
     * @var Collection
     */
    private Collection $userCanCreateCrmCompanies;

    /**
     * Collection of the crm roles.
     *
     * @var Collection
     */
    private Collection $crmRoles;

    /**
     * Array holding company groups mapped by creator.
     *
     * @var array
     */
    private array $companyGroupIdsMapped;

    /**
     * Create a new CrmDataSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userCanCreateCrmCompanies = User::role(RoleService::getMainCompanyRoles()->pluck('name'))->get();
        $this->companyGroupIdsMapped = CompanyGroup::mapByColumn('creator_id');
        $this->crmRoles = RoleService::getCrmRoles()->get();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spatieTableNames = config('permission.table_names');

        $companyId = 3;

        $companiesInsertData = [];
        $companiesAddressesInsertData = [];

        foreach ($this->userCanCreateCrmCompanies as $user) {
            $isKvk = mt_rand(0, 1);
            $name = "{$this->faker->company} {$this->faker->companySuffix}";

            $companiesInsertData[] = [
                'id'                                => $companyId,
                'creator_id'                        => $user->id,
                'company_group_id'                  => isset($this->companyGroupIdsMapped[$user->id]) ? $this->faker->randomElement($this->companyGroupIdsMapped[$user->id]) : null,
                'main_user_id'                      => null,
                'billing_contact_id'                => null,
                'logistics_contact_id'              => null,
                'type'                              => $this->faker->randomElement(CompanyType::valuesWithout(CompanyType::Base)),
                'default_currency'                  => $this->faker->randomElement(Currency::values()),
                'country'                           => $this->faker->randomElement(Country::values()),
                'name'                              => $name,
                'number'                            => $this->faker->bothify('??-####-??'),
                'number_addition'                   => $this->faker->bothify('??-####-??'),
                'postal_code'                       => $this->faker->postcode,
                'purchase_type'                     => $this->faker->randomElement(NationalEuOrWorldType::values()),
                'city'                              => $this->faker->city,
                'address'                           => $this->faker->address,
                'province'                          => $this->faker->city,
                'vat_number'                        => $this->faker->unique()->numerify('#####'),
                'company_number_accounting_system'  => $this->faker->bothify('#####-????'),
                'debtor_number_accounting_system'   => $this->faker->bothify('#####??'),
                'creditor_number_accounting_system' => $this->faker->bothify('??#####'),
                'website'                           => $this->faker->url,
                'email'                             => StringHelper::generateEmailFromName($name),
                'phone'                             => $this->faker->phoneNumber,
                'iban'                              => $this->faker->iban(),
                'swift_or_bic'                      => $this->faker->swiftBicNumber(),
                'bank_name'                         => $this->faker->domainName(),
                'kvk_number'                        => $isKvk ? $this->faker->unique()->numberBetween(10000000, 99999999) : null,
                'logistics_times'                   => $this->faker->realText,
                'billing_remarks'                   => $this->faker->realText,
                'logistics_remarks'                 => $this->faker->realText,
            ];

            for ($j = 1; $j <= $this->faker->numberBetween(3, 7); $j++) {
                $companiesAddressesInsertData[] = [
                    'company_id' => $companyId,
                    'type'       => $this->faker->boolean(60) ? $this->faker->numberBetween(1, 4) : CompanyAddressType::Logistics->value,
                    'address'    => $this->faker->address,
                ];
            }

            $companyId++;
        }

        $userRoleInserts = [];
        $crmCompanyUsersData = [];
        $userId = User::count() + 1;
        $companyUpserts = [];
        $userPivotData = [];

        $userSeeder = new UserSeeder();
        $userSeeder->loadUsedEmails();

        $password = Hash::make(config('app.user_password'));
        foreach ($companiesInsertData as $crmCompany) {
            $randUserIds = [];
            foreach ($this->crmRoles as $crmRole) {
                if ('Customer' == $crmRole->name && isset($this->companyGroupIdsMapped[$crmCompany['creator_id']])) {
                    $userPivotData[] = [
                        'user_group_id' => $this->faker->randomElement($this->companyGroupIdsMapped[$crmCompany['creator_id']]),
                        'user_id'       => $userId,
                        'created_at'    => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                    ];
                }

                $gender = $this->faker->numberBetween(1, 2);
                $personalData = $userSeeder->generatePersonalData($gender);

                $crmCompanyUsersData[] = [
                    'creator_id' => $crmCompany['creator_id'],
                    'company_id' => $crmCompany['id'],
                    'first_name' => $personalData['firstName'],
                    'last_name'  => $personalData['lastName'],
                    'full_name'  => "{$personalData['firstName']} {$personalData['lastName']}",
                    'email'      => $personalData['email'],
                    'password'   => $password,
                    'mobile'     => $this->faker->phoneNumber(),
                    'language'   => 1,
                    'gender'     => $gender,
                    'created_at' => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                ];

                $userRoleInserts[] = [
                    'model_type' => User::class,
                    'model_id'   => $userId,
                    'role_id'    => $crmRole->id,
                ];

                $randUserIds[] = $userId;
                $userId++;
            }

            $companyUpserts[] = [
                ...$crmCompany,
                'main_user_id'         => $this->faker->randomElement($randUserIds),
                'billing_contact_id'   => $this->faker->randomElement($randUserIds),
                'logistics_contact_id' => $this->faker->randomElement($randUserIds),
            ];
        }

        DB::table('companies')->insert($companiesInsertData);
        DB::table('company_addresses')->insert($companiesAddressesInsertData);
        DB::table('users')->insert($crmCompanyUsersData);
        DB::table($spatieTableNames['model_has_roles'])->insertOrIgnore($userRoleInserts);
        DB::table('user_user_group')->insertOrIgnore($userPivotData);

        Company::upsert(
            $companyUpserts,
            ['id'],
            ['main_user_id', 'billing_contact_id', 'logistics_contact_id']
        );
    }

    /**
     * Map resource by company.
     *
     * @param        $data
     * @return array
     */
    private static function mapByCompany($data): array
    {
        $mappedArray = [];

        foreach ($data as $row) {
            foreach ($row->companies as $company) {
                $keyId = $company->id;
                if (! isset($mappedArray[$keyId])) {
                    $mappedArray[$keyId] = [];
                }

                $mappedArray[$keyId][] = $row;
            }
        }

        return $mappedArray;
    }

    // TODO: #184 getForOrder() should be refactored

    /**
     * Duplicate Sales Order and Service Order crm data.
     *
     * @param  int   $companyId
     * @return array
     */
    public static function getForOrder(int $companyId): array
    {

        $systemServiceLevels = ServiceLevel::whereHas('creator', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })?->where('type', ServiceLevelType::System)->with(['items', 'additionalServices'])->get();

        $mappedByCompanyServiceLevels = self::mapByCompany(ServiceLevel::whereHas('creator', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })?->where('type', ServiceLevelType::Client)->with(['items', 'additionalServices', 'companies:id'])->get());

        $sellerIds = User::where('company_id', $companyId)->role(RoleService::getMainCompanyRoles()->pluck('id'))->pluck('id');

        $crmCompanyIds = Company::crmCompanies(CompanyType::General->value)
            ->whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->get(['id'])->pluck('id')->all();
        $customerIds = User::whereIn('company_id', $crmCompanyIds)->role('Customer')->pluck('id', 'company_id');

        return compact(
            'crmCompanyIds',
            'customerIds',
            'sellerIds',
            'systemServiceLevels',
            'mappedByCompanyServiceLevels'
        );

    }
}
