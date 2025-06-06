<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-service-order');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-service-order');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User         $user
     * @param  ServiceOrder $serviceOrder
     * @return bool
     */
    public function update(User $user, ServiceOrder $serviceOrder): bool
    {
        return $serviceOrder->creator->company_id === $user->company_id && $user->can('edit-service-order');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User         $user
     * @param  ServiceOrder $serviceOrder
     * @return bool
     */
    public function delete(User $user, ServiceOrder $serviceOrder): bool
    {
        return $serviceOrder->creator->company_id === $user->company_id && $user->can('delete-service-order');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User         $user
     * @param  ServiceOrder $serviceOrder
     * @return bool
     */
    public function restore(User $user, ServiceOrder $serviceOrder): bool
    {
        return $serviceOrder->creator->company_id === $user->company_id && $user->can('restore-service-order');
    }

    /**
     * Determine whether the user can update the status.
     *
     * @param  User         $user
     * @param  ServiceOrder $serviceOrder
     * @param  int          $status
     * @return bool
     */
    public function updateStatus(User $user, ServiceOrder $serviceOrder, int $status): bool
    {
        return $serviceOrder->creator->company_id === $user->company_id && (($status === $serviceOrder->status->value + 1) || auth()->user()->can('super-change-status'));
    }
}
