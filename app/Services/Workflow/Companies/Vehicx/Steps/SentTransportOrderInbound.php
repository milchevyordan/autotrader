<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithUrlInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;
use Throwable;

class SentTransportOrderInbound extends Step implements StepWithUrlInterface, StepWithRedFlagInterface
{
    public const NAME = 'Sent Transport Order';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?Carbon $transportOrderSentDateCarbon = null;

    private ?string $transportOrderSentDate = null;

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
        if (! $this->transportOrderSentDate) {
            $this->initTransportOrderSentDate();
        }

        return (bool) $this->transportOrderSentDate;
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
        if (! $this->transportOrderSentDate) {
            $this->initTransportOrderSentDate();
        }

        return $this->transportOrderSentDate;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $threeDaysAfterToday = Carbon::today()->addDays(3);
        $agreedDateReadyWithSupplier = new AgreedDateReadyWithSupplier($this->getModelsWorkflow());
        $agreedDate = $agreedDateReadyWithSupplier->getFinishedStep()?->finished_at;
        $carbonAgreedDate = $agreedDate ? Carbon::parse($agreedDate) : null;

        $transportOrders = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders']);
        $transportOrderInbound = $transportOrders?->where('transport_type', TransportType::Inbound)?->first();

        $redFlagTriggered = $carbonAgreedDate && $carbonAgreedDate->isBefore($threeDaysAfterToday) && $transportOrderInbound && $transportOrderInbound->status >= TransportOrderStatus::Issued->value;

        return new RedFlag(
            name: self::NAME,
            description: 'Agreed date ready with Supplier is less than three days in the future (< TODAY +3) AND a Transport Order inbound IS Started AND no Transport Order inbound is Sent',
            isTriggered: $redFlagTriggered
        );
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {

        $transportOrderInbound = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Inbound)?->first();

        return $transportOrderInbound ? route('transport-orders.edit', $transportOrderInbound) : null;
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

        $transportOrder = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Inbound)?->first();

        if (! $transportOrder || $transportOrder->status->value < TransportOrderStatus::Issued->value || $transportOrder->statuses->isEmpty()) {
            return;
        }

        $this->transportOrderSentDateCarbon = $transportOrder->statuses->where('status', TransportOrderStatus::Issued->value)?->last()?->created_at;
        $this->transportOrderSentDate = $this->transportOrderSentDateCarbon?->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * Initialize transport order send date in carbon instance.
     *
     * @return null|Carbon
     */
    public function getTransportOrderSentDateCarbon(): ?Carbon
    {
        if (! $this->transportOrderSentDateCarbon) {
            $this->initTransportOrderSentDate();
        }

        return $this->transportOrderSentDateCarbon;
    }
}
