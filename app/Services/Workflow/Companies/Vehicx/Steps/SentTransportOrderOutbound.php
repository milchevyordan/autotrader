<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithUrlInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;

class SentTransportOrderOutbound extends Step implements StepWithUrlInterface
{
    public const NAME = 'Sent Transport Order';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

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
     * @return null|string
     */
    public function getUrl(): ?string
    {
        $transportOrderOutbound = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Outbound)?->first();

        return $transportOrderOutbound ? route('transport-orders.edit', $transportOrderOutbound->id) : null;
    }

    /**
     * Initialize transport order sent date.
     *
     * @return void
     */
    private function initTransportOrderSentDate(): void
    {

        $transportOrder = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Outbound)?->first();

        if (! $transportOrder || $transportOrder->status->value < TransportOrderStatus::Issued->value || $transportOrder->statuses->isEmpty()) {
            $this->transportOrderSentDate = null;

            return;
        }

        $this->transportOrderSentDate = $transportOrder->statuses->where('status', TransportOrderStatus::Issued->value)?->last()?->created_at?->format(self::SUMMARY_DATE_FORMAT);
    }
}
