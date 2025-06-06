<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum TransportOrderFileType: int
{
    use Enum;

    case Stickervel = 1;
    case Pick_Up_Authorization = 2;
    case Transport_Request = 3;
}
