<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class OpenSalesOrders extends BoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::SalesOrdersDataTable;
    }

    public function getBuilder(): Builder
    {
        return SalesOrder::inThisCompany()
            ->whereNot('status', SalesOrderStatus::Completed);
    }
}
