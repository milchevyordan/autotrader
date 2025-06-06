<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrderTask;

class WorkOrderTaskPolicy
{
    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-work-order-task');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User          $user
     * @param  WorkOrderTask $workOrderTask
     * @return bool
     */
    public function update(User $user, WorkOrderTask $workOrderTask): bool
    {
        return $workOrderTask->creator->company_id === $user->company_id && $user->can('edit-work-order-task');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User          $user
     * @param  WorkOrderTask $workOrderTask
     * @return bool
     */
    public function delete(User $user, WorkOrderTask $workOrderTask): bool
    {
        return $workOrderTask->creator->company_id === $user->company_id && $user->can('delete-work-order-task');
    }
}
