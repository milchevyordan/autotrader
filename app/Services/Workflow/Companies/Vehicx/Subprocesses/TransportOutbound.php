<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Subprocesses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Statuses\Delivered;
use App\Services\Workflow\Companies\Vehicx\Statuses\DeliveryPlanned;
use App\Services\Workflow\Companies\Vehicx\Statuses\LatestCommunicatedDeliveryDate;
use App\Services\Workflow\Components\Status;
use App\Services\Workflow\Components\Subprocesses\Subprocess;
use Illuminate\Support\Collection;

class TransportOutbound extends Subprocess
{
    public const NAME = 'Transport Outbound';

    public const VUE_COMPONENT_NAME = 'Truck';

    private const STATUS_CLASSES = [
        LatestCommunicatedDeliveryDate::class,
        DeliveryPlanned::class,
        Delivered::class,
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
