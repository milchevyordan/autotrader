<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ServiceVehicle;
use App\Models\User;

class ServiceVehiclePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-service-vehicle');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-service-vehicle');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User                $user
     * @param  ServiceVehicle $serviceVehicle
     * @return bool
     */
    public function update(User $user, ServiceVehicle $serviceVehicle): bool
    {
        return $serviceVehicle->creator->company_id === $user->company_id && $user->can('edit-service-vehicle');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User                $user
     * @param  ServiceVehicle $serviceVehicle
     * @return bool
     */
    public function delete(User $user, ServiceVehicle $serviceVehicle): bool
    {
        return $serviceVehicle->creator->company_id === $user->company_id && $user->can('delete-service-vehicle');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User                $user
     * @param  ServiceVehicle $serviceVehicle
     * @return bool
     */
    public function restore(User $user, ServiceVehicle $serviceVehicle): bool
    {
        return $serviceVehicle->creator->company_id === $user->company_id && $user->can('restore-service-vehicle');
    }
}
