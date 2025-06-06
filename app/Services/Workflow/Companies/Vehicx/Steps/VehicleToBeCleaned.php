<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\WorkOrderTaskType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Step;
use Illuminate\Database\Eloquent\Collection;

class VehicleToBeCleaned extends Step
{
    public const NAME = 'Vehicle To Be Cleaned';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $cleaningTaskDate = null;

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
        if (! $this->cleaningTaskDate) {
            $this->initCleaningTaskDate();
        }

        return (bool) $this->cleaningTaskDate;
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
        if (! $this->cleaningTaskDate) {
            $this->initCleaningTaskDate();
        }

        return $this->cleaningTaskDate ?: null;
    }

    /**
     * Initialize cleaning tasks collection.
     *
     * @return void
     */
    private function initCleaningTaskDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])) {
            return;
        }

        $cleaningTasks = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])?->where('type', WorkOrderTaskType::Clean);

        $this->cleaningTaskDate = $cleaningTasks?->isNotEmpty() ? $cleaningTasks->first()?->created_at->format(self::SUMMARY_DATE_FORMAT) : null;
    }
}
