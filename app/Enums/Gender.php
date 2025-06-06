<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Gender: int
{
    use Enum;

    case Male = 1;
    case Female = 2;
    case Other = 3;
}
