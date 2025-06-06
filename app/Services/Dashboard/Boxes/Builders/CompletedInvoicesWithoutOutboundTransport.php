<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\DocumentStatus;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\BoxWithRedFlagInterface;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class CompletedInvoicesWithoutOutboundTransport extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface, BoxWithRedFlagInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::PurchaseOrdersDataTable;
    }

    public function getBuilder(): Builder
    {
        $vehicles = Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields)
            ->whereHas('documents', function ($documentQuery) {
                $documentQuery
                    ->where('status', DocumentStatus::Paid);
            })
            ->whereDoesntHave('transportOrderOutbound');

        return $vehicles;
    }

    public function getRedFlagBuilder(): Builder
    {
        return clone $this->getBuilder();
    }
}
