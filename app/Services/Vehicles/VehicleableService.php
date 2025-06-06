<?php

declare(strict_types=1);

namespace App\Services\Vehicles;

use App\Models\Quote;
use App\Models\SalesOrder;
use App\Models\Vehicleable;
use App\Services\Service;
use Illuminate\Support\Collection;

class VehicleableService extends Service
{
    /**
     * Quote or Sales order model.
     *
     * @var Quote|SalesOrder
     */
    private Quote|SalesOrder $model;

    /**
     * Create a new QuoteService instance.
     *
     * @param Quote|SalesOrder $model
     */
    public function __construct(Quote|SalesOrder $model)
    {
        $this->setModel($model);
    }

    /**
     * Set the value of model.
     *
     * @param  Quote|SalesOrder $model
     * @return void
     */
    private function setModel(Quote|SalesOrder $model): void
    {
        $this->model = $model;
    }

    /**
     * Get the value of model.
     *
     * @return Quote|SalesOrder
     */
    private function getModel(): Quote|SalesOrder
    {
        return $this->model;
    }

    /**
     * Update vehicles relation of the model resource.
     *
     * @param       $vehicles
     * @return void
     */
    public function syncVehicles($vehicles): void
    {
        $model = $this->getModel();
        $modelId = $model->id;

        foreach ($vehicles as &$vehicleable) {
            if (is_array($vehicleable['delivery_week'])) {
                $deliveryWeekDates = $vehicleable['delivery_week'];
                $deliveryWeekDates = $deliveryWeekDates instanceof Collection ? $deliveryWeekDates : new Collection($deliveryWeekDates);
                $deliveryWeekDates->toJson();
            } else {
                $deliveryWeekDates = $vehicleable['delivery_week'];
            }

            $vehicleable['vehicleable_id'] = $modelId;
            $vehicleable['vehicleable_type'] = $model::class;
            $vehicleable['delivery_week'] = $deliveryWeekDates;
        }

        Vehicleable::upsert(
            $vehicles,
            ['vehicleable_type', 'vehicleable_id', 'vehicle_id'],
            ['delivery_week']
        );
    }
}
