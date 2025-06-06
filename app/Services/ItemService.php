<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ItemType;
use App\Models\Item;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ItemService
{
    public Model $model;

    /**
     * Update items on resources that have service level.
     *
     * @param  array $validatedRequest
     * @param  Model $model
     * @return void
     */
    public function updateItems(array $validatedRequest, Model $model): void
    {
        $model->orderItems()->delete();

        $orderItemsToCreate = collect($validatedRequest['items'] ?? [])
            ->filter(function ($orderItem) {
                return $orderItem['shouldBeAdded'];
            })
            ->all();

        $model->orderItems()->createMany($orderItemsToCreate);

        $this->setModel($model);
    }

    /**
     * Return items connected to service level provided in the request or additional services passed as a relation.
     *
     * @param  null|MorphMany   $loadedItems
     * @return array|Collection
     */
    public static function getByServiceLevel(?MorphMany $loadedItems = null): Collection|array
    {
        $selectedServiceLevel = request()->input('service_level_id');

        return $selectedServiceLevel ? Item::join('item_service_level', 'items.id', '=', 'item_service_level.item_id')
            ->when($selectedServiceLevel, function ($query) use ($selectedServiceLevel) {
                $query->where('item_service_level.service_level_id', $selectedServiceLevel);
            })
            ->select('items.id', 'items.type', 'items.shortcode', 'items.description', 'items.purchase_price', 'items.sale_price', 'item_service_level.in_output')
            ->get() : $loadedItems?->get() ?? [];
    }

    /**
     * Return datatable of Items by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getItemsDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(Item::$defaultSelectFields)
        ))
            ->setColumn('id', '#', true, true)
            ->setColumn('type', __('Type'), true, true)
            ->setColumn('shortcode', __('Shortcode'), true, true)
            ->setColumn('purchase_price', __('Purchase Price'), true, true)
            ->setColumn('sale_price', __('Sale Price'), true, true)
            ->setTimestamps()
            ->setEnumColumn('type', ItemType::class)
            ->setPriceColumn('purchase_price')
            ->setPriceColumn('sale_price');
    }

    /**
     * Get the value of model.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set the value of model.
     *
     * @param  Model $model
     * @return self
     */
    private function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }
}
