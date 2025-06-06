<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\DocumentStatus;
use App\Enums\WorkOrderStatus;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class CompletedInvoiceOpenWorkOrder extends BoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        return Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields)
            ->whereHas('documents', function ($documentQuery) {
                $documentQuery
                    ->where('status', DocumentStatus::Paid);
            })
            ->whereHas('workOrder', function ($workOrderQuery) {
                $workOrderQuery->whereNot('status', WorkOrderStatus::Sign_off);
            });
    }
}
