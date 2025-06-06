<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Locale: int
{
    use Enum;

    case en = 1;
    case nl = 2;
}
