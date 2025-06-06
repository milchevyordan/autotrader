<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PreOrderVehicle;
use App\Models\User;

class PreOrderVehiclePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-pre-order-vehicle');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-pre-order-vehicle');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User            $user
     * @param  PreOrderVehicle $preOrderVehicle
     * @return bool
     */
    public function update(User $user, PreOrderVehicle $preOrderVehicle): bool
    {
        return $preOrderVehicle->creator->company_id === $user->company_id && $user->can('edit-pre-order-vehicle');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User            $user
     * @param  PreOrderVehicle $preOrderVehicle
     * @return bool
     */
    public function delete(User $user, PreOrderVehicle $preOrderVehicle): bool
    {
        return $preOrderVehicle->creator->company_id === $user->company_id && $user->can('delete-pre-order-vehicle');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User            $user
     * @param  PreOrderVehicle $preOrderVehicle
     * @return bool
     */
    public function restore(User $user, PreOrderVehicle $preOrderVehicle): bool
    {
        return $preOrderVehicle->creator->company_id === $user->company_id && $user->can('restore-pre-order-vehicle');
    }
}
