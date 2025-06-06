<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Warranty: int
{
    use Enum;

    case No = 1;
    case Warranty = 2;
}
