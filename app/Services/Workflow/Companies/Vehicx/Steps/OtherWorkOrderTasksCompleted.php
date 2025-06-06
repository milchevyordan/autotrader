<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderTaskStatus;
use App\Enums\WorkOrderTaskType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;
use Illuminate\Console\View\Components\Task;
use Illuminate\Database\Eloquent\Collection;

class OtherWorkOrderTasksCompleted extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Other Work Order Tasks Completed';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $otherCompletedDate = null;

    /**
     * @var null|Collection
     */
    private ?Collection $otherTasks;

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct($modelsWorkflow);

        $this->initOtherTasks();
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
        if (! $this->otherCompletedDate) {
            $this->initOtherCompletedDate();
        }

        return (bool) $this->otherCompletedDate;
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
        if (! $this->otherCompletedDate) {
            $this->initOtherCompletedDate();
        }

        return $this->otherCompletedDate;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $otherTasks = $this->getOtherTasks();
        $notFinishedTasksForWeek = ! $otherTasks
            ? null
            : $otherTasks
                ->where('created_at', '<', Carbon::today()->subDays(7))
                ->where('status', '<', WorkOrderStatus::Completed->value)
                ->first();

        return new RedFlag(
            name: self::NAME,
            description: 'If Other Work Order tasks are not completed within one week after the tasks were added (<TODAY -7)',
            isTriggered: (bool) $notFinishedTasksForWeek,
        );
    }

    public function getOtherTasks()
    {
        if (! isset($this->otherTasks)) {
            $this->initOtherTasks();
        }

        return $this->otherTasks;
    }

    /**
     * Init last damage repair task completed date.
     *
     * @return void
     */
    private function initOtherCompletedDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])) {
            return;
        }

        $otherTasks = $this->getOtherTasks();

        if ($otherTasks?->isEmpty()) {
            return;
        }

        $allOtherTasksCount = $otherTasks?->count();
        $completedOtherTasks = $otherTasks?->where('status', WorkOrderTaskStatus::Completed)?->whereNotNull('completed_at');
        if ($allOtherTasksCount != $completedOtherTasks?->count()) {
            return;
        }

        $this->otherCompletedDate = $completedOtherTasks?->sortByDesc('completed_at')?->first()?->completed_at?->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * @return void
     */
    private function initOtherTasks(): void
    {

        $this->otherTasks = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])?->where('type', WorkOrderTaskType::Other);

    }
}
