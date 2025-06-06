<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Variant;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VariantService
{
    /**
     * Get the value of variants.
     *
     * @param  ?int       $makeId
     * @return Collection
     */
    public static function getVariants(?int $makeId = null): Collection
    {
        $selectedMakeId = request(null)->input('make_id', $makeId);

        return (new MultiSelectService(
            Variant::when(
                $selectedMakeId,
                fn ($query) => $query->where('make_id', $selectedMakeId),
                fn ($query) => $query->whereNull('id')
            )
        ))->dataForSelect();
    }

    /**
     * Return datatable of Variants by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getVariantsDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(array_diff(Variant::$defaultSelectFields, ['creator_id']))
        ))
            ->setRelation('make', ['id', 'name'])
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('name', __('Variant'), true, true)
            ->setTimestamps();
    }
}
