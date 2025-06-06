<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('edit-setting');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('edit-setting');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User    $user
     * @param  Setting $setting
     * @return bool
     */
    public function update(User $user, Setting $setting): bool
    {
        return $setting->company_id === $user->company_id && $user->can('edit-setting');
    }
}
