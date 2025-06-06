<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum ServiceOrderStatus: int
{
    use Enum;

    case Concept = 1;
    case Submitted = 2;
    case Completed = 3;
}
