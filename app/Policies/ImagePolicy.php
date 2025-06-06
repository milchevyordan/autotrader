<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Image;
use App\Models\User;

class ImagePolicy
{
    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Image $image
     * @return bool
     */
    public function destroy(User $user, Image $image): bool
    {
        return $image->imageable->creator->company_id === $user->company_id && $user->can('delete-image');
    }
}
