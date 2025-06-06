<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Vehicle;
use App\Models\Vehicleable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasVehicles
{
    /**
     * Represents the 'vehicleable' relationship.
     *
     * @return MorphToMany
     */
    public function vehicles(): MorphToMany
    {
        return $this->morphToMany(Vehicle::class, 'vehicleable')->withPivot('delivery_week')->using(Vehicleable::class);
    }
}
