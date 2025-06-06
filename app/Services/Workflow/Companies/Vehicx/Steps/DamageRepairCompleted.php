<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\WorkOrderTaskStatus;
use App\Enums\WorkOrderTaskType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Illuminate\Support\Carbon;

class DamageRepairCompleted extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Damage Repair Completed';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $damageRepairCompletedDate = null;

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
        if (! $this->damageRepairCompletedDate) {
            $this->initDamageRepairCompletedDate();
        }

        return (bool) $this->damageRepairCompletedDate;
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
        if (! $this->damageRepairCompletedDate) {
            $this->initDamageRepairCompletedDate();
        }

        return $this->damageRepairCompletedDate;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $plannedDateDamageRepair = new PlannedDateDamageRepair($this->getModelsWorkflow());
        $plannedDateDamageRepairDate = $plannedDateDamageRepair->getDateFinished();

        return new RedFlag(
            name: self::NAME,
            description: 'If damage repair is not completed within 4 days of the planned date (Damage repair planned for) (<TODAY -4)',
            isTriggered: ! $this->isCompleted() && $plannedDateDamageRepairDate && $plannedDateDamageRepairDate->isBefore(Carbon::today()->addDays(4)),
        );
    }

    /**
     * Init last damage repair task completed date.
     *
     * @return void
     */
    private function initDamageRepairCompletedDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])) {
            return;
        }

        $damageRepairTasks = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])?->where('type', WorkOrderTaskType::Damage_repair);

        if ($damageRepairTasks?->isEmpty()) {
            return;
        }

        $allDamageRepairTasksCount = $damageRepairTasks?->count();
        $completedDamageRepairTasks = $damageRepairTasks?->where('status', WorkOrderTaskStatus::Completed)?->whereNotNull('completed_at');
        if ($allDamageRepairTasksCount != $completedDamageRepairTasks?->count()) {
            return;
        }

        $this->damageRepairCompletedDate = $completedDamageRepairTasks?->sortByDesc('completed_at')?->first()?->completed_at?->format(self::SUMMARY_DATE_FORMAT);
    }
}
