<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use App\Services\Workflow\Companies\Vehicx\Steps\RdwApproval;
use Illuminate\Database\Eloquent\Builder;

final class RdwNotApproved extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        $vehicles = Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields);

        self::filterWorkFlowNotFinishedSteps($vehicles, [RdwApproval::class]);

        return $vehicles;
    }
}
