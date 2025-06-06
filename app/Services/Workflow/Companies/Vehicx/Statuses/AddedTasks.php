<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\AddedWorkOrderTasks;
use App\Services\Workflow\Companies\Vehicx\Steps\DamageToRepair;
use App\Services\Workflow\Companies\Vehicx\Steps\PlannedDateCleaning;
use App\Services\Workflow\Companies\Vehicx\Steps\PlannedDateDamageRepair;
use App\Services\Workflow\Companies\Vehicx\Steps\StartWorkOrder;
use App\Services\Workflow\Companies\Vehicx\Steps\VehicleToBeCleaned;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class AddedTasks extends Status
{
    public const NAME = 'Added Tasks';

    private const STEP_CLASSES = [
        StartWorkOrder::class,
        AddedWorkOrderTasks::class,
        DamageToRepair::class,
        PlannedDateDamageRepair::class,
        VehicleToBeCleaned::class,
        PlannedDateCleaning::class,
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

    protected function getStepClasses(): Collection
    {
        return new Collection(self::STEP_CLASSES);
    }
}
