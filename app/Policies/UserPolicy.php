<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {

        return $user->hasAnyRole(['Root', 'Company Administrator']) ?
             $user->can('view-any-base-user') :
             $user->can('view-any-crm-user');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User $user
     * @param  User $viewUser
     * @return bool
     */
    public function view(User $user, User $viewUser): bool
    {
        if ($user->hasRole('Company Administrator')) {
            return $viewUser->company_id === $user->company_id && $user->can('view-base-user');
        }

        return $viewUser->creator->company_id === $user->company_id && $user->can('view-crm-user');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Root', 'Company Administrator']) ?
            $user->can('create-base-user') :
            $user->can('create-crm-user');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  User $editUser
     * @return bool
     */
    public function update(User $user, User $editUser): bool
    {
        if ($user->hasRole(['Root', 'Company Administrator'])) {
            if ($user->hasRole('Company Administrator')) {
                return $editUser->company_id === $user->company_id && $user->can('edit-base-user');
            }

            return $user->can('edit-base-user');
        }

        return $editUser->creator->company_id === $user->company_id && $user->can('edit-crm-user');
    }
}
