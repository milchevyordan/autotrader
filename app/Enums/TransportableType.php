<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum TransportableType: int
{
    use Enum;

    case Vehicles = 1;
    case Pre_order_vehicles = 2;
    case Service_vehicles = 3;
}
