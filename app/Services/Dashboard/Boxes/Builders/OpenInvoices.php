<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\DocumentStatus;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\BoxWithRedFlagInterface;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class OpenInvoices extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface, BoxWithRedFlagInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        return Vehicle::inThisCompany()
            ->select(Vehicle::$defaultSelectFields)
            ->whereHas('documents', function ($documentQuery) {
                $documentQuery->whereNot('status', DocumentStatus::Paid);
            });
    }

    public function getRedFlagBuilder(): Builder
    {
        return (clone $this->getBuilder())
            ->whereHas('documents', function ($documentQuery) {
                $documentQuery->where('documents.created_at', '<', Carbon::now()->subDays(7));
            });
    }
}
