<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\ExpectedReceiveDate;
use App\Services\Workflow\Companies\Vehicx\Steps\SentCmrToSupplier;
use App\Services\Workflow\Companies\Vehicx\Steps\VehicleReceived as VehicleReceivedStep;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class VehicleReceived extends Status
{
    public const NAME = 'Vehicle Received';

    private const STEP_CLASSES = [
        ExpectedReceiveDate::class,
        VehicleReceivedStep::class,
        SentCmrToSupplier::class,
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
