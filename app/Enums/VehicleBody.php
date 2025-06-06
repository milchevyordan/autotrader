<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum VehicleBody: int
{
    use Enum;

    case Hatchback = 1;
    case Station = 2;
    case Sedan = 3;
    case SUV = 4;
    case Van = 5;
    case Cabrio = 6;
}
