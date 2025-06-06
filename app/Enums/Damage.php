<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Damage: int
{
    use Enum;

    case No_damages = 1;
    case Showroom_ready_polished = 2;
    case Showroom_ready_washed = 3;
    case Light_user_damages_possible_max_300_euros = 4;
    case User_damages_possible_max_500_euros = 5;
    case Damages_possible_max_750_euros = 6;
    case Damages_possible_max_1000_euros = 7;
    case Damages_see_additional_information = 8;
    case Brand_new_vehicle = 9;
}
