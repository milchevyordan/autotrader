<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum InteriorColour: int
{
    use Enum;

    case Beige = 1;
    case Blue = 2;
    case Brown = 3;
    case Yellow = 4;
    case Grey = 5;
    case Green = 6;
    case Red = 7;
    case Black = 8;
    case Purple = 9;
    case White = 10;
    case Orange = 11;
    case Gold = 12;
    case Other = 13;
}
