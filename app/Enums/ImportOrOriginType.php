<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum ImportOrOriginType: int
{
    use Enum;

    case Import = 1;
    case Origin_registration = 2;
}
