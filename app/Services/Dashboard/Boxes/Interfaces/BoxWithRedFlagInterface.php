<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface BoxWithRedFlagInterface
{
    public function getRedFlagBuilder(): Builder;
}
