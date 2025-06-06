<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanyAddressType;
use App\Traits\MapByColum;
use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    use MapByColum;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'company_id',
        'type',
        'address',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => CompanyAddressType::class,
    ];

    /**
     * Specify what fields should not be included in change logs.
     *
     * @var array|string[]
     */
    public array $omittedInChangeLog = [
        'company_id',
    ];
}
