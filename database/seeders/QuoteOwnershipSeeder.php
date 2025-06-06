<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Model;

class QuoteOwnershipSeeder extends OwnershipSeeder
{
    protected Model $model;

    protected function getModel(): Model
    {
        return new Quote();
    }
}
