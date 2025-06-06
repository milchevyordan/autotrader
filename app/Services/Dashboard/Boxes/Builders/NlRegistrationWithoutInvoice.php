<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Enums\DocumentStatus;
use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Illuminate\Database\Eloquent\Builder;

final class NlRegistrationWithoutInvoice extends WorkflowStepsBoxBuilder implements ShouldBeCachedInterface
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
                $vehicleQuery->whereNotNull('nl_registration_number')
                    ->orWhereNot('nl_registration_number', '');
            })->whereDoesntHave('documents', function ($documentQuery) {
                $documentQuery
                    ->whereNot('status', DocumentStatus::Concept);
            });

        return $vehicles;
    }
}
