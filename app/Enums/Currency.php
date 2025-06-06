<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Currency: int
{
    use Enum;

    case Euro_EUR = 1;
    case Danish_krone_DKK = 2;
    case Norwegian_krone_NOK = 3;
    case Polish_zloty_PLN = 4;
    case Hungarian_forint_HUF = 5;
    case Czech_koruna_CZK = 6;
    case Swedish_krona_SEK = 7;
    case Swiss_franc_CHF = 8;
    case British_pound_GBP = 9;
    case US_dollar_USD = 10;
    case Japanese_yen_JPY = 11;
}
