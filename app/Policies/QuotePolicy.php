<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-quote');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-quote');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  Quote $quote
     * @return bool
     */
    public function update(User $user, Quote $quote): bool
    {
        return $quote->creator->company_id === $user->company_id && $user->can('edit-quote');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Quote $quote
     * @return bool
     */
    public function delete(User $user, Quote $quote): bool
    {
        return $quote->creator->company_id === $user->company_id && $user->can('delete-quote');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User  $user
     * @param  Quote $quote
     * @return bool
     */
    public function restore(User $user, Quote $quote): bool
    {
        return $quote->creator->company_id === $user->company_id && $user->can('restore-quote');
    }

    /**
     * Determine whether the user can duplicate the quote.
     *
     * @param  User  $user
     * @param  Quote $quote
     * @return bool
     */
    public function duplicate(User $user, Quote $quote): bool
    {
        return $quote->creator->company_id === $user->company_id && $user->can('duplicate-quote');
    }

    /**
     * Determine whether the user can update the status.
     *
     * @param  User  $user
     * @param  Quote $quote
     * @param  int   $status
     * @return bool
     */
    public function updateStatus(User $user, Quote $quote, int $status): bool
    {
        return $quote->creator->company_id === $user->company_id &&
            (
                (1 == $status - $quote->status->value) || auth()->user()->can('super-change-status') ||
                ($status == QuoteStatus::Stop_quote->value && $quote->status->value == QuoteStatus::Sent->value) ||
                ($status == QuoteStatus::Approved->value && $quote->status->value == QuoteStatus::Accepted_by_client->value) ||
                ($status != QuoteStatus::Sent->value && $status == QuoteStatus::Reserve->value)
            );
    }

    /**
     * Check the required.
     *
     * @param  Quote $quote
     * @param  User  $user
     * @return bool
     */
    public function reserve(User $user, Quote $quote): bool
    {
        return $quote->creator->company_id === $user->company_id && QuoteStatus::Sent == $quote->status;
    }
}
