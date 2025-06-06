<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum MailType: int
{
    use Enum;

    case Owner_changed = 1;
    case Red_flag = 2;
    case Internal_remarks = 3;
    case Work_order_task = 4;
    case Pdf = 5;
    case Transfer_vehicle = 6;
}
