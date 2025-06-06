<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum TowBar: int
{
    use Enum;

    case No = 1;
    case Fixed_tow_bar = 2;
    case Detachable_tow_bar = 3;
    case Manually_collapsible_tow_bar = 4;
    case Electrically_collapsible_tow_bar = 5;
}
