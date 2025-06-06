<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FuelType;
use App\Models\Engine;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EngineService
{
    /**
     * Collection of all the engines for concrete company based on the request.
     *
     * @var Collection<Engine>
     */
    private Collection $engines;

    /**
     * Set the value of engines.
     *
     * @param  ?int                        $makeId
     * @param  ?int                        $fuelId
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function setEngines(?int $makeId, ?int $fuelId): void
    {
        $makeId = request()->get('make_id', $makeId);
        $fuelId = request()->get('fuel', $fuelId);

        $engines = (new MultiSelectService(Engine::when(
            $makeId && $fuelId,
            fn ($query) => $query->where('make_id', $makeId)->where('fuel', $fuelId),
            fn ($query) => $query->whereNull('id')
        )))
            ->dataForSelect();

        $this->engines = $engines;
    }

    /**
     * Get the value of engines.
     *
     * @param  ?int                        $makeId
     * @param  ?int                        $fuelId
     * @return Collection
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getEngines(?int $makeId = null, ?int $fuelId = null): Collection
    {
        $self = new self();

        if (! isset($self->engines)) {
            $self->setEngines($makeId, $fuelId);
        }

        return $self->engines;
    }

    /**
     * Return datatable of Engines by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getEnginesDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(array_diff(Engine::$defaultSelectFields, ['creator_id']))
        ))
            ->setRelation('make', ['id', 'name'])
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('name', __('Engine'), true, true)
            ->setColumn('fuel', __('Fuel type'), true, true)
            ->setTimestamps()
            ->setEnumColumn('fuel', FuelType::class);
    }
}
