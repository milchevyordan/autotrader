<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderOwnershipSeeder extends OwnershipSeeder
{
    protected Model $model;

    protected function getModel(): Model
    {
        return new ServiceOrder();
    }
}
