<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleGroup;

class VehicleGroupPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-vehicle-group');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-vehicle-group');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User         $user
     * @param  VehicleGroup $vehicleGroup
     * @return bool
     */
    public function update(User $user, VehicleGroup $vehicleGroup): bool
    {
        return $vehicleGroup->creator->company_id === $user->company_id && $user->can('edit-vehicle-group');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User         $user
     * @param  VehicleGroup $vehicleGroup
     * @return bool
     */
    public function delete(User $user, VehicleGroup $vehicleGroup): bool
    {
        return $vehicleGroup->creator->company_id === $user->company_id && $user->can('delete-vehicle-group');
    }
}
