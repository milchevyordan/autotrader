<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLevelService extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'purchase_price',
        'sale_price',
        'in_output',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'in_output'      => 'boolean',
        'purchase_price' => Price::class,
        'sale_price'     => Price::class,
    ];

    /**
     * Specify what fields should not be included in change logs.
     *
     * @var array|string[]
     */
    public array $omittedInChangeLog = [
        'service_level_id',
    ];

    /**
     * Return service level related to that service.
     *
     * @return BelongsTo
     */
    public function serviceLevel(): BelongsTo
    {
        return $this->belongsTo(ServiceLevel::class, 'service_level_id');
    }
}
