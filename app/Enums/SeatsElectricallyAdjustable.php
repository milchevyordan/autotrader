<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum SeatsElectricallyAdjustable: int
{
    use Enum;

    case No = 1;
    case Seats_electrically_adjustable = 2;
}
