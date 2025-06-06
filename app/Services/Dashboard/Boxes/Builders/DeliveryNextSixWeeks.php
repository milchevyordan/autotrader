<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes\Builders;

use App\Models\Vehicle;
use App\Services\Dashboard\Boxes\Builders\Enums\DataTableMethod;
use App\Services\Dashboard\Boxes\Interfaces\ShouldBeCachedInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class DeliveryNextSixWeeks extends BoxBuilder implements ShouldBeCachedInterface
{
    protected function getDataTableMethod(): DataTableMethod
    {
        return DataTableMethod::VehiclesDataTable;
    }

    public function getBuilder(): Builder
    {
        $fromDate = Carbon::now();
        $toDate = Carbon::now()->addWeeks(6);

        return Vehicle::inThisCompany()
            ->select(array_merge(Vehicle::$defaultSelectFields, ['expected_date_of_availability_from_supplier']))
            ->whereBetween(
                'expected_date_of_availability_from_supplier->from[0]',
                [$fromDate, $toDate]
            )->orWhereBetween(
                'expected_date_of_availability_from_supplier->to[0]',
                [$fromDate, $toDate]
            );
    }
}
