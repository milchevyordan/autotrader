<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum FuelType: int
{
    use Enum;

    case Petrol = 1;
    case Diesel = 2;
    case PHEV_Petrol = 3;
    case PHEV_Diesel = 4;
    case Hybrid_Petrol = 5;
    case Electric = 6;
    case LPG = 7;
    case Hydrogen = 8;
    case CNG = 9;
}
