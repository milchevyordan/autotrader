<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum ItemType: int
{
    use Enum;

    case Product = 1;
    case Service = 2;
}
