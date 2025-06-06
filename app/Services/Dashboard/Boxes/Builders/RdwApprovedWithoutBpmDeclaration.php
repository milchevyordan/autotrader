<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\BoxWithRedFlagInterface;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use App\Services\Workflow\Companies\Vehicx\Steps\RdwApproval;
use App\Services\Workflow\Companies\Vehicx\Steps\UploadBpmDeclaration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class RdwApprovedWithoutBpmDeclaration extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface, BoxWithRedFlagInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        $vehicles = Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields);

        self::filterWorkFlowFinishedSteps($vehicles, [RdwApproval::class]);
        self::filterWorkFlowNotFinishedSteps($vehicles, [UploadBpmDeclaration::class]);

        return $vehicles;
    }

    public function getRedFlagBuilder(): Builder
    {
        return (clone $this->getBuilder())->whereHas('workflow', function ($workflowQuery) {
            $workflowQuery->whereHas('finishedSteps', function ($workflowFinishedStepQuery) {
                $workflowFinishedStepQuery->where('created_at', '<', Carbon::now()->subDays(1));
            });
        });
    }
}
