<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Workflow;

class WorkflowPolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User     $user
     * @param  Workflow $workflow
     * @return bool
     */
    public function view(User $user, Workflow $workflow): bool
    {
        return $workflow->creator->company_id === $user->company_id && $user->can('view-workflow');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-workflow');
    }
}
