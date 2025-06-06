<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Navigation: int
{
    use Enum;

    case No = 1;
    case Navigation = 2;
}
