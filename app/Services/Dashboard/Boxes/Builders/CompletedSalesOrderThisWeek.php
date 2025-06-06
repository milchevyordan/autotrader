<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\SalesOrderStatus;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class CompletedSalesOrderThisWeek extends BoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        return Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields)
            ->whereHas('salesOrder', function ($query) {
                $query->where('status', SalesOrderStatus::Completed)
                    ->whereHas('statuses', function ($statusQuery) {
                        $statusQuery->where('status', SalesOrderStatus::Completed->value)
                            ->whereBetween('created_at', [
                                Carbon::now()->startOfWeek(),
                                Carbon::now()->endOfWeek(),
                            ]);
                    });
            });
    }
}
