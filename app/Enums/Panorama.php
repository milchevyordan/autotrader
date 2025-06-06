<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Panorama: int
{
    use Enum;

    case Panorama_sliding_roof = 1;
    case Panorama_roof = 2;
    case Steel_sliding_roof = 3;
    case Glass_sliding_roof = 4;
}
