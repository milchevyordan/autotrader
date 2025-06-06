<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum PDC: int
{
    use Enum;

    case Back_pdc = 1;
    case Front_and_back_pdc = 2;
}
