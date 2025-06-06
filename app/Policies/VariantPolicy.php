<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Variant;

class VariantPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-variant');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-variant');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User    $user
     * @param  Variant $variant
     * @return bool
     */
    public function update(User $user, Variant $variant): bool
    {
        return $variant->creator->company_id === $user->company_id && $user->can('edit-variant');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User    $user
     * @param  Variant $variant
     * @return bool
     */
    public function delete(User $user, Variant $variant): bool
    {
        return $variant->creator->company_id === $user->company_id && $user->can('delete-variant');
    }
}
