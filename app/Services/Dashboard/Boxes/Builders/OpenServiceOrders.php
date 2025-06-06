<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;

use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class OpenServiceOrders extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::ServiceOrdersDataTable;
    }

    public function getBuilder(): Builder
    {
        return ServiceOrder::inThisCompany()
            ->whereNot('status', ServiceOrderStatus::Completed);
    }
}
