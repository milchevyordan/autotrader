<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Country;
use App\Enums\Gender;
use App\Enums\Locale;

use App\Models\User;
use App\Services\RoleService;
use App\Support\StringHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CrmUserSeeder extends BaseSeeder
{
    private const USERS_PER_ROLE = 5;

    /**
     * Create a new CompanySeeder instance.
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
        $spatieTableNames = config('permission.table_names');

        $companyAdministrators = User::role('Company Administrator')->with([
            'company:id',
        ])->get();
        $crmRoles = RoleService::getCrmRoles()->get();
        $lastUserId = User::latest('id')->value('id');

        $usedEmails = [];

        foreach ($companyAdministrators as $companyAdministrator) {

            foreach ($crmRoles as $crmRole) {

                for ($i = 0; $i < self::USERS_PER_ROLE; $i++) {

                    $gender = $this->faker->randomElement(Gender::values());
                    $userData = $this->generatePersonalData($gender, $usedEmails);

                    $userInserts[] = [
                        'company_id' => $companyAdministrator->company->id,
                        'creator_id' => $companyAdministrator->id,
                        'gender'     => Gender::Other->value,
                        'language'   => Locale::en->value,
                        'first_name' => $userData['firstName'],
                        'last_name'  => $userData['lastName'],
                        'full_name'  => $userData['firstName'].' '.$userData['lastName'],
                        'email'      => $userData['email'],
                        'mobile'     => $this->faker->phoneNumber,
                        'password'   => Str::random(15),
                    ];

                    $usedEmails[] = $userData['email'];

                    $userRoleInserts[] = [
                        'model_type' => User::class,
                        'model_id'   => ++$lastUserId,
                        'role_id'    => $crmRole->id,
                    ];

                }

            }

        }

        User::insert($userInserts);
        DB::table($spatieTableNames['model_has_roles'])->insertOrIgnore($userRoleInserts);
    }

    /**
     * Generate unique first last name and email of a user.
     *
     * @param  int   $gender
     * @return array
     */
    public function generatePersonalData(int $gender, array $usedEmails): array
    {
        $firstName = $gender === Gender::Male->value ? $this->faker->firstNameMale : $this->faker->firstNameFemale;
        $lastName = $this->faker->lastName;

        $email = StringHelper::generateEmailFromName($firstName.$lastName);

        if (in_array($email, $usedEmails, true)) {
            return $this->generatePersonalData($gender, $usedEmails);
        }

        return [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
        ];
    }
}
