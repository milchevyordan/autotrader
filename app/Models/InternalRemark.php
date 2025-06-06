<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalRemark extends Model
{
    use HasCreator;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'creator_id',
        'remarkable_type',
        'remarkable_id',
        'note',
    ];

    /**
     * Return all users related to the internal remark.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Return all replies related to the internal remark.
     *
     * @return HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(InternalRemarkReply::class)->with(['creator']);
    }
}
