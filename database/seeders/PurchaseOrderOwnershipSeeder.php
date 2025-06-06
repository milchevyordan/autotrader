<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderOwnershipSeeder extends OwnershipSeeder
{
    protected Model $model;

    protected function getModel(): Model
    {
        return new PurchaseOrder();
    }
}
