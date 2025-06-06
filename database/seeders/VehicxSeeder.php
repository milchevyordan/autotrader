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
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VehicxSeeder extends BaseSeeder
{
    /**
     * Collection of users with the role 'Root'.
     *
     * @var Collection
     */
    private Collection $userCanCreateCompanies;

    private array $companiesInsertData = [];

    private array $companySetting = [];

    private array $companyAdminUserRoles = [];

    private array $companiesAddressesInsertData = [];

    private int $userIdCounter;

    private int $adminRoleId;

    private int $rootUserId;

    private string $userPassword;

    private array $mainCompanyRoleIds;

    private array $userInserts;

    private array $userRoleInserts;

    /**
     * Create a new CompanySeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->userCanCreateCompanies = User::role('Root')->get();
        $this->userIdCounter = User::count() + 1;
        $this->adminRoleId = Role::findByName('Company Administrator')->id;
        $this->rootUserId = User::role('Root')->first()->id;
        $this->userPassword = Hash::make(config('app.user_password'));
        $this->mainCompanyRoleIds = Role::where('name', 'Super Manager')->pluck('id')->toArray();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $creatorId) {
            $this->generateCompanyVehicxData($companyId);
        }

        Company::insert($this->companiesInsertData);
        CompanyAddress::insert($this->companiesAddressesInsertData);
        Setting::insert($this->companySetting);
        User::insert($this->companyAdminUsers);
        DB::table('user_role')->insert($this->companyAdminUserRoles);

        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $companyAdministratorId) {
            foreach ($this->mainCompanyRoleIds as $roleId) {
                $this->generateUserVehicxData($roleId, $companyAdministratorId, $companyId);
            }
        }

        DB::table('users')->insert($this->userInserts);
        DB::table('user_role')->insert($this->userRoleInserts);
    }

    private function generateCompanyVehicxData(int $companyId): void
    {
        $name = 'Vehicx BV';
        $email = StringHelper::generateEmailFromName($name);

        $this->companiesInsertData[] = [
            'id'                => $companyId,
            'creator_id'        => $this->userCanCreateCompanies->random()->id,
            'type'              => CompanyType::Base->value,
            'name'              => $name,
            'email'             => $email,
            'country'           => Country::Netherlands->value,
            'city'              => 'VELDDRIEL',
            'address'           => 'Provincialeweg 100a',
            'postal_code'       => '5334 JK',
            'phone'             => '+31 6 20 36 68 19',
            'default_currency'  => Currency::Euro_EUR->value,
            'vat_percentage'    => 21,
            'kvk_number'        => '63535904',
            'iban'              => 'NL83 RABO 0305 0035 18',
            'logistics_times'   => 'Monday - Friday 08:90 - 17:00',
            'pdf_footer_text'   => 'Vehicx BV | +31 88 42 88 678 | info@vehicx.nl | www.vehicx.nl',
            'logistics_remarks' => 'Contact location 24h in advance Drive onto the compound, do not unload along the street',
            'swift_or_bic'      => 'RABONL2U',
            'bank_name'         => 'Rabobank',
            'vat_number'        => 'NL8552.79.412.B01',
        ];

        $this->companiesAddressesInsertData[] = [
            'company_id' => $companyId,
            'type'       => CompanyAddressType::Logistics->value,
            'address'    => 'Vehicx B.V.
                        Provincialeweg 100a
                        5334 JK VELDDRIEL
                        Netherlands',
        ];

        // Static at the moment will be changed later
        $this->companySetting[] = [
            'company_id'         => $companyId,
            'transport_outbound' => 8500,
            'recycling_fee'      => 2100,
            'sales_margin'       => 200000,
            'leges_vat'          => 9200,
        ];

        $firstName = 'Vehicx';
        $lastName = 'Administration';
        $this->companyAdminUsers[] = [
            'creator_id' => $this->rootUserId,
            'company_id' => $companyId,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'full_name'  => "{$firstName} {$lastName}",
            'language'   => Locale::en->value,
            'email'      => 'admin@vehicx.nl',
            'password'   => $this->userPassword,
            'mobile'     => '+31 6 20 36 68 19',
            'gender'     => Gender::Male->value,
        ];

        $this->companyAdminUserRoles[] = [
            'user_id' => $this->userIdCounter,
            'role_id' => $this->adminRoleId,
        ];

        $this->userIdCounter++;
    }

    private function generateUserVehicxData(int $roleId, int $companyAdministratorId, int $companyId): void
    {
        $this->userInserts[] = [
            'creator_id' => $companyAdministratorId,
            'id'         => $this->userIdCounter,
            'company_id' => $companyId,
            'first_name' => 'Marnix',
            'last_name'  => 'Benink',
            'full_name'  => 'Marnix Benink',
            'email'      => 'marnix@vehicx.nl',
            'language'   => Locale::en->value,
            'password'   => $this->userPassword,
            'mobile'     => '+31 88 42 88 678',
            'gender'     => Gender::Male->value,
            'created_at' => $this->safeDateTimeBetween(),
        ];

        // Set user role 1:1
        $this->userRoleInserts[] = [
            'user_id' => $this->userIdCounter,
            'role_id' => $roleId,
        ];

        $this->userIds[] = $this->userIdCounter;
        $this->userIdCounter++;
    }
}
