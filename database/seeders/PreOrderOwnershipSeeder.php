<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PreOrder;
use Illuminate\Database\Eloquent\Model;

class PreOrderOwnershipSeeder extends OwnershipSeeder
{
    protected Model $model;

    protected function getModel(): Model
    {
        return new PreOrder();
    }
}
