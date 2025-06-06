<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Papers: int
{
    use Enum;

    case Together_with_vehicle = 1;
    case After_cmr = 2;
    case After_delivery = 3;
    case Unknown = 4;
}
