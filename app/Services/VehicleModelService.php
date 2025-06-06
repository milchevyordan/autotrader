<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\VehicleModel;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VehicleModelService
{
    /**
     * Get the value of vehicleModels.
     *
     * @param  ?int       $makeId
     * @return Collection
     */
    public static function getVehicleModels(?int $makeId = null): Collection
    {
        $selectedMakeId = request(null)->input('make_id', $makeId);

        return (new MultiSelectService(
            VehicleModel::when(
                $selectedMakeId,
                fn ($query) => $query->where('make_id', $selectedMakeId),
                fn ($query) => $query->whereNull('id')
            )
        ))->dataForSelect();
    }

    /**
     * Return datatable of Vehicle Models by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getVehicleModelsDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(array_diff(VehicleModel::$defaultSelectFields, ['creator_id']))
        ))
            ->setRelation('make', ['id', 'name'])
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('name', __('Model'), true, true)
            ->setTimestamps();
    }
}
