<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum SportsSeat: int
{
    use Enum;

    case No = 1;
    case Sports_seat = 2;
}
