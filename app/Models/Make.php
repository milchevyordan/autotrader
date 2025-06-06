<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Make extends Model
{
    use HasFactory;
    use HasCreator;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
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
     * Array holding fields that should be selected in work builder dataTable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'name', 'created_at', 'updated_at'];

    /**
     * Return all variants that this make is used in.
     *
     * @return HasMany
     */
    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    /**
     * Return all vehicle models that this make is used in.
     *
     * @return HasMany
     */
    public function vehicleModels(): HasMany
    {
        return $this->hasMany(VehicleModel::class);
    }

    /**
     * Return all engines that this make is used in.
     *
     * @return HasMany
     */
    public function engines(): HasMany
    {
        return $this->hasMany(Engine::class);
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
