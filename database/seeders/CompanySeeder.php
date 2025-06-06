<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyAddressType;
use App\Enums\CompanyType;
use App\Enums\Country;
use App\Enums\Currency;
use App\Enums\Gender;
use App\Enums\Locale;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\Setting;
use App\Models\User;
use App\Support\StringHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CompanySeeder extends BaseSeeder
{
    /**
     * Collection of users with role 'Root'.
     *
     * @var Collection
     */
    private Collection $userCanCreateCompanies;

    /**
     * Seeded company admins (those who rent the system).
     *
     * @var string
     */
    private string $companyAdminEmail = 'companyAdmin'; // Real email is companyAdmin[$companyId]@test.com

    /**
     * @var array
     */
    private array $companiesInsertData = [];

    /**
     * @var array
     */
    private array $companySetting = [];

    /**
     * @var array
     */
    private array $companyAdminUserRoles = [];

    /**
     * @var array
     */
    private array $companyAdminUsers = [];

    /**
     * @var array
     */
    private array $companiesAddressesInsertData = [];

    /**
     * @var int
     */
    private int $userIdCounter;

    /**
     * @var int
     */
    private int $adminRoleId;

    /**
     * @var int
     */
    private int $rootUserId;

    /**
     * @var string
     */
    private string $userPassword;

    /**
     * Create a new CompanySeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->userCanCreateCompanies = User::role('Root')->get();
        $this->userIdCounter = User::count() + 1;
        $this->adminRoleId = Role::findByName('Company Administrator')?->id;
        $this->rootUserId = User::role('Root')->first()->id;
        $this->userPassword = Hash::make(config('app.user_password'));
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spatieTableNames = config('permission.table_names');

        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $creatorId) {
            $this->generateTestCompaniesData($companyId);

        }

        Company::insert($this->companiesInsertData);
        CompanyAddress::insert($this->companiesAddressesInsertData);
        Setting::insert($this->companySetting);
        User::insert($this->companyAdminUsers);
        DB::table($spatieTableNames['model_has_roles'])->insert($this->companyAdminUserRoles);
    }

    private function generateTestCompaniesData(int $companyId): void
    {
        $name = "{$this->faker->company} {$this->faker->companySuffix}";
        $email = StringHelper::generateEmailFromName($name);

        $this->companiesInsertData[] = [
            // At the moment we have only one root user, and it's supposed to be 1
            'id'                => $companyId,
            'creator_id'        => $this->userCanCreateCompanies->random()->id,
            'type'              => CompanyType::Base->value,
            'name'              => $name,
            'email'             => $email,
            'country'           => $this->faker->randomElement(Country::values()),
            'city'              => $this->faker->city,
            'address'           => $this->faker->address,
            'postal_code'       => $this->faker->postcode,
            'phone'             => $this->faker->phoneNumber,
            'default_currency'  => Currency::Euro_EUR->value,
            'vat_percentage'    => 21,
            'kvk_number'        => $this->faker->unique()->numberBetween(10000000, 99999999),
            'iban'              => $this->faker->iban(),
            'logistics_times'   => $this->faker->sentence(),
            'billing_remarks'   => $this->faker->sentence(),
            'logistics_remarks' => $this->faker->sentence(),
            'swift_or_bic'      => $this->faker->swiftBicNumber(),
            'bank_name'         => $this->faker->domainName(),
            'vat_number'        => $this->faker->unique()->numerify('#####'),
        ];

        for ($j = 1; $j <= $this->faker->numberBetween(3, 7); $j++) {
            $this->companiesAddressesInsertData[] = [
                'company_id' => $companyId,
                'type'       => $this->faker->boolean(60) ? $this->faker->numberBetween(1, 4) : CompanyAddressType::Logistics->value,
                'address'    => $this->faker->address,
            ];
        }

        for ($j = 1; $j <= $this->faker->numberBetween(3, 7); $j++) {
            $this->companiesAddressesInsertData[] = [
                'company_id' => $companyId,
                'type'       => $this->faker->boolean(60) ? $this->faker->numberBetween(1, 4) : CompanyAddressType::Logistics->value,
                'address'    => $this->faker->address,
            ];
        }

        // Static at the moment will be changed later
        $this->companySetting[] = [
            'company_id'         => $companyId,
            'transport_outbound' => 8500,
            'recycling_fee'      => 2100,
            'sales_margin'       => 200000,
            'leges_vat'          => 9200,
        ];

        $adminEmail = "{$this->companyAdminEmail}{$companyId}@test.com";
        $firstName = $this->faker->firstNameMale;
        $lastName = $this->faker->lastName;
        $this->companyAdminUsers[] = [
            'creator_id' => $this->rootUserId,
            'company_id' => $companyId,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'full_name'  => "{$firstName} {$lastName}",
            'language'   => Locale::en->value,
            'email'      => $adminEmail,
            'password'   => $this->userPassword,
            'mobile'     => $this->faker->phoneNumber(),
            'gender'     => Gender::Male->value,
        ];
        $this->companyAdminUserRoles[] = [
            'model_type' => User::class,
            'model_id'   => $this->userIdCounter,
            'role_id'    => $this->adminRoleId,
        ];

        $this->userIdCounter++;

    }
}
