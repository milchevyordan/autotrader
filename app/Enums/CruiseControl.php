<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum CruiseControl: int
{
    use Enum;

    case Adaptive = 1;
    case Regular = 2;
}
