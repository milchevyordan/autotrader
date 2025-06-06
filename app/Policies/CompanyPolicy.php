<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Root') ?
            $user->can('view-any-base-company') :
            $user->can('view-any-crm-company');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Root') ?
            $user->can('create-base-company') :
            $user->can('create-crm-company');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User    $user
     * @param  Company $company
     * @return bool
     */
    public function update(User $user, Company $company): bool
    {
        if ($user->hasRole('Root')) {
            return $user->can('edit-base-company');
        }

        return $company->creator->company_id === $user->company_id && $user->can('edit-crm-company');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User    $user
     * @param  Company $company
     * @return bool
     */
    public function delete(User $user, Company $company): bool
    {
        return $user->hasRole('Root') ?
            false :
            $company->creator->company_id === $user->company_id && $user->can('delete-crm-company');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User    $user
     * @param  Company $company
     * @return bool
     */
    public function restore(User $user, Company $company): bool
    {
        return $user->hasRole('Root') ?
            false : $company->creator->company_id === $user->company_id && $user->can('restore-crm-company');
    }

    /**
     * Determine whether the user can change company logo.
     *
     * @param  User $user
     * @return bool
     */
    public function updateLogo(User $user): bool
    {
        return $user->can('update-company-logo');
    }
}
