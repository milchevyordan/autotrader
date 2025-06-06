<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum AppConnect: int
{
    use Enum;

    case No = 1;
    case App_connect = 2;
}
