<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Step;
use Carbon\Carbon;

class TransportOutboundDelivered extends Step
{
    public const NAME = 'Transport Outbound Delivered';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $transportOrderDeliveredDate = null;

    public ?Carbon $transportOrderDeliveredDateCarbonObject = null;

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
        if (! $this->transportOrderDeliveredDate) {
            $this->initTransportOrderSentDate();
        }

        return (bool) $this->transportOrderDeliveredDate;
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
        if (! $this->transportOrderDeliveredDate) {
            $this->initTransportOrderSentDate();
        }

        return $this->transportOrderDeliveredDate;
    }

    /**
     * Initialize transport order sent date.
     *
     * @return void
     */
    private function initTransportOrderSentDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])) {
            return;
        }

        $transportOrder = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Outbound)?->first();

        if (! $transportOrder || $transportOrder->status->value < TransportOrderStatus::Delivered->value || $transportOrder->statuses->isEmpty()) {
            return;
        }

        $this->transportOrderDeliveredDateCarbonObject = $transportOrder->statuses->where('status', TransportOrderStatus::Delivered->value)->last()?->created_at;

        $this->transportOrderDeliveredDate = $this->transportOrderDeliveredDateCarbonObject->format(self::SUMMARY_DATE_FORMAT);
    }
}
