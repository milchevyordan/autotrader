<?php

declare(strict_types=1);

namespace Database\Seeders\Traits;

use App\Models\User;

trait UserPermissionTrait
{
    /**
     * Get users with a specific role or permission. If $companyId is provided, filter by company.
     *
     * @param  string   $permissionName
     * @param  null|int $companyId
     * @return array
     */
    public function getUsersWithPermissionIds(string $permissionName, ?int $companyId = null): array
    {
        $query = User::query();

        // If company ID is provided, add it to the query
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->whereHas('roles.permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->get(['id'])->pluck('id')->all();
    }
}
