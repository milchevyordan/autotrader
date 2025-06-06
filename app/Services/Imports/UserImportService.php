<?php

declare(strict_types=1);

namespace App\Services\Imports;

use App\Enums\Gender;
use App\Enums\Locale;
use App\Models\Company;

use App\Models\User;
use App\Services\CsvReader;
use App\Services\RoleService;
use App\Services\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserImportService extends Service
{
    const COLUMNS = [
        'company_id',
        'company_name',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'language',
        'role',
        'gender',
    ];

    public function importFromCsv(string $filePath, User $creator): void
    {
        $csvReader = new CsvReader($filePath);
        $csvReader->read(self::COLUMNS, ',');
        $users = $csvReader->getData();
        $creatorId = $creator->id;
        $companies = Company::where('creator_id', $creator->id)->select('id', 'company_id')->get();
        $roles = RoleService::getCrmRoles()->get();

        $userInserts = [];
        $userRoleInserts = [];

        $lastUserId = User::count();
        foreach (array_slice($users, 1) as $key => $user) {
            $currentUserId = $lastUserId + $key + 1;
            $userCompany = $companies->where('id', $user['company_id'])->first();
            $gender = Gender::getCaseByName($this->toEnumCaseFormat($user['gender']));
            $role = $roles->where('name', $user['role'])->first();
            $localeEnumType = Locale::getCaseByName($this->toEnumCaseFormat($user['language']));

            $userInserts[] = [
                'creator_id' => $creatorId,
                'company_id' => $userCompany->id,
                'gender'     => $gender,
                'language'   => $localeEnumType,
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'full_name'  => $user['first_name'].' '.$user['last_name'],
                'email'      => $user['email'],
                'mobile'     => $user[8] ?? 'no phone',
                'password'   => Str::random(32),

            ];

            $userRoleInserts[] = [
                'user_id' => $currentUserId,
                'role_id' => $role->id,
            ];

        }

        User::insert($userInserts);
        DB::table('user_role')->insert($userRoleInserts);
    }

    private function toEnumCaseFormat(string $input): string
    {
        return str_replace(' ', '_', $input);
    }
}
