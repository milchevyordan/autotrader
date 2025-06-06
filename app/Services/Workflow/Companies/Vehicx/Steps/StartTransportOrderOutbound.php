<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportableType;
use App\Enums\TransportType;
use App\Models\TransportOrder;
use App\Models\Vehicle;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithUrlInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;

class StartTransportOrderOutbound extends Step implements StepWithUrlInterface
{
    public const NAME = 'Start Transport Order Outbound';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

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
        return (bool) $this->getTransportOrderOutbound()?->created_at?->format(self::SUMMARY_DATE_FORMAT);
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

        return $this->getTransportOrderOutbound()?->created_at?->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {

        $transportOrderOutbound = $this->getTransportOrderOutbound();

        if ($transportOrderOutbound) {
            return route('transport-orders.edit', $transportOrderOutbound->id);
        }

        $vehicleable = $this->getSelfProp(['modelsWorkflow', 'vehicleable']);
        $transportableType = $vehicleable instanceof Vehicle ? TransportableType::Vehicles->value : TransportableType::Service_vehicles->value;

        return route(
            'transport-orders.create',
            [
                'transport_type' => TransportType::Outbound->value,
                'vehicle_type'   => $transportableType,
                'filter'         => [
                    'id' => $vehicleable->id,
                ],
            ]
        );
    }

    /**
     * Initialize invoice created date.
     *
     * @return void
     */
    private function initTransportOrderOutbound(): void
    {
        $this->transportOrderOutbound = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Outbound)?->first();
    }

    public function getTransportOrderOutbound()
    {

        if (! isset($this->transportOrderOutbound)) {
            $this->initTransportOrderOutbound();
        }

        return $this->transportOrderOutbound;
    }
}
