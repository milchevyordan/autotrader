<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Step;

class WorkOrderCosts extends Step
{
    public const NAME = 'Work Order Costs';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $workOrderTotalPrice = null;

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
        if (! $this->workOrderTotalPrice) {
            $this->initWorkOrderTotalPrice();
        }

        return (bool) $this->workOrderTotalPrice;
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
        if (! $this->workOrderTotalPrice) {
            $this->initWorkOrderTotalPrice();
        }

        return $this->workOrderTotalPrice;
    }

    /**
     * Initialize transport order sent date.
     *
     * @return void
     */
    private function initWorkOrderTotalPrice(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'total_price'])) {
            return;
        }

        $this->workOrderTotalPrice = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'total_price']);
    }
}
