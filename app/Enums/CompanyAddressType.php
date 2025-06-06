<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum CompanyAddressType: int
{
    use Enum;

    case Postal = 1;
    case Billing = 2;
    case Logistics = 3;
    case Other = 4;
}
