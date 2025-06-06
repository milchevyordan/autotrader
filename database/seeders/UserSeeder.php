<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Country;
use App\Enums\Gender;
use App\Models\Company;

use App\Models\User;
use App\Services\RoleService;
use App\Support\StringHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends BaseSeeder
{
    /**
     * Used Emails
     *
     * @var array<string>
     */
    private array $usedEmails = [];

    /**
     * @var int
     */
    private int $numberOfUsersPerRole = 2;

    /**
     * @var array<int>
     */
    private array $mainCompanyRoleIds;

    /**
     * @var array
     */
    private array $userInserts;

    /**
     * @var array
     */
    private array $userRoleInserts;

    /**
     * @var int
     */
    private int $userIdCounter;

    /**
     * @var string
     */
    private string $userPassword;

    /**
     * @var array
     */
    private array $companyUpserts = [];

    /**
     * @var array<int>
     */
    private array $userIds = [];

    /**
     * Create a new UserSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mainCompanyRoleIds = RoleService::getMainCompanyRoles()->pluck('id')->toArray();
        $this->userIdCounter = User::count() + 1;
        $this->userPassword = Hash::make(config('app.user_password'));
    }

    /**
     * Auto-generated seed file.
     *
     * @return void
     */
    public function run(): void
    {
        $spatieTableNames = config('permission.table_names');

        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $companyAdministratorId) {
            $this->userIds = [];
            foreach ($this->mainCompanyRoleIds as $roleId) {
                $this->generateTestData($roleId, $companyAdministratorId, $companyId);
            }

            $this->companyUpserts[] = ['id' => $companyId,
                'billing_contact_id'        => $this->faker->randomElement($this->userIds),
                'logistics_contact_id'      => $this->faker->randomElement($this->userIds)];
        }

        DB::table('users')->insert($this->userInserts);
        // Attach role to users
        DB::table($spatieTableNames['model_has_roles'])->insert($this->userRoleInserts);

        foreach ($this->companyUpserts as $upsert) {
            Company::where('id', $upsert['id'])->update([
                'billing_contact_id'   => $upsert['billing_contact_id'],
                'logistics_contact_id' => $upsert['logistics_contact_id'], ]);
        }
    }

    public function generateTestData(int $roleId, int $companyAdministratorId, int $companyId): void
    {
        // Generate all other users
        for ($i = 0; $i <= $this->numberOfUsersPerRole; $i++) {
            $gender = $this->faker->numberBetween(1, 2);
            $personalData = $this->generatePersonalData($gender);
            $this->usedEmails[] = $personalData['email'];

            $this->userInserts[] = [
                'creator_id' => $companyAdministratorId,
                'id'         => $this->userIdCounter,
                'company_id' => $companyId,
                'first_name' => $personalData['firstName'],
                'last_name'  => $personalData['lastName'],
                'full_name'  => "{$personalData['firstName']} {$personalData['lastName']}",
                'email'      => $personalData['email'],
                'password'   => $this->userPassword,
                'mobile'     => $this->faker->phoneNumber(),
                'gender'     => $gender,
                'language'   => 1,
                'created_at' => $this->safeDateTimeBetween(),
            ];

            // Set user role 1:1
            $this->userRoleInserts[] = [
                'model_type' => User::class,
                'model_id'   => $this->userIdCounter,
                'role_id'    => $roleId,
            ];

            $this->userIds[] = $this->userIdCounter;
            $this->userIdCounter++;
        }
    }

    /**
     * Add email to used ones.
     *
     * @param  string $email
     * @return void
     */
    private function setUsedEmail(string $email): void
    {
        $this->usedEmails[] = $email;
    }

    /**
     * Load all used emails in database.
     *
     * @return void
     */
    public function loadUsedEmails(): void
    {
        $this->usedEmails = User::all()->pluck('email')->toArray();
    }

    /**
     * Generate unique first last name and email of a user.
     *
     * @param  int   $gender
     * @return array
     */
    public function generatePersonalData(int $gender): array
    {
        $firstName = $gender === Gender::Male->value ? $this->faker->firstNameMale : $this->faker->firstNameFemale;
        $lastName = $this->faker->lastName;

        $email = StringHelper::generateEmailFromName($firstName.$lastName);

        if (in_array($email, $this->usedEmails, true)) {
            return $this->generatePersonalData($gender);
        }

        $this->setUsedEmail($email);

        return [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
        ];
    }
}
