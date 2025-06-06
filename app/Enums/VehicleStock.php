<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum VehicleStock: int
{
    use Enum;

    case Stock_pipeline = 1;
    case Stock = 2;
    case Financial_stock = 3;
    case Sold = 4;
}
