<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum OwnershipStatus: int
{
    use Enum;

    case Pending = 1;
    case Cancelled = 2;
    case Rejected = 3;
    case Accepted = 4;
}
