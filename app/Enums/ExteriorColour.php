<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum ExteriorColour: int
{
    use Enum;

    case Beige = 1;
    case Blue = 2;
    case Brown = 3;
    case Bronze = 4;
    case Yellow = 5;
    case Grey = 6;
    case Green = 7;
    case Red = 8;
    case Black = 9;
    case Silver = 10;
    case Purple = 11;
    case White = 12;
    case Orange = 13;
    case Gold = 14;
}
