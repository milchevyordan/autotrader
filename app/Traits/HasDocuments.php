<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Document;
use App\Models\Documentable;
use App\Models\DocumentLine;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasDocuments
{
    /**
     * Get all the documents that belong to the model.
     */
    public function documents(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'documentable');
    }

    /**
     * Return document lines associated with this model.
     *
     * @return HasManyThrough
     */
    public function documentLines(): HasManyThrough
    {
        return $this->hasManyThrough(
            DocumentLine::class,
            Documentable::class,
            'documentable_id',
            'document_id',
            'id',
            'document_id'
        )->where('documentable_type', static::class);
    }
}
