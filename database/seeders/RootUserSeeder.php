<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\Locale;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RootUserSeeder extends BaseSeeder
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Auto-generated seed file.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('users')->delete();

        $spatieTableNames = config('permission.table_names');

        $faker = Faker::create();

        $userInserts = [];
        $modelRoleInserts = [];

        // Generate the admin account with email = .env MAIL_USERNAME="[get the email from here]"
        $userInserts[] = [
            'id'                => 1,
            'first_name'        => 'Root',
            'last_name'         => 'User',
            'full_name'         => 'Root User',
            'language'          => Locale::en->value,
            'email'             => config('app.root_user_email'),
            'email_verified_at' => now(),
            'password'          => Hash::make(config('app.user_password')),
            'gender'            => Gender::Male->value,
            'mobile'            => $faker->phoneNumber(),
        ];

        $modelRoleInserts[] = [
            'model_type' => User::class,
            'model_id'   => 1,
            'role_id'    => 1,
        ];

        DB::table('users')->insert($userInserts);
        // Attach role to users
        DB::table($spatieTableNames['model_has_roles'])->insert($modelRoleInserts);
    }
}
