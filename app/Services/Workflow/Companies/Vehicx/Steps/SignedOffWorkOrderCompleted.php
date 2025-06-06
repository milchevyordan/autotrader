<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\WorkOrderStatus;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Step;

class SignedOffWorkOrderCompleted extends Step
{
    public const NAME = 'Signed Off Work Order Completed';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $workOrderSignOffDate = null;

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
        if (! $this->workOrderSignOffDate) {
            $this->initIsWorkOrderSignOff();
        }

        return (bool) $this->workOrderSignOffDate;
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
        if (! $this->workOrderSignOffDate) {
            $this->initIsWorkOrderSignOff();
        }

        return $this->workOrderSignOffDate;
    }

    /**
     * Initialize work order sign off date.
     *
     * @return void
     */
    private function initIsWorkOrderSignOff(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'statuses'])) {
            return;
        }

        $statuses = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'statuses'])?->where('status', WorkOrderStatus::Sign_off->value);

        if ($statuses?->isEmpty() || $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'status', 'value']) < WorkOrderStatus::Sign_off->value) {
            return;
        }

        $this->workOrderSignOffDate = $statuses->last()->created_at->format(self::SUMMARY_DATE_FORMAT);
    }
}
