<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\MarginSignedOffSollVsIst;
use App\Services\Workflow\Companies\Vehicx\Steps\ReceivedOriginalVehicleInvoice;
use App\Services\Workflow\Companies\Vehicx\Steps\TransportInboundCosts;
use App\Services\Workflow\Companies\Vehicx\Steps\TransportOutboundCosts;
use App\Services\Workflow\Companies\Vehicx\Steps\WorkOrderCosts;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class CompletingFinancially extends Status
{
    public const NAME = 'Completing Financially';

    private const STEP_CLASSES = [
        ReceivedOriginalVehicleInvoice::class,
        TransportInboundCosts::class,
        TransportOutboundCosts::class,
        WorkOrderCosts::class,
        MarginSignedOffSollVsIst::class,
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
