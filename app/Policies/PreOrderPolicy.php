<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PreOrderStatus;
use App\Models\PreOrder;
use App\Models\User;

class PreOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-pre-order');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-pre-order');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User     $user
     * @param  PreOrder $preOrder
     * @return bool
     */
    public function update(User $user, PreOrder $preOrder): bool
    {
        return $preOrder->creator->company_id === $user->company_id && $user->can('edit-pre-order');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User     $user
     * @param  PreOrder $preOrder
     * @return bool
     */
    public function delete(User $user, PreOrder $preOrder): bool
    {
        return $preOrder->creator->company_id === $user->company_id && $user->can('delete-pre-order');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User     $user
     * @param  PreOrder $preOrder
     * @return bool
     */
    public function restore(User $user, PreOrder $preOrder): bool
    {
        return $preOrder->creator->company_id === $user->company_id && $user->can('restore-pre-order');
    }

    /**
     * Determine whether the user can update the status.
     *
     * @param  User     $user
     * @param  PreOrder $preOrder
     * @param  int      $status
     * @return bool
     */
    public function updateStatus(User $user, PreOrder $preOrder, int $status): bool
    {
        return $preOrder->creator->company_id === $user->company_id &&
            (($status === $preOrder->status->value + 1) || auth()->user()->can('super-change-status') ||
                ($status == PreOrderStatus::Approved->value && $preOrder->status->value == PreOrderStatus::Submitted->value));
    }
}
