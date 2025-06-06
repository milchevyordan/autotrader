<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\TransportOrder;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use App\Services\Workflow\RedFlags\RedFlag;

class SentCmrToSupplier extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Sent CMR to Supplier';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    /**
     * @var null|TransportOrder
     */
    private ?TransportOrder $transportOrderInbound;

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
        $transportOrderInbound = $this->getTransportOrderInbound();

        return $transportOrderInbound && $transportOrderInbound->statuses->where('status', TransportOrderStatus::Cmr_waybill)->first();
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
        $transportOrderInbound = $this->getTransportOrderInbound();

        return $transportOrderInbound ? $transportOrderInbound->statuses->where('status', TransportOrderStatus::Cmr_waybill)?->first()?->created_at?->format(self::SUMMARY_DATE_FORMAT) : null;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $vehicleReceived = new VehicleReceived($this->getModelsWorkflow());

        $transportOrderInbound = $this->getTransportOrderInbound();

        return new RedFlag(
            name: self::NAME,
            description: 'Vehicle was Received longer than one day ago AND CMR not send (< TODAY -1)',
            isTriggered: $transportOrderInbound && $transportOrderInbound->statuses->where('status', TransportOrderStatus::Cmr_waybill)->first() && $vehicleReceived->transportOrderDeliveredDateCarbonObject?->isBefore(today()->subDays(1)),
        );
    }

    /**
     * @return null|TransportOrder
     */
    private function getTransportOrderInbound(): ?TransportOrder
    {
        if (! isset($this->transportOrderInbound)) {
            $this->initTransportOrderInbound();
        }

        return $this->transportOrderInbound;
    }

    private function initTransportOrderInbound(): void
    {

        $this->transportOrderInbound = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Inbound)?->first();

    }
}
