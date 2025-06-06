<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    use HasCreator;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'creator_id',
        'changeable_type',
        'changeable_id',
        'action',
        'change',
    ];
}
