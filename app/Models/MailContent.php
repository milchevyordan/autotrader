<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MailContent extends Model
{
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'content',
    ];

    /**
     * Return mail main record.
     *
     * @return HasOne
     */
    public function mail(): HasOne
    {
        return $this->hasOne(Mail::class);
    }
}
