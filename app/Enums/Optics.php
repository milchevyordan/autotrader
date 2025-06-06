<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Optics: int
{
    use Enum;

    case Normal_optics = 1;
    case Black_optics = 2;
}
