<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\CleaningCompleted;
use App\Services\Workflow\Companies\Vehicx\Steps\DamageRepairCompleted;
use App\Services\Workflow\Companies\Vehicx\Steps\OtherWorkOrderTasksCompleted;
use App\Services\Workflow\Companies\Vehicx\Steps\SignedOffWorkOrderCompleted;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class WorkOrderCompleted extends Status
{
    public const NAME = 'Work Order Completed';

    private const STEP_CLASSES = [
        DamageRepairCompleted::class,
        CleaningCompleted::class,
        OtherWorkOrderTasksCompleted::class,
        SignedOffWorkOrderCompleted::class,
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
