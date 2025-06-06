<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\WorkOrderTaskType;
use App\Models\Workflow as ModelsWorkflow;
use App\Models\WorkOrderTask;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use App\Services\Workflow\RedFlags\RedFlag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class PlannedDateCleaning extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Planned Date Cleaning';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    /**
     * @var null|Collection<WorkOrderTask>
     */
    private ?Collection $cleaningTasks;

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
        return (bool) $this->getCleaningTasks()?->sortBy('planned_date')?->last()?->planned_date;
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
        $cleaningTasks = $this->getCleaningTasks();

        return $cleaningTasks ? $cleaningTasks?->sortBy('planned_date')?->last()?->planned_date?->format(self::SUMMARY_DATE_FORMAT) : null;
    }

    /**
     * @return null|Carbon
     */
    public function getDateFinished(): ?Carbon
    {
        $cleaningTasks = $this->getCleaningTasks();

        return $cleaningTasks?->sortBy('planned_date')?->last()?->planned_date;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $modelsWorkflow = $this->getModelsWorkflow();
        $damageToRepair = new DamageToRepair($modelsWorkflow);
        $vehicleToBeCleaned = new VehicleToBeCleaned($modelsWorkflow);
        $cleaningTasks = $this->getCleaningTasks();

        $conditionOne = ! $damageToRepair->getDamageToRepairTasks() && $cleaningTasks && $cleaningTasks
            ->where('planned_date', '<', Carbon::today()->subDay())
            ->isNotEmpty();

        $conditionTwo = $damageToRepair->getDamageToRepairTasks() && $damageToRepair->isCompleted() && $cleaningTasks
            ->where('planned_date', '<', Carbon::today()->subDay())
            ->isNotEmpty();

        return new RedFlag(
            name: self::NAME,
            description: 'If damage repair is not necessary, and cleaning is necessary but not planned within 2 days after Vehicle has to be cleaned (< TODAY - 1) OR if damage repair is necessary and completed, but cleaning is not planned within 2 days (< TODAY - 1)',
            isTriggered: $conditionOne || $conditionTwo
        );
    }

    private function initCleaningTasks(): void
    {

        $this->cleaningTasks = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])?->where('type', WorkOrderTaskType::Clean);

    }

    /**
     * Get the value of cleaningTasks.
     *
     * @return ?Collection<WorkOrderTask>
     */
    public function getCleaningTasks(): ?Collection
    {
        if (! isset($this->cleaningTasks)) {
            $this->initCleaningTasks();
        }

        return $this->cleaningTasks;
    }
}
