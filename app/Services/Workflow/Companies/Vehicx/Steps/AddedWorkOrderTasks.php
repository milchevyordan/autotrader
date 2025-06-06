<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;

class AddedWorkOrderTasks extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Added Work Order Tasks';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private bool $tasksCreated = false;

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct($modelsWorkflow);
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        return __(self::NAME);
    }

    /**
     * @return null|string
     */
    protected function getModalComponentName(): ?string
    {
        return self::MODAL_COMPONENT_NAME;
    }

    /**
     * @return bool
     */
    protected function isCompleted(): bool
    {
        $this->tasksCreated = $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks']);

        return $this->tasksCreated;
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return self::HAS_QUICK_DATE_FINISH;
    }

    protected function getSummary(): ?string
    {
        return $this->tasksCreated ? $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])?->first()?->created_at->format(self::SUMMARY_DATE_FORMAT) : null;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $startWorkOrder = (new StartWorkOrder($this->getModelsWorkflow()));

        return new RedFlag(
            name: 'Added Work Order Tasks',
            description: 'if a Work Order was started, but no tasks were added within 4 days (< TODAY -3)',
            isTriggered: $startWorkOrder->isCompleted && ! $this->isCompleted && $startWorkOrder->workOrderCreatedDateCarbonObject?->lessThan(today()->subDays(3)),
        );
    }
}
