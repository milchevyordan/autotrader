<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-purchase-order');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-purchase-order');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User          $user
     * @param  PurchaseOrder $purchaseOrder
     * @return bool
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->creator->company_id === $user->company_id && $user->can('edit-purchase-order');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User          $user
     * @param  PurchaseOrder $purchaseOrder
     * @return bool
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->creator->company_id === $user->company_id && $user->can('delete-purchase-order');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User          $user
     * @param  PurchaseOrder $purchaseOrder
     * @return bool
     */
    public function restore(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->creator->company_id === $user->company_id && $user->can('restore-purchase-order');
    }

    /**
     * Determine whether the user update the status.
     *
     * @param  User          $user
     * @param  PurchaseOrder $purchaseOrder
     * @param  int           $status
     * @return bool
     */
    public function updateStatus(User $user, PurchaseOrder $purchaseOrder, int $status): bool
    {
        return $purchaseOrder->creator->company_id === $user->company_id && (($status === $purchaseOrder->status->value + 1) ||
            auth()->user()->can('super-change-status') ||
                ($status == PurchaseOrderStatus::Approved->value && $purchaseOrder->status->value == PurchaseOrderStatus::Submitted->value) ||
                (! $purchaseOrder->down_payment && $purchaseOrder->status->value === PurchaseOrderStatus::Uploaded_signed_contract->value && $status === PurchaseOrderStatus::Completed->value));
    }
}
