<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Make;
use App\Models\User;

class MakePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-make');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-make');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  Make $make
     * @return bool
     */
    public function update(User $user, Make $make): bool
    {
        return $make->creator->company_id === $user->company_id && $user->can('edit-make');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  Make $make
     * @return bool
     */
    public function delete(User $user, Make $make): bool
    {
        return $make->creator->company_id === $user->company_id && $user->can('delete-make');
    }
}
