<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum InteriorMaterial: int
{
    use Enum;

    case Fabric = 1;
    case Velour = 2;
    case Microfiber = 3;
    case Alcantara = 4;
    case Synthetic_leather = 5;
    case Leather = 6;
    case Fabric_and_alcantara = 7;
    case Fabric_and_microfiber = 8;
    case Synthetic_leather_and_fabric = 9;
    case Leather_and_fabric = 10;
    case Leather_and_alcantara = 11;
    case Full_leather = 12;
    case Extended_leather = 13;
    case New = 14;
}
