<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Model;

class SalesOrderOwnershipSeeder extends OwnershipSeeder
{
    protected Model $model;

    protected function getModel(): Model
    {
        return new SalesOrder();
    }
}
