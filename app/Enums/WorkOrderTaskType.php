<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum WorkOrderTaskType: int
{
    use Enum;

    case Clean = 1;
    case Damage_repair = 2;
    case Other = 3;
}
