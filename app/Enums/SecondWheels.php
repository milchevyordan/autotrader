<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum SecondWheels: int
{
    use Enum;

    case No = 1;
    case Second_wheels = 2;
}
