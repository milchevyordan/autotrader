<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum CacheTag: int
{
    use Enum;

    case User_roles = 1;
    case User_permissions = 2;
    case User_notifications = 3;
    case Currency = 4;
    case Vat_percentage = 5;
    case Company_logo = 6;
    case Dashboard_boxes = 7;
    case Pending_ownerships = 8;
}
