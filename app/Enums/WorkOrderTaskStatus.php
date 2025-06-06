<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum WorkOrderTaskStatus: int
{
    use Enum;

    case Open = 1;
    case Completed = 2;
}
