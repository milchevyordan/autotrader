<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportableType;
use App\Enums\TransportType;
use App\Models\TransportOrder;
use App\Models\Vehicle;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithUrlInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;
use Throwable;

class StartTransportOrderInbound extends Step implements StepWithRedFlagInterface, StepWithUrlInterface
{
    public const NAME = 'Start Transport Order inbound';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    public ?TransportOrder $transportOrderInbound;

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
        return (bool) $this->getTransportOrderInbound()?->created_at?->format(self::SUMMARY_DATE_FORMAT);
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
        return $this->getTransportOrderInbound()?->created_at?->format(self::SUMMARY_DATE_FORMAT);
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
        $transportOrderInbound = $this->getTransportOrderInbound();

        $redFlagTriggered = $carbonAgreedDate && $carbonAgreedDate->isBefore($threeDaysAfterToday) && ! $transportOrderInbound;

        return new RedFlag(
            name: self::NAME,
            description: 'Agreed date ready with Supplier is less than three days in the future (< TODAY +3)  AND NO Transport Order inbound is Started for this car',
            isTriggered: $redFlagTriggered
        );
    }

    /**
     * Get the value of transportOrderInbound.
     *
     * @return null|TransportOrder
     */
    public function getTransportOrderInbound(): ?TransportOrder
    {
        if (! isset($this->transportOrderInbound)) {
            $this->initTransportOrderInbound();
        }

        return $this->transportOrderInbound;
    }

    /**
     * @return void
     */
    private function initTransportOrderInbound(): void
    {
        $transportOrders = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders']);
        $this->transportOrderInbound = $transportOrders?->where('transport_type', TransportType::Inbound)?->first();

    }

    public function getUrl(): string
    {
        $transportOrderInbound = $this->getTransportOrderInbound();

        if ($transportOrderInbound) {
            return route('transport-orders.edit', $transportOrderInbound->id);
        }

        $vehicleable = $this->getSelfProp(['modelsWorkflow', 'vehicleable']);
        $transportableType = $vehicleable instanceof Vehicle ? TransportableType::Vehicles->value : TransportableType::Service_vehicles->value;

        return route(
            'transport-orders.create',
            [
                'transport_type' => TransportType::Inbound->value,
                'vehicle_type'   => $transportableType,
                'filter'         => [
                    'id' => $vehicleable->id,
                ],
            ]
        );
    }
}
