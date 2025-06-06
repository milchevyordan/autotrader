<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Airconditioning: int
{
    use Enum;

    case No = 1;
    case Automatic_air_conditioning = 2;
    case Manual_air_conditioning = 3;
    case Two_zone_air_conditioning = 4;
    case Three_zone_air_conditioning = 5;
    case Four_zone_air_conditioning = 6;
}
