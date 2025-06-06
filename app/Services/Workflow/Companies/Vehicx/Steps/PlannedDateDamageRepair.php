<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderTaskType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PlannedDateDamageRepair extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Planned Date Damage Repair';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?Collection $damageToRepairTasks = null;

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
        return (bool) $this->getDamageToRepairTasks()?->sortBy('planned_date')?->last()?->planned_date;
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
        return $this->getDamageToRepairTasks()?->sortBy('planned_date')?->last()?->planned_date?->format(self::SUMMARY_DATE_FORMAT);
    }

    public function getDateFinished(): ?Carbon
    {
        $summary = $this->getSummary();

        return $summary ? $this->getDamageToRepairTasks()?->sortBy('planned_date')?->last()?->planned_date : null;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $damageToRepair = new DamageToRepair($this->getModelsWorkflow());
        $damageToRepairTasks = $damageToRepair->getDamageToRepairTasks();

        return new RedFlag(
            name: self::NAME,
            description: 'If damage repair is necessary, but not planned within 2 days (<TODAY -1)',
            isTriggered: 'Yes' == $damageToRepair?->getSummary() && $damageToRepairTasks?->sortBy('planned_date')?->last()?->planned_date?->isAfter(Carbon::today()->addDays(2)),
        );
    }

    /**
     * Get the value of damageToRepairTasks.
     *
     * @return ?Collection
     */
    public function getDamageToRepairTasks(): ?Collection
    {
        if (! isset($this->damageToRepairTasks)) {
            $this->initDamageToRepairTasks();
        }

        return $this->damageToRepairTasks;
    }

    /**
     * Initialize damage to repair prop.
     *
     * @return void
     */
    private function initDamageToRepairTasks(): void
    {

        $this->damageToRepairTasks = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'tasks'])?->where('type', WorkOrderTaskType::Damage_repair);

    }
}
