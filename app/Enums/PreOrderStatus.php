<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum PreOrderStatus: int
{
    use Enum;

    case Concept = 1;
    case Submitted = 2;
    case Rejected = 3;
    case Approved = 4;
    case Sent_to_supplier = 5;
    case Uploaded_signed_contract = 6;
    case Down_payment_done = 7;
    case Files_creation = 8;
    case Completed = 9;
}
