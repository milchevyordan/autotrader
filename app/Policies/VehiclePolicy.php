<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-vehicle');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-vehicle');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User    $user
     * @param  Vehicle $vehicle
     * @return bool
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->creator->company_id === $user->company_id && $user->can('edit-vehicle');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User    $user
     * @param  Vehicle $vehicle
     * @return bool
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->creator->company_id === $user->company_id && $user->can('delete-vehicle');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User    $user
     * @param  Vehicle $vehicle
     * @return bool
     */
    public function restore(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->creator->company_id === $user->company_id && $user->can('restore-vehicle');
    }

    /**
     * Determine whether the user can duplicate vehicle.
     *
     * @param  User    $user
     * @param  Vehicle $vehicle
     * @return bool
     */
    public function duplicate(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->creator->company_id === $user->company_id && $user->can('duplicate-vehicle');
    }

    /**
     * Determine whether the user can view overview page.
     *
     * @param  User $user
     * @return bool
     */
    public function overview(User $user): bool
    {
        return $user->can('view-any-overview');
    }

    /**
     * Determine whether the user can view following page.
     *
     * @param  User $user
     * @return bool
     */
    public function following(User $user): bool
    {
        return $user->can('view-any-following');
    }

    /**
     * Determine whether the user can view management page.
     *
     * @param  User $user
     * @return bool
     */
    public function management(User $user): bool
    {
        return $user->can('view-any-management');
    }

    /**
     * Determine whether the user can transfer a vehicle to his company.
     *
     * @param  User $user
     * @return bool
     */
    public function transfer(User $user): bool
    {
        return $user->can('transfer-vehicle');
    }
}
