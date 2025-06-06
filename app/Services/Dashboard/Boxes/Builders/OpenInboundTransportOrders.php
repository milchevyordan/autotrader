<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\BoxWithRedFlagInterface;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class OpenInboundTransportOrders extends BoxBuilder implements ShouldBeCachedInterface, BoxWithRedFlagInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        return Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields)
            ->whereHas('transportOrders', function ($transportOrderQuery) {
                $transportOrderQuery
                    ?->where('transport_type', TransportType::Inbound)
                    ->where('status', '<', TransportOrderStatus::Cmr_waybill->value);
            });
    }

    public function getRedFlagBuilder(): Builder
    {
        return (clone $this->getBuilder())
            ->whereHas('transportOrders', function ($transportOrderQuery) {
                $transportOrderQuery
                    ->where('pick_up_after_date', '<', Carbon::now()->subDays(3));
            });
    }
}
