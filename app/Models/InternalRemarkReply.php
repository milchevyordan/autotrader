<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Model;

class InternalRemarkReply extends Model
{
    use HasCreator;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'internal_remark_id',
        'reply',
    ];
}
