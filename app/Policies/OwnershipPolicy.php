<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ownership;
use App\Models\User;

class OwnershipPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-ownership');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User      $user
     * @param Ownership $ownership
     */
    public function view(User $user, Ownership $ownership): bool
    {
        return $user->can('view-ownership') && $ownership->user_id == auth()->id();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     */
    public function create(User $user): bool
    {
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User      $user
     * @param Ownership $ownership
     */
    public function update(User $user, Ownership $ownership): bool
    {
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User      $user
     * @param Ownership $ownership
     */
    public function delete(User $user, Ownership $ownership): bool
    {
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User      $user
     * @param Ownership $ownership
     */
    public function restore(User $user, Ownership $ownership): bool
    {
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User      $user
     * @param Ownership $ownership
     */
    public function forceDelete(User $user, Ownership $ownership): bool
    {
    }

    /**
     * Determine whether the customer can quote reject invitation.
     *
     * @param  User      $user
     * @param  Ownership $ownership
     * @return bool
     */
    public function accept(User $user, Ownership $ownership): bool
    {
        return $user->can('accept-or-reject-ownership') && $ownership->user_id = auth()->id();
    }

    /**
     * Determine whether the customer can quote reject invitation.
     *
     * @param  User      $user
     * @param  Ownership $ownership
     * @return bool
     */
    public function reject(User $user, Ownership $ownership): bool
    {
        return $user->can('accept-or-reject-ownership') && $ownership->user_id = auth()->id();
    }
}
