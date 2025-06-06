<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderService extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'orderable_type',
        'orderable_id',
        'name',
        'purchase_price',
        'sale_price',
        'in_output',
        'is_service_level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'in_output'        => 'boolean',
        'purchase_price'   => Price::class,
        'sale_price'       => Price::class,
        'is_service_level' => 'boolean',
    ];
}
