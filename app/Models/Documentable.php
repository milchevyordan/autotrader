<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Documentable extends Model
{
    /**
     * Get the document that owns the documentable item.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the owning documentable model (e.g., Product, Service).
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
