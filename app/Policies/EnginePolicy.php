<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Engine;
use App\Models\User;

class EnginePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-engine');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-engine');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User   $user
     * @param  Engine $engine
     * @return bool
     */
    public function update(User $user, Engine $engine): bool
    {
        return $engine->creator->company_id === $user->company_id && $user->can('edit-engine');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User   $user
     * @param  Engine $engine
     * @return bool
     */
    public function delete(User $user, Engine $engine): bool
    {
        return $engine->creator->company_id === $user->company_id && $user->can('delete-engine');
    }
}
