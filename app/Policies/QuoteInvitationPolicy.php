<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\QuoteInvitation;
use App\Models\User;

class QuoteInvitationPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-quote-invitation');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User            $user
     * @param  QuoteInvitation $quoteInvitation
     * @return bool
     */
    public function view(User $user, QuoteInvitation $quoteInvitation): bool
    {
        return $user->can('view-quote-invitation') && $quoteInvitation::forRole($user->roles[0]->name)->where('id', $quoteInvitation->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-quote-invitation');
    }

    /**
     * Determine whether the customer can quote reject invitation.
     *
     * @param  User            $user
     * @param  QuoteInvitation $quoteInvitation
     * @return bool
     */
    public function accept(User $user, QuoteInvitation $quoteInvitation): bool
    {
        return 'Customer' == $user->roles[0]->name && $user->can('accept-or-reject-quote-invitation') && ((! isset($quoteInvitation->reservation_until)) || $quoteInvitation->customer_id == $user->id);
    }

    /**
     * Determine whether the customer can quote reject invitation.
     *
     * @param  User            $user
     * @param  QuoteInvitation $quoteInvitation
     * @return bool
     */
    public function reject(User $user, QuoteInvitation $quoteInvitation): bool
    {
        return 'Customer' == $user->roles[0]->name && $user->can('accept-or-reject-quote-invitation') && ((! isset($quoteInvitation->reservation_until)) || $quoteInvitation->customer_id == $user->id);
    }
}
