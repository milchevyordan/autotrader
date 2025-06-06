<?php

declare(strict_types=1);

namespace App\Services\Workflow\Events;

use Exception;
use ReflectionClass;

abstract class BaseEvent
{
    protected string $extendingClassname;

    public function __construct(string $extendingClassname)
    {
        $this->extendingClassname = $extendingClassname;
        $this->validateExtendingClass();
    }

    private function validateExtendingClass()
    {
        $reflection = new ReflectionClass($this);
        $className = $reflection->getShortName();

        if ($className !== $this->extendingClassname) {
            throw new Exception('Class extending BaseVehicleUpdated must be named VehicleUpdated.');
        }
    }
}
