<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum TransportOrderStatus: int
{
    use Enum;

    case Concept = 1;
    case Offer_requested = 2;
    case Issued = 3;
    case Picked_up = 4;
    case Delivered = 5;
    case Cmr_waybill = 6;
}
