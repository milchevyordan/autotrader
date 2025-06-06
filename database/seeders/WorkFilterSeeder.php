<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Services\RedFlag\Initiator;
use Illuminate\Support\Facades\Auth;

class WorkFilterSeeder extends BaseSeeder
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $creatorId) {
            $user = User::role('Management')->whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->first();

            if (! $user) {
                continue;
            }

            Auth::login($user);
            new Initiator();
            Auth::logout();
        }
    }
}
