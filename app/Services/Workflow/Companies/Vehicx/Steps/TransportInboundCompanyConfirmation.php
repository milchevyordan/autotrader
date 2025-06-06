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
use Throwable;

class TransportInboundCompanyConfirmation extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Transport company Confirmation';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $transportOrderIssuedDate = null;

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
        if (! $this->transportOrderIssuedDate) {
            $this->initTransportOrderSentDate();
        }

        return (bool) $this->transportOrderIssuedDate;
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
        if (! $this->transportOrderIssuedDate) {
            $this->initTransportOrderSentDate();
        }

        return $this->transportOrderIssuedDate;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {

        $twoDaysAgo = Carbon::today()->subDays(2);
        $transportOrders = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders']);
        $transportOrderInbound = $transportOrders?->where('transport_type', TransportType::Inbound)?->first();

        $issuedStatusesCreatedTwoDaysAgo = $transportOrderInbound ? $transportOrderInbound?->statuses?->filter(function ($statusable) use ($twoDaysAgo) {
            $carbonCreatedAt = Carbon::parse($statusable->created_at);

            return $statusable->status == TransportOrderStatus::Issued->value && $carbonCreatedAt->isBefore($twoDaysAgo);
        }) : null;

        return new RedFlag(
            name: self::NAME,
            description: 'Transport Order is Sent more than two days ago  (< TODAY -2) AND Transport Confirmation is not yet checked',
            isTriggered: $issuedStatusesCreatedTwoDaysAgo && ! $this->isCompleted()
        );
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

        $this->transportOrderIssuedDate = $transportOrder->statuses->where('status', TransportOrderStatus::Issued->value)->last()?->created_at->format(self::SUMMARY_DATE_FORMAT);
    }
}
