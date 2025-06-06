<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TransportOrderStatus;
use App\Models\TransportOrder;
use App\Models\User;

class TransportOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-transport-order');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-transport-order');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User           $user
     * @param  TransportOrder $transportOrder
     * @return bool
     */
    public function update(User $user, TransportOrder $transportOrder): bool
    {
        return $transportOrder->creator->company_id === $user->company_id && $user->can('edit-transport-order');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User           $user
     * @param  TransportOrder $transportOrder
     * @return bool
     */
    public function delete(User $user, TransportOrder $transportOrder): bool
    {
        return $transportOrder->creator->company_id === $user->company_id && $user->can('delete-transport-order');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User           $user
     * @param  TransportOrder $transportOrder
     * @return bool
     */
    public function restore(User $user, TransportOrder $transportOrder): bool
    {
        return $transportOrder->creator->company_id === $user->company_id && $user->can('restore-transport-order');
    }

    /**
     * Determine whether the user can update the status.
     *
     * @param  User           $user
     * @param  TransportOrder $transportOrder
     * @param  int            $status
     * @return bool
     */
    public function updateStatus(User $user, TransportOrder $transportOrder, int $status): bool
    {
        $differance = ($status === TransportOrderStatus::Issued->value && $transportOrder->status->value == TransportOrderStatus::Concept->value) ? 2 : 1;

        return $transportOrder->creator->company_id === $user->company_id && (($status - $transportOrder->status->value == $differance) || auth()->user()->can('super-change-status'));
    }
}
