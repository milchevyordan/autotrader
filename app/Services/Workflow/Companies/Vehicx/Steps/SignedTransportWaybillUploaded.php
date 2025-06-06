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

class SignedTransportWaybillUploaded extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Signed Transport Waybill Uploaded';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $transportOrderWaybillDate = null;

    private ?Carbon $transportOrderWaybillDateCarbonObject = null;

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
        if (! $this->transportOrderWaybillDate) {
            $this->initTransportOrderSentDate();
        }

        return (bool) $this->transportOrderWaybillDate;
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
        if (! $this->transportOrderWaybillDate) {
            $this->initTransportOrderSentDate();
        }

        return $this->transportOrderWaybillDate;
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

        if (! $transportOrder || $transportOrder->status->value < TransportOrderStatus::Cmr_waybill->value || $transportOrder->statuses->isEmpty()) {
            return;
        }

        $this->transportOrderWaybillDateCarbonObject = $transportOrder->statuses->where('status', TransportOrderStatus::Cmr_waybill->value)->last()?->created_at;

        $this->transportOrderWaybillDate = $this->transportOrderWaybillDateCarbonObject->format(self::SUMMARY_DATE_FORMAT);
    }

    public function getRedFlag(): RedFlag
    {
        return new RedFlag(
            name: 'Signed Transport Waybill Uploaded',
            description: 'If the vehicle was picked up or delivered but the waybill was not uploaded within 5 days (< today -4)',
            isTriggered: $this->checkIsTriggered(),
        );
    }

    private function checkIsTriggered(): bool
    {
        if (! (new TransportOutboundPickedUp($this->getModelsWorkflow()))->isCompleted && ! (new TransportOutboundDelivered($this->getModelsWorkflow()))->isCompleted) {
            return false;
        }

        if (! $this->isCompleted()) {
            return true;
        }

        return $this->transportOrderWaybillDateCarbonObject?->lessThan(today()->subDays(4));
    }
}
