<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Statusable;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasStatuses
{
    /**
     * Return all the statuses with their respective dates.
     */
    public function statuses(): MorphMany
    {
        return $this->morphMany(Statusable::class, 'statusable');
    }
}
