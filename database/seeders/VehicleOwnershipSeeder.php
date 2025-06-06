<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;

class VehicleOwnershipSeeder extends OwnershipSeeder
{
    protected Model $model;

    protected function getModel(): Model
    {
        return new Vehicle();
    }
}
