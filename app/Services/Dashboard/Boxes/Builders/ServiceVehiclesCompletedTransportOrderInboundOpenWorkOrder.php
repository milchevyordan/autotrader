<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\TransportOrderStatus;
use App\Enums\WorkOrderStatus;
use App\Models\ServiceVehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class ServiceVehiclesCompletedTransportOrderInboundOpenWorkOrder extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::ServiceVehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        $vehicles = ServiceVehicle::inThisCompany()
            ->whereHas('transportOrderInbound', function ($query) {
                $query->where('status', TransportOrderStatus::Cmr_waybill);
            })
            ->whereHas('workOrder', function ($query) {
                $query->whereNot('status', WorkOrderStatus::Sign_off);
            });

        return $vehicles;
    }
}
