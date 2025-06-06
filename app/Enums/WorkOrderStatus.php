<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum WorkOrderStatus: int
{
    use Enum;

    case Open = 1;
    case Completed = 2;
    case Sign_off = 3;
}
