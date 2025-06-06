<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\BoxWithRedFlagInterface;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class OpenPurchaseOrders extends BoxBuilder implements ShouldBeCachedInterface, BoxWithRedFlagInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::PurchaseOrdersDataTable;
    }

    public function getBuilder(): Builder
    {
        return PurchaseOrder::inThisCompany()
            ->whereNot('status', PurchaseOrderStatus::Completed);
    }

    public function getRedFlagBuilder(): Builder
    {
        return (clone $this->getBuilder())
            ->where('purchase_orders.created_at', '<', Carbon::now()->subDays(2));
    }
}
