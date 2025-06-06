<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use App\Services\Workflow\Companies\Vehicx\Steps\AllOriginalDocumentsReceived;
use App\Services\Workflow\Companies\Vehicx\Steps\PassedRandomInspection;
use Illuminate\Database\Eloquent\Builder;

final class ReceivedWithPapersNoRdw extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        $vehicles = Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields)
            ->whereHas('transportOrders', function ($transportOrderQuery) {
                $transportOrderQuery
                    ?->where('transport_type', TransportType::Inbound)
                    ->where('status', TransportOrderStatus::Cmr_waybill);
            });

        self::filterWorkFlowNotFinishedSteps($vehicles, [AllOriginalDocumentsReceived::class, PassedRandomInspection::class]);

        return $vehicles;
    }
}
