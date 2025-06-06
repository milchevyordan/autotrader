<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\SentPickUpAuthorization;
use App\Services\Workflow\Companies\Vehicx\Steps\SentTransportOrderInbound;
use App\Services\Workflow\Companies\Vehicx\Steps\StartTransportOrderInbound;
use App\Services\Workflow\Companies\Vehicx\Steps\TransportInboundCompanyConfirmation;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class TransportPlanned extends Status
{
    public const NAME = 'Transport Planned';

    private const STEP_CLASSES = [
        StartTransportOrderInbound::class,
        SentTransportOrderInbound::class,
        SentPickUpAuthorization::class,
        TransportInboundCompanyConfirmation::class,
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
    protected function getStepClasses(): Collection
    {
        return new Collection(self::STEP_CLASSES);
    }
}
