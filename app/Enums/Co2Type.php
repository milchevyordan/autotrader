<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Co2Type: int
{
    use Enum;

    case Wltp = 1;
    case Nedc = 2;
}
