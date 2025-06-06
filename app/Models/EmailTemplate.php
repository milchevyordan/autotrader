<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MailType;
use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasCreator;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'text_top',
        'text_bottom',
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'mail_type', 'name', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mail_type' => MailType::class,
    ];
}
