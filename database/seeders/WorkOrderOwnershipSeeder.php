<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Model;

class WorkOrderOwnershipSeeder extends OwnershipSeeder
{
    protected Model $model;

    protected function getModel(): Model
    {
        return new WorkOrder();
    }
}
