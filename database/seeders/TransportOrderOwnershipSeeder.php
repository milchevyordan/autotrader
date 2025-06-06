<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TransportOrder;
use Illuminate\Database\Eloquent\Model;

class TransportOrderOwnershipSeeder extends OwnershipSeeder
{
    protected Model $model;

    protected function getModel(): Model
    {
        return new TransportOrder();
    }
}
