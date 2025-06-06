<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum DocumentableType: int
{
    use Enum;

    case Pre_order_vehicle = 1;
    case Vehicle = 2;
    case Service_vehicle = 3;
    case Sales_order_down_payment = 4;
    case Sales_order = 5;
    case Service_order = 6;
    case Work_order = 7;
}
