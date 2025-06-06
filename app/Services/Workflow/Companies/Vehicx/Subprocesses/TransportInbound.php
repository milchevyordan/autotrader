<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Subprocesses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Statuses\Intake;
use App\Services\Workflow\Companies\Vehicx\Statuses\TransportPlanned;
use App\Services\Workflow\Companies\Vehicx\Statuses\VehicleReceived;
use App\Services\Workflow\Companies\Vehicx\Statuses\WaitingDelivery;
use App\Services\Workflow\Components\Status;
use App\Services\Workflow\Components\Subprocesses\Subprocess;
use Illuminate\Support\Collection;

class TransportInbound extends Subprocess
{
    public const NAME = 'Transport Inbound';

    public const VUE_COMPONENT_NAME = 'CalendarCheck';

    private const STATUS_CLASSES = [
        TransportPlanned::class,
        WaitingDelivery::class,
        VehicleReceived::class,
        Intake::class,
    ];

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct($modelsWorkflow);
    }

    /**
     * @return string The name of the component
     */
    public function getName(): string
    {
        return __(self::NAME);
    }

    /**
     * @return string
     */
    protected function getVueComponentName(): string
    {
        return self::VUE_COMPONENT_NAME;
    }

    /**
     * @return Collection<string> Status classes
     */
    protected function getStatusClasses(): Collection
    {
        return new Collection(self::STATUS_CLASSES);
    }
}
