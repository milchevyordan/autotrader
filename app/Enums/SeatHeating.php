<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum SeatHeating: int
{
    use Enum;

    case No = 1;
    case Front_seat_heating = 2;
    case Front_and_back_seat_heating = 3;
}
