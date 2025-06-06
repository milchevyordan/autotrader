<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailTemplate;
use App\Models\User;

class EmailTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-email-template');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User          $user
     * @param  EmailTemplate $emailTemplate
     * @return bool
     */
    public function update(User $user, EmailTemplate $emailTemplate): bool
    {
        return $emailTemplate->creator->company_id === $user->company_id && $user->can('edit-email-template');
    }
}
