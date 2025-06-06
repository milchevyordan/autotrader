<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\WorkOrderTaskType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Step;
use Illuminate\Database\Eloquent\Collection;

// use Illuminate\Database\Eloquent\Collection;

class DamageToRepair extends Step
{
    public const NAME = 'Damage To Repair';

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
        if (! $this->damageToRepairTasks) {
            $this->initDamageToRepairTasks();
        }

        return (bool) $this->damageToRepairTasks?->count() ?? false;
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
        if (! $this->damageToRepairTasks) {
            $this->initDamageToRepairTasks();
        }

        return $this->damageToRepairTasks?->first() ? 'Yes' : 'No';
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
