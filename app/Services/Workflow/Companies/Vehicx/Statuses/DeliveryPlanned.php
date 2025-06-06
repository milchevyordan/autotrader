<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\InternalOrExternalTransportOrPickUp;
use App\Services\Workflow\Companies\Vehicx\Steps\PlannedDeliveryDate;
use App\Services\Workflow\Companies\Vehicx\Steps\SentPlannedDeliveryDateToClient;
use App\Services\Workflow\Companies\Vehicx\Steps\SentTransportOrderOutbound;
use App\Services\Workflow\Companies\Vehicx\Steps\StartTransportOrderOutbound;
use App\Services\Workflow\Companies\Vehicx\Steps\TransportOutboundCompanyConfirmation;
use App\Services\Workflow\Companies\Vehicx\Steps\VehicleAllowedToBeDelivered;
use App\Services\Workflow\Companies\Vehicx\Steps\VehicleReadyToBeDelivered;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class DeliveryPlanned extends Status
{
    public const NAME = 'Delivery Planned';

    private const STEP_CLASSES = [
        VehicleReadyToBeDelivered::class,
        VehicleAllowedToBeDelivered::class,
        InternalOrExternalTransportOrPickUp::class,
        StartTransportOrderOutbound::class,
        SentTransportOrderOutbound::class,
        TransportOutboundCompanyConfirmation::class,
        PlannedDeliveryDate::class,
        SentPlannedDeliveryDateToClient::class,
    ];

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct($modelsWorkflow);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return __(self::NAME);
    }

    /**
     * @return Collection<string> Step classes
     */
    public function getStepClasses(): Collection
    {
        return new Collection(self::STEP_CLASSES);
    }
}
