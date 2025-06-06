<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Coc: int
{
    use Enum;

    case No = 1;
    case Yes = 2;
    case Requested = 3;
    case Unknown = 4;
}
