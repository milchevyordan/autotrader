<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Headlights: int
{
    use Enum;

    case LED = 1;
    case Matrix_LED = 2;
    case Adaptive_LED = 3;
    case Xenon = 4;
}
