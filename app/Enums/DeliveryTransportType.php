<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum DeliveryTransportType: int
{
    use Enum;

    case Internal_transport = 1;
    case External_transport = 2;
    case Pick_up = 3;
}
