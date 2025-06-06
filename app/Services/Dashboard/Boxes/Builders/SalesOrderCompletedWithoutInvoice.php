<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\DocumentStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\BoxWithRedFlagInterface;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class SalesOrderCompletedWithoutInvoice extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface, BoxWithRedFlagInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        $vehicles = Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields)
            ->whereHas('salesOrder', function ($salesOrderQuery) {
                $salesOrderQuery->where('status', SalesOrderStatus::Completed);
            })
            ->whereDoesntHave('documents', function ($documentQuery) {
                $documentQuery
                    ->whereNot('status', '<', DocumentStatus::Create_invoice->value);
            });

        return $vehicles;
    }

    public function getRedFlagBuilder(): Builder
    {
        return clone $this->getBuilder();
    }
}
