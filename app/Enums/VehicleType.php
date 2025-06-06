<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum VehicleType: int
{
    use Enum;

    case Passenger_vehicle = 1;
    case Company_vehicle_max_3500kg = 2;
    case Camper = 3;
}
