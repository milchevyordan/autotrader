<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\WorkOrderType;
use App\Models\Vehicle;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithUrlInterface;
use App\Services\Workflow\Components\Steps\Step;
use Carbon\Carbon;

class StartWorkOrder extends Step implements StepWithUrlInterface
{
    public const NAME = 'Start Work Order';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $workOrderCreatedDate = null;

    public ?Carbon $workOrderCreatedDateCarbonObject = null;

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
        if (! $this->workOrderCreatedDate) {
            $this->initWorkOrderCreatedDate();
        }

        return (bool) $this->workOrderCreatedDate;
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
        if (! $this->workOrderCreatedDate) {
            $this->initWorkOrderCreatedDate();
        }

        return $this->workOrderCreatedDate;
    }

    public function getUrl(): string
    {

        $vehicleable = $this->getSelfProp(['modelsWorkflow', 'vehicleable']);
        $workOrder = $vehicleable->workOrder;

        if ($workOrder) {
            return route('work-orders.edit', $workOrder->id);
        }

        $workOrderableType = $vehicleable instanceof Vehicle ? WorkOrderType::Vehicle->value : WorkOrderType::Service_vehicle->value;

        return route(
            'work-orders.create',
            [
                'type'   => $workOrderableType,
                'filter' => [
                    'id' => $vehicleable->id,
                ],
            ]
        );
    }

    private function initWorkOrderCreatedDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'created_at'])) {
            return;
        }

        $this->workOrderCreatedDateCarbonObject = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'workOrder', 'created_at']);
        $this->workOrderCreatedDate = $this->workOrderCreatedDateCarbonObject?->format(self::SUMMARY_DATE_FORMAT);
    }
}
