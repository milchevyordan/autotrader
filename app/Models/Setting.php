<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'costs_of_damages',
        'transport_inbound',
        'transport_outbound',
        'costs_of_taxation',
        'recycling_fee',
        'sales_margin',
        'leges_vat',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'costs_of_damages'   => Price::class,
        'transport_inbound'  => Price::class,
        'transport_outbound' => Price::class,
        'costs_of_taxation'  => Price::class,
        'recycling_fee'      => Price::class,
        'sales_margin'       => Price::class,
        'leges_vat'          => Price::class,
    ];
}
