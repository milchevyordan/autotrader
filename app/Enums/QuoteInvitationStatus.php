<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum QuoteInvitationStatus: int
{
    use Enum;

    case Concept = 1;
    case Closed = 2;
    case Rejected = 3;
    case Accepted = 4;
}
