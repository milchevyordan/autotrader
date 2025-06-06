<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum SupplierOrIntermediary: int
{
    use Enum;

    case Supplier = 1;
    case Intermediary = 2;
}
