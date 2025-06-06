<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-item');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-item');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  Item $item
     * @return bool
     */
    public function update(User $user, Item $item): bool
    {
        return $item->creator->company_id === $user->company_id && $user->can('edit-item');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  Item $item
     * @return bool
     */
    public function delete(User $user, Item $item): bool
    {
        return $item->creator->company_id === $user->company_id && $user->can('delete-item');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User $user
     * @param  Item $item
     * @return bool
     */
    public function restore(User $user, Item $item): bool
    {
        return $item->creator->company_id === $user->company_id && $user->can('restore-item');
    }
}
