<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\WorkOrderTaskStatus;
use App\Enums\WorkOrderTaskType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class CleaningCompleted extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Cleaning Completed';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $cleaningCompletedDate = null;

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
        if (! $this->cleaningCompletedDate) {
            $this->initCleaningCompletedDate();
        }

        return (bool) $this->cleaningCompletedDate;
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
        if (! $this->cleaningCompletedDate) {
            $this->initCleaningCompletedDate();
        }

        return $this->cleaningCompletedDate;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $plannedDateCleaning = new PlannedDateCleaning($this->getModelsWorkflow());

        return new RedFlag(
            name: self::NAME,
            description: 'If the Vehicle was received longer than two days ago (< Today -2) AND no complete copy has been uploaded',
            isTriggered: ! $this->isCompleted() && $plannedDateCleaning->getDateFinished()?->isBefore(Carbon::today()->subDays(2))
        );
    }

    /**
     * Init last damage repair task completed date.
     *
     * @return void
     */
    private function initCleaningCompletedDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])) {
            return;
        }

        $cleaningTasks = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])?->where('type', WorkOrderTaskType::Clean);

        if ($cleaningTasks?->isEmpty()) {
            return;
        }

        $allCleaningTasksCount = $cleaningTasks?->count();
        $completedCleaningTasks = $cleaningTasks?->where('status', WorkOrderTaskStatus::Completed)?->whereNotNull('completed_at');
        if ($allCleaningTasksCount != $completedCleaningTasks?->count()) {
            return;
        }

        $this->cleaningCompletedDate = $completedCleaningTasks?->sortByDesc('completed_at')?->first()?->completed_at?->format(self::SUMMARY_DATE_FORMAT);
    }
}
