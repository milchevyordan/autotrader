<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum DigitalCockpit: int
{
    use Enum;

    case No = 1;
    case Regular_digital_cockpit = 2;
    case Digital_cockpit_pro = 3;
}
