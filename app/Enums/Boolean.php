<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Boolean: int
{
    use Enum;

    case No = 0;
    case Yes = 1;
}
