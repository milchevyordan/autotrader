<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use App\Traits\MapByColum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    use HasFactory;
    use HasCreator;
    use MapByColum;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'make_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Array holding fields that should be selected in work builder datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'make_id', 'name', 'created_at', 'updated_at'];

    /**
     * Return vehicle model's make.
     *
     * @return BelongsTo
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    /**
     * Return all vehicles that this make is used in.
     *
     * @return HasMany
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Return all service vehicles that this make is used in.
     *
     * @return HasMany
     */
    public function serviceVehicles(): HasMany
    {
        return $this->hasMany(ServiceVehicle::class);
    }

    /**
     * Return all pre order vehicles that this make is used in.
     *
     * @return HasMany
     */
    public function preOrderVehicles(): HasMany
    {
        return $this->hasMany(PreOrderVehicle::class);
    }
}
