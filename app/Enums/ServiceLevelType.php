<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum ServiceLevelType: int
{
    use Enum;

    case System = 1;
    case Client = 2;
}
