<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum CompanyType: int
{
    use Enum;

    case Base = 1;
    case General = 2;
    case Supplier = 3;
    case Transport = 4;
}
