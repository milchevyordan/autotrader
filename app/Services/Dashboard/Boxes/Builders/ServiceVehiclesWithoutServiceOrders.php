<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Models\ServiceVehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class ServiceVehiclesWithoutServiceOrders extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::ServiceVehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        return ServiceVehicle::inThisCompany()
            ->whereDoesntHave('serviceOrder');
    }
}
