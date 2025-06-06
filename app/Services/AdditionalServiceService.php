<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ServiceLevelService;
use App\Support\CurrencyHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AdditionalServiceService
{
    public Model $model;

    /**
     * Update additional services of resources that have service level.
     *
     * @param  array $validatedRequest
     * @param  Model $model
     * @return $this
     */
    public function updateAdditionalServices(array $validatedRequest, Model $model): self
    {
        $additionalServices = $validatedRequest['additional_services'] ?? [];

        $existingServiceIds = collect($additionalServices)->pluck('id')->filter();
        $model->orderServices()->whereNotIn('id', $existingServiceIds)->delete();

        if (! $additionalServices) {
            return $this;
        }

        foreach ($additionalServices as &$service) {
            $service['orderable_type'] = $model::class;
            $service['orderable_id'] = $model->id;
            $service['sale_price'] = CurrencyHelper::convertCurrencyToUnits($service['sale_price']);
            $service['purchase_price'] = CurrencyHelper::convertCurrencyToUnits($service['purchase_price']);
        }

        $model->orderServices()->upsert(
            $additionalServices,
            ['id'],
            ['name', 'sale_price', 'purchase_price', 'in_output', 'is_service_level']
        );

        $this->setModel($model);

        return $this;
    }

    /**
     * Return additional services connected to service level provided in the request or additional services passed as a relation.
     *
     * @param  null|MorphMany   $loadedItems
     * @return array|Collection
     */
    public static function getByServiceLevel(?MorphMany $loadedItems = null): Collection|array
    {
        $selectedServiceLevel = request(null)->input('service_level_id');

        return $selectedServiceLevel ? ServiceLevelService::whereHas('serviceLevel', function ($serviceLevelQuery) use ($selectedServiceLevel) {
            $serviceLevelQuery->where('id', $selectedServiceLevel);
        })->select(
            'service_level_id',
            'name',
            'purchase_price',
            'sale_price',
            'in_output',
        )->get() :
            $loadedItems?->select(
                'id',
                'orderable_id',
                'orderable_type',
                'name',
                'purchase_price',
                'sale_price',
                'in_output',
                'is_service_level'
            )->get() ?? [];
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
