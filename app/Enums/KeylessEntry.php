<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum KeylessEntry: int
{
    use Enum;

    case No = 1;
    case Keyless_entry_and_Go = 2;
    case Keyless_Go = 3;
    case Keyless_entry = 4;
}
