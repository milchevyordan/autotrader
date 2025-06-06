<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class RegistrationOpenWorkOrders extends BoxBuilder implements ShouldBeCachedInterface
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
}
