<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum QuoteStatus: int
{
    use Enum;

    case Concept = 1;
    case Submitted = 2;
    case Sent = 3;
    case Stop_quote = 4;
    case Reserve = 5;
    case Closed = 6;
    case Accepted_by_client = 7;
    case Rejected = 8;
    case Approved = 9;
    case Created_sales_order = 10;
}
