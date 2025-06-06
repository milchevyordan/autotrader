<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Week;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Vehicleable extends MorphPivot
{
    use HasFactory;

    protected $table = 'vehicleables';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'vehicle_id',
        'vehicleable_id',
        'vehicleable_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'delivery_week' => Week::class,
    ];
}
