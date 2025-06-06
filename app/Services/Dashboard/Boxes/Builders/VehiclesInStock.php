<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class VehiclesInStock extends BoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        return Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields)
            ->whereHas('purchaseOrder', function ($query) {
                $query->where('status', PurchaseOrderStatus::Completed);
            })
            ->whereHas('salesOrder', function ($query) {
                $query->where('status', '<', SalesOrderStatus::Uploaded_signed_contract);
            });
    }
}
