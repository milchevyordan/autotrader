<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-sales-order');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-sales-order');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User       $user
     * @param  SalesOrder $salesOrder
     * @return bool
     */
    public function update(User $user, SalesOrder $salesOrder): bool
    {
        return $salesOrder->creator->company_id === $user->company_id && $user->can('edit-sales-order');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User       $user
     * @param  SalesOrder $salesOrder
     * @return bool
     */
    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        return $salesOrder->creator->company_id === $user->company_id && $user->can('delete-sales-order');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User       $user
     * @param  SalesOrder $salesOrder
     * @return bool
     */
    public function restore(User $user, SalesOrder $salesOrder): bool
    {
        return $salesOrder->creator->company_id === $user->company_id && $user->can('restore-sales-order');
    }

    /**
     * Determine whether the user can update the status.
     *
     * @param  User       $user
     * @param  SalesOrder $salesOrder
     * @param  int        $status
     * @return bool
     */
    public function updateStatus(User $user, SalesOrder $salesOrder, int $status): bool
    {
        return $salesOrder->creator->company_id === $user->company_id && (($status === $salesOrder->status->value + 1) ||
            auth()->user()->can('super-change-status') ||
                ($status == SalesOrderStatus::Approved->value && $salesOrder->status->value == SalesOrderStatus::Submitted->value) ||
                (! $salesOrder->down_payment && $salesOrder->status->value === SalesOrderStatus::Uploaded_signed_contract->value && $status === SalesOrderStatus::Completed->value));
    }
}
