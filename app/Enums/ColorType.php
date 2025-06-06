<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum ColorType: int
{
    use Enum;

    case Metallic = 1;
    case Pearl = 2;
    case Solid = 3;
    case Uni = 4;
    case Matt = 5;
    case Special_Paint = 6;
}
