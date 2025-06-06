<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum DocumentStatus: int
{
    use Enum;

    case Concept = 1;
    case Pro_forma = 2;
    case Rejected = 3;
    case Approved = 4;
    case Create_invoice = 5;
    case Sent_to_customer = 6;
    case Paid = 7;
    case Credit_invoice = 8;
}
