<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CompanyGroup;
use App\Models\User;

class CompanyGroupPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-crm-company-group');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-crm-company-group');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User         $user
     * @param  CompanyGroup $companyGroup
     * @return bool
     */
    public function update(User $user, CompanyGroup $companyGroup): bool
    {
        return $companyGroup->creator->company_id === $user->company_id && $user->can('edit-crm-company-group');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User         $user
     * @param  CompanyGroup $companyGroup
     * @return bool
     */
    public function delete(User $user, CompanyGroup $companyGroup): bool
    {
        return $companyGroup->creator->company_id === $user->company_id && $user->can('delete-crm-company-group');
    }
}
