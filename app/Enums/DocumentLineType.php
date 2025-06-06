<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum DocumentLineType: int
{
    use Enum;

    case Main = 1;
    case Bpm = 2;
    case Other = 3;
}
