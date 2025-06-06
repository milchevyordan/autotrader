<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-document');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-document');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User     $user
     * @param  Document $document
     * @return bool
     */
    public function update(User $user, Document $document): bool
    {
        return $document->creator->company_id === $user->company_id && $user->can('edit-document');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User     $user
     * @param  Document $document
     * @return bool
     */
    public function delete(User $user, Document $document): bool
    {
        return $document->creator->company_id === $user->company_id && $user->can('delete-document');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User     $user
     * @param  Document $document
     * @return bool
     */
    public function restore(User $user, Document $document): bool
    {
        return $document->creator->company_id === $user->company_id && $user->can('restore-document');
    }

    /**
     * Determine whether the user can update the status.
     *
     * @param  User     $user
     * @param  int      $status
     * @param  Document $document
     * @return bool
     */
    public function updateStatus(User $user, Document $document, int $status): bool
    {
        return $document->creator->company_id === $user->company_id &&
            ((1 == $status - $document->status->value) || auth()->user()->can('super-change-status')) ||
            ($status == DocumentStatus::Approved->value && $document->status->value == DocumentStatus::Pro_forma->value);
    }

    /**
     * Determine whether the user can duplicate the quote.
     *
     * @param  User     $user
     * @param  Document $document
     * @return bool
     */
    public function duplicate(User $user, Document $document): bool
    {
        return $document->creator->company_id === $user->company_id && $user->can('duplicate-document');
    }
}
