<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ServiceLevel;
use App\Models\User;

class ServiceLevelPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-service-level');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User         $user
     * @param  ServiceLevel $serviceLevel
     * @return bool
     */
    public function view(User $user, ServiceLevel $serviceLevel): bool
    {
        return $serviceLevel->creator->company_id === $user->company_id && $user->can('view-service-level');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-service-level');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User         $user
     * @param  ServiceLevel $serviceLevel
     * @return bool
     */
    public function update(User $user, ServiceLevel $serviceLevel): bool
    {
        return $serviceLevel->creator->company_id === $user->company_id && $user->can('edit-service-level');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User         $user
     * @param  ServiceLevel $serviceLevel
     * @return bool
     */
    public function delete(User $user, ServiceLevel $serviceLevel): bool
    {
        return $serviceLevel->creator->company_id === $user->company_id && $user->can('delete-service-level');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User         $user
     * @param  ServiceLevel $serviceLevel
     * @return bool
     */
    public function restore(User $user, ServiceLevel $serviceLevel): bool
    {
        return $serviceLevel->creator->company_id === $user->company_id && $user->can('restore-service-level');
    }
}
