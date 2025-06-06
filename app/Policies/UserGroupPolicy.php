<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserGroup;

class UserGroupPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-user-group');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-user-group');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User      $user
     * @param  UserGroup $userGroup
     * @return bool
     */
    public function update(User $user, UserGroup $userGroup): bool
    {
        return $userGroup->creator->company_id === $user->company_id && $user->can('edit-user-group');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User      $user
     * @param  UserGroup $userGroup
     * @return bool
     */
    public function delete(User $user, UserGroup $userGroup): bool
    {
        return $userGroup->creator->company_id === $user->company_id && $user->can('delete-user-group');
    }
}
