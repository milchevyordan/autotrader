<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\TransportOrderStatus;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class CompletedTransportOrderInboundYesterday extends BoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        return Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields)
            ->whereHas('transportOrderInbound', function ($query) {
                $query->where('status', TransportOrderStatus::Cmr_waybill)
                    ->whereHas('statuses', function ($statusQuery) {
                        $statusQuery->where('status', TransportOrderStatus::Cmr_waybill->value)
                            ->whereBetween('created_at', [
                                Carbon::yesterday()->startOfDay(),
                                Carbon::yesterday()->endOfDay(),
                            ]);
                    });
            });
    }
}
