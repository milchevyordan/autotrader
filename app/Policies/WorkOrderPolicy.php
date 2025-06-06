<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-work-order');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-work-order');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User      $user
     * @param  WorkOrder $workOrder
     * @return bool
     */
    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $workOrder->creator->company_id === $user->company_id && $user->can('edit-work-order');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User      $user
     * @param  WorkOrder $workOrder
     * @return bool
     */
    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $workOrder->creator->company_id === $user->company_id && $user->can('delete-work-order');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User      $user
     * @param  WorkOrder $workOrder
     * @return bool
     */
    public function restore(User $user, WorkOrder $workOrder): bool
    {
        return $workOrder->creator->company_id === $user->company_id && $user->can('restore-work-order');
    }

    /**
     * Determine whether the user can update the status.
     *
     * @param  User      $user
     * @param  WorkOrder $workOrder
     * @param  int       $status
     * @return bool
     */
    public function updateStatus(User $user, WorkOrder $workOrder, int $status): bool
    {
        return $workOrder->creator->company_id === $user->company_id &&
            ((1 == $status - $workOrder->status->value) || auth()->user()->can('super-change-status'));
    }
}
