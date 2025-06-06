<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum VehicleStatus: int
{
    use Enum;

    case New_without_registration = 1;
    case New_registered = 2;
    case Used = 3;
}
