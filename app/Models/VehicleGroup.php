<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use App\Traits\MapByColum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleGroup extends Model
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
    ];

    /**
     * Return vehicles in the group relation.
     *
     * @return HasMany
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'vehicle_group_id', 'id');
    }

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'name'];
}
