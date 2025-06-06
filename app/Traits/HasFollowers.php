<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasFollowers
{
    /**
     * Get all the followers that belong to the model.
     */
    public function followers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'followable');
    }

    /**
     * Check if the model is followed by the logged-in user.
     */
    public function userFollows(): MorphToMany
    {
        return $this->followers()->wherePivot('user_id', auth()->id())->select('id');
    }
}
