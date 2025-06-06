<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Camera: int
{
    use Enum;

    case No = 1;
    case Front_camera = 2;
    case Back_camera = 3;
    case Front_and_back_camera = 4;
}
