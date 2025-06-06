<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\ExpectedDateReadyInPurchaseOrder;
use App\Services\Workflow\Companies\Vehicx\Steps\LatestUpdateExpectDateReady;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class LatestExpectDateReady extends Status
{
    public const NAME = 'Latest Expected date Ready';

    private const STEP_CLASSES = [
        ExpectedDateReadyInPurchaseOrder::class,
        LatestUpdateExpectDateReady::class,
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
