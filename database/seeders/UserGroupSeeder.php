<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserGroupSeeder extends BaseSeeder
{
    /**
     * Array of users that have the permission to create user groups.
     *
     * @var array
     */
    private array $userCanCreateUserGroupIds;

    /**
     * Create a new UserGroupSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userCanCreateUserGroupIds = User::whereHas('roles.permissions', function ($query) {
            $query->where('name', 'create-user-group');
        })->pluck('id')->all();
    }

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $groupInserts = [];

        foreach ($this->userCanCreateUserGroupIds as $userId) {
            $groupInserts[] = [
                'creator_id' => $userId,
                'name'       => $this->faker->unique()->company,
                'created_at' => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                'updated_at' => now(),
            ];
        }

        DB::table('user_groups')->insert($groupInserts);
    }
}
