<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'original_name',
        'unique_name',
        'path',
        'order',
        'section',
        'size',
    ];

    /**
     * The attributes that should be hidden for arrays and JSON serialization.
     *
     * @var string[]
     */
    protected $hidden = [
        'url',
    ];

    /**
     * Get all the owning fileable models.
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'fileable_type', 'fileable_id', 'id');
    }
}
