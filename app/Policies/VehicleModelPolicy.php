<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleModel;

class VehicleModelPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-vehicle-model');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-vehicle-model');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User         $user
     * @param  VehicleModel $vehicleModel
     * @return bool
     */
    public function update(User $user, VehicleModel $vehicleModel): bool
    {
        return $vehicleModel->creator->company_id === $user->company_id && $user->can('edit-vehicle-model');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User         $user
     * @param  VehicleModel $vehicleModel
     * @return bool
     */
    public function delete(User $user, VehicleModel $vehicleModel): bool
    {
        return $vehicleModel->creator->company_id === $user->company_id && $user->can('delete-vehicle-model');
    }
}
