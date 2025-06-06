<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class TransportOutboundPickedUp extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Transport Outbound Picked Up';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $transportOrderPickedUpDate = null;

    private ?Carbon $transportOrderPickedUpDateCarbonObject = null;

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
        if (! $this->transportOrderPickedUpDate) {
            $this->initTransportOrderSentDate();
        }

        return (bool) $this->transportOrderPickedUpDate;
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
        if (! $this->transportOrderPickedUpDate) {
            $this->initTransportOrderSentDate();
        }

        return $this->transportOrderPickedUpDate;
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

        if (! $transportOrder || $transportOrder->status->value < TransportOrderStatus::Picked_up->value || $transportOrder->statuses->isEmpty()) {
            return;
        }

        $this->transportOrderPickedUpDateCarbonObject = $transportOrder->statuses->where('status', TransportOrderStatus::Picked_up->value)->last()?->created_at;

        $this->transportOrderPickedUpDate = $this->transportOrderPickedUpDateCarbonObject?->format(self::SUMMARY_DATE_FORMAT);
    }

    public function getRedFlag(): RedFlag
    {
        $sentTransportOutbound = (new SentTransportOrderOutbound($this->getModelsWorkflow()))->isCompleted;
        $transportOutboundDelivered = (new TransportOutboundDelivered($this->getModelsWorkflow()))->transportOrderDeliveredDateCarbonObject;

        return new RedFlag(
            name: 'Transport Outbound Picked Up',
            description: 'If the TO outbound was sent to a Transport Company but the vehicle has not been picked up (or delivered) within 5 days (<TODAY -4)',
            isTriggered: $sentTransportOutbound && ($this->transportOrderPickedUpDateCarbonObject?->lessThan(today()->subDays(5)) || $transportOutboundDelivered?->lessThan(today()->subDays(4))),
        );
    }
}
