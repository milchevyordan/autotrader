<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServiceVehicle;
use Illuminate\Database\Eloquent\Model;

class ServiceVehicleOwnershipSeeder extends OwnershipSeeder
{
    protected Model $model;

    protected function getModel(): Model
    {
        return new ServiceVehicle();
    }
}
