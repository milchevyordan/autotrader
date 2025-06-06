<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompanyGroupSeeder extends BaseSeeder
{
    /**
     * Array holding the ids of the users that have permission 'create-crm-company-group'.
     *
     * @var array
     */
    private array $userCanCreateCompanyGroupIds;

    /**
     * Create a new CompanyGroupSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userCanCreateCompanyGroupIds = User::whereHas('roles.permissions', function ($query) {
            $query->where('name', 'create-crm-company-group');
        })->pluck('id')->all();
    }

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $groupInserts = [];

        foreach ($this->userCanCreateCompanyGroupIds as $userId) {
            $groupInserts[] = [
                'creator_id' => $userId,
                'name'       => $this->faker->unique()->company,
                'created_at' => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                'updated_at' => now(),
            ];
        }

        DB::table('company_groups')->insert($groupInserts);
    }
}
