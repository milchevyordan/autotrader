<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Transmission: int
{
    use Enum;

    case Manual = 1;
    case Automatic = 2;
    case Continuously_variable = 3;
    case Dual_clutch = 4;
    case Automated_manual = 5;
    case Sequential_manual = 6;
    case Tiptronic = 7;
    case Electric_vehicle = 8;
}
