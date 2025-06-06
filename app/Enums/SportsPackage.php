<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum SportsPackage: int
{
    use Enum;

    case No = 1;
    case Sport_interior = 2;
    case Sport_exterior = 3;
    case Sport_interior_and_exterior = 4;
}
