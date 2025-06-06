<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\BoxWithRedFlagInterface;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use App\Services\Workflow\Companies\Vehicx\Steps\RdwApproval;
use App\Services\Workflow\Companies\Vehicx\Steps\SubmitBpmDeclarationArticle;
use Illuminate\Database\Eloquent\Builder;

final class NoNlRegistrationWithBpmDeclaration extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface, BoxWithRedFlagInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        $vehicles = Vehicle::inThisCompany()
            ->select(array_merge(Vehicle::$defaultSelectFields, ['nl_registration_number']))
            ->where(function ($vehicleQuery) {
                $vehicleQuery->whereNull('nl_registration_number')
                    ->orWhere('nl_registration_number', '');
            });

        self::filterWorkFlowFinishedSteps($vehicles, [RdwApproval::class]);
        self::filterWorkFlowNotFinishedSteps($vehicles, [SubmitBpmDeclarationArticle::class]);

        return $vehicles;
    }

    public function getRedFlagBuilder(): Builder
    {
        return clone $this->getBuilder();
    }
}
