<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Transportable extends MorphPivot
{
    protected $table = 'transportables';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'transport_order_id',
        'transportable_id',
        'transportable_type',
        'location_id',
        'location_free_text',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => Price::class,
    ];

    /**
     * Return transport orders related to the resource.
     *
     * @return BelongsTo
     */
    public function transportOrders(): BelongsTo
    {
        return $this->belongsTo(TransportOrder::class);
    }

    /**
     * Define relation with company addresses.
     *
     * @return BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(CompanyAddress::class, 'location_id');
    }
}
