<?php

declare(strict_types=1);

namespace App\Services\SearchFilter;

use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Filter extends Service
{
    /**
     * @var Builder
     */
    private Builder $builder;

    /**
     * @var null|string
     */
    private ?string $searchText;

    /**
     * @var null|array<string>
     */
    private ?array $searchColumns;

    /**
     * The key represents the relation and the value represents the column name.
     *
     * @var null|array<string, string>
     *
     * @example
     * ```php
     * $childRelations = [
     *     'engine' => 'name',
     *     'make' => 'name',
     * ];
     *  ```
     */
    private null|array $childRelations;

    /**
     * @var array<string, string>
     */
    private array $enumCasts;

    /**
     * Create a new Filter instance.
     *
     * @param Builder     $builder
     * @param null|string $searchText
     * @param null|array  $searchColumns
     * @param null|array  $childRelations
     * @param null|array  $enumCasts
     */
    public function __construct(Builder $builder, ?string $searchText, ?array $searchColumns, ?array $childRelations = null, ?array $enumCasts = null)
    {
        $this->builder = $builder;
        $this->searchText = $searchText;
        $this->searchColumns = $searchColumns;
        $this->childRelations = $childRelations;
        $this->enumCasts = $enumCasts;
    }

    /**
     * Filter records by search, child relations and enums.
     *
     * @return Builder
     */
    public function filterRecords(): Builder
    {
        if (empty($this->searchText)) {
            return $this->builder;
        }

        return $this->builder->where(function ($vehicleQuery) {
            $this->filterSearchColumns($vehicleQuery);
            $this->filterChildRelations($vehicleQuery);
            $this->filterEnums($vehicleQuery);
        });
    }

    /**
     * Filter only by search columns.
     *
     * @param  Builder $query
     * @return void
     */
    private function filterSearchColumns(Builder $query): void
    {
        foreach ($this->searchColumns as $column) {
            $query->orWhere($column, 'LIKE', '%'.$this->searchText.'%');
        }
    }

    /**
     * Filter by child relations.
     *
     * @param  Builder $query
     * @return void
     */
    private function filterChildRelations(Builder $query): void
    {
        foreach ($this->childRelations as $relation => $column) {
            $query->orWhereRelation($relation, function ($subQuery) use ($column) {
                $subQuery->where($column, 'LIKE', '%'.$this->searchText.'%');
            });
        }
    }

    /**
     * Filter by enums.
     *
     * @param  Builder $query
     * @return void
     */
    private function filterEnums(Builder $query): void
    {
        foreach ($this->enumCasts as $castColumn => $castClass) {
            $filteredEnumCases = $castClass::getCasesByName($this->searchText, true);
            $matchedEnumIds = (new Collection($filteredEnumCases))->pluck('value');

            $query->orWhereIn($castColumn, $matchedEnumIds);
        }
    }
}
