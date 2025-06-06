<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum SeatMassage: int
{
    use Enum;

    case Massage_seat = 1;
    case Ventilation_seat = 2;
    case Ventilation_and_massage_seat = 3;
}
