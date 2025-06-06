<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum UnitType: int
{
    use Enum;

    case Pcs = 1;
}
