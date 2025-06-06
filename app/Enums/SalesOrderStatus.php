<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum SalesOrderStatus: int
{
    use Enum;

    case Concept = 1;
    case Submitted = 2;
    case Rejected = 3;
    case Approved = 4;
    case Sent_to_buyer = 5;
    case Uploaded_signed_contract = 6;
    case Ready_for_down_payment_invoice = 7;
    case Down_payment_invoice_sent = 8;
    case Down_payment_done = 9;
    case Completed = 10;
}
