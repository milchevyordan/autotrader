<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportType;
use App\Models\TransportOrder;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;

class PlannedDeliveryDate extends Step
{
    public const NAME = 'Planned Delivery Date';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    /**
     * @var null|TransportOrder
     */
    private ?TransportOrder $transportOrderOutbound;

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
        return (bool) $this->getTransportOrderOutbound()?->planned_delivery_date;
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
        return $this->getTransportOrderOutbound()?->planned_delivery_date?->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * Get the value of transportOrderOutbound.
     *
     * @return null|TransportOrder
     */
    public function getTransportOrderOutbound(): ?TransportOrder
    {
        if (! isset($this->transportOrderOutbound)) {
            $this->initTransportOrderOutbound();
        }

        return $this->transportOrderOutbound;
    }

    /**
     * Initialize transport order sent date.
     *
     * @return void
     */
    private function initTransportOrderOutbound(): void
    {
        $this->transportOrderOutbound = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Outbound)?->first();
    }
}
