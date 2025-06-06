<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  File $file
     * @return bool
     */
    public function destroy(User $user, File $file): bool
    {
        return $file->fileable->creator->company_id === $user->company_id && $user->can('delete-file');
    }
}
