<?php

declare(strict_types=1);

namespace App\Services\Workflow\Events;

use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;

abstract class BaseVehicleUpdated extends BaseEvent
{
    private const EXTENDING_CLASS_NAME = 'VehicleUpdated';

    protected Vehicle $vehicle;

    protected array $validatedRequest;

    public function __construct(Vehicle $vehicle, array $validatedRequest)
    {
        parent::__construct(self::EXTENDING_CLASS_NAME);

        $this->vehicle = $vehicle;
        $this->validatedRequest = $validatedRequest;
    }
}
