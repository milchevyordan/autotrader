<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum TintedWindows: int
{
    use Enum;

    case No = 1;
    case Extra_tinted_windows = 2;
    case Tinted_windows = 3;
}
