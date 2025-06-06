<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum ConnectedToModulesType: int
{
    use Enum;

    case Single_record = 1;
    case Collection = 2;
    case Collection_first = 3;
}
