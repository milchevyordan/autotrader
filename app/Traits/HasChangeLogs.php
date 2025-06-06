<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\ChangeLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasChangeLogs
{
    /**
     * Retrieve change log of the resource.
     *
     * @return MorphMany
     */
    public function changeLogs(): MorphMany
    {
        return $this->morphMany(ChangeLog::class, 'changeable')->with(['creator']);
    }
}
