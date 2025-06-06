<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum TransportType: int
{
    use Enum;

    case Inbound = 1;
    case Outbound = 2;
    case Other = 3;
}
