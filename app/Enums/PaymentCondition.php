<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum PaymentCondition: int
{
    use Enum;

    case Payment_immediately = 1;
    case Payment_before_delivery = 2;
    case Payment_before_pickup = 3;
    case Payment_after_pickup = 4;
    case Payment_before_international_transport = 5;
    case Payment_after_landing_in_NL = 6;
    case Payment_after_NL_registration = 7;
    case Payment_immediately_after_delivery = 8;
    case Payment_two_days_after_invoice = 9;
    case Payment_one_week_after_invoice = 10;
    case Payment_two_weeks_after_invoice = 11;
    case See_additional_information = 12;
}
