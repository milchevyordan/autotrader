<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum WorkOrderType: int
{
    use Enum;

    case Vehicle = 1;
    case Service_vehicle = 2;
}
