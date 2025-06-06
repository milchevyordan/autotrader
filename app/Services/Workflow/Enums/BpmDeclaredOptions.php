<?php

declare(strict_types=1);

namespace App\Services\Workflow\Enums;

use App\Traits\Enum;

enum BpmDeclaredOptions: int
{
    use Enum;

    case Valuation_List = 1;
    case Table = 2;
    case Taxation = 3;
}
