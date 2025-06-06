<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class ConfigurationPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-configuration');
    }
}
