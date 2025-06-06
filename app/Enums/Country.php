<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Country: int
{
    use Enum;

    case Austria = 1;
    case Belgium = 2;
    case Bulgaria = 3;
    case Croatia = 4;
    case Czech_Republic = 5;
    case Denmark = 6;
    case Finland = 7;
    case France = 8;
    case Germany = 9;
    case Greece = 10;
    case Hungary = 11;
    case Ireland = 12;
    case Italy = 13;
    case Lithuania = 14;
    case Luxembourg = 15;
    case Netherlands = 16;
    case Norway = 17;
    case Poland = 18;
    case Portugal = 19;
    case Romania = 20;
    case Serbia = 21;
    case Slovakia = 22;
    case Slovenia = 23;
    case Switzerland = 24;
    case Spain = 25;
    case Sweden = 26;
}
