<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum NationalEuOrWorldType: int
{
    use Enum;

    case National = 1;
    case EU = 2;
    case Rest_of_the_world = 3;
}
