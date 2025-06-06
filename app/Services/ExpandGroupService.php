<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\SearchFilter\Filter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExpandGroupService
{
    /**
     * @var Filter
     */
    private Filter $filter;

    /**
     * Builder instance.
     *
     * @var Builder
     */
    private Builder $builder;

    /**
     * Main group relation.
     *
     * @var string
     */
    private string $mainRelation;

    /**
     * The text we are searching by.
     *
     * @var null|string
     */
    private null|string $searchText;

    /**
     * @var array<string>
     *
     * @example ``` ['vehicles.engine:id,name] ```
     */
    private array $relations;

    /**
     * Array holding the columns in main relation that will be selected.
     *
     * @var array<string>
     */
    private array $relationColumnsToSelect;

    /**
     * The key represents the relation and the value represents the column name.
     *
     * @var null|array<string, string>
     *
     * @example
     * ```php
     * $searchRelations = [
     *     'engine' => 'name',
     *     'make' => 'name',
     * ];
     *  ```
     */
    private array $childRelations;

    /**
     * Columns that will be searchable in main group model.
     *
     * @var null|array<string>
     */
    private ?array $parentFilterColumns;

    /**
     * The key represents the columns that we must search in the main relation.
     *
     * @var null|array<string>
     */
    private ?array $childFilterColumns;

    /**
     * Array holding main relation columns that are cast.
     *
     * @var array<string, string>
     */
    private ?array $enumCasts;

    /**
     * Items count in group when collapsed.
     *
     * @var int
     */
    private int $collapsedGroupItems;

    /**
     * Groups count shown per page.
     *
     * @var int
     */
    private int $perPage;

    /**
     * Create a new ExpandGroupService instance.
     *
     * @param Builder     $builder
     * @param string      $mainRelation
     * @param array       $relations
     * @param array       $childRelations
     * @param int         $collapsedGroupItems
     * @param null|array  $childFilterColumns
     * @param null|string $searchText
     * @param null|array  $relationColumnsToSelect
     * @param null|array  $parentFilterColumns
     * @param null|array  $enumCasts
     * @param int         $perPage
     */
    public function __construct(
        Builder $builder,
        string $mainRelation,
        array $relations,
        array $childRelations,
        int $collapsedGroupItems,
        ?array $childFilterColumns,
        ?string $searchText,
        ?array $relationColumnsToSelect = [],
        ?array $parentFilterColumns = [],
        ?array $enumCasts = null,
        int $perPage = 10,
    ) {
        $this->builder = $builder;
        $this->mainRelation = $mainRelation;
        $this->relations = $relations;
        $this->childRelations = $childRelations;
        $this->collapsedGroupItems = $collapsedGroupItems;
        $this->childFilterColumns = $childFilterColumns;
        $this->searchText = $searchText;
        $this->relationColumnsToSelect = $relationColumnsToSelect;
        $this->parentFilterColumns = $parentFilterColumns;
        $this->enumCasts = $enumCasts;
        $this->perPage = $perPage;
    }

    /**
     * Filter items and limit to their collapsed view.
     *
     * @return Builder
     */
    public function filter(): Builder
    {
        $this->builder->with([
            $this->mainRelation => function ($query) {
                return $query->select($this->relationColumnsToSelect)
                    ->limit($this->collapsedGroupItems);
            },

            ...$this->relations,
        ])
            ->orderBy('id', 'DESC');

        if (empty($this->searchText)) {
            return $this->builder;
        }

        return $this->builder->where(function ($groupQuery) {
            foreach ($this->parentFilterColumns as $parentFilterCol) {
                $groupQuery->orWhere($parentFilterCol, 'LIKE', "%{$this->searchText}%");
            }

            $groupQuery->orWhereHas($this->mainRelation, function ($mainRelationQuery) {
                $this->filter = new Filter(
                    $mainRelationQuery,
                    $this->searchText,
                    $this->childFilterColumns,
                    $this->childRelations,
                    $this->enumCasts
                );

                $this->filter->filterRecords();
                $mainRelationQuery->limit($this->collapsedGroupItems);
            });
        });
    }

    /**
     * Load items when expanded.
     *
     * @return LengthAwarePaginator
     */
    public function getExpandedGroupPaginator(): LengthAwarePaginator
    {
        $loadGroupIds = new Collection(request('loadGroupIds'));

        return $this->getPaginator()->through(function ($item) use ($loadGroupIds) {
            if (! $loadGroupIds->contains($item->id)) {
                return $item;
            }

            return $item->load([
                $this->mainRelation => function ($query) {
                    $query->select($this->relationColumnsToSelect);
                },
                ...$this->relations,
            ]);
        });
    }

    /**
     * Paginate items.
     *
     * @return LengthAwarePaginator
     */
    public function getPaginator(): LengthAwarePaginator
    {
        return $this->filter()->paginate($this->perPage);
    }
}
