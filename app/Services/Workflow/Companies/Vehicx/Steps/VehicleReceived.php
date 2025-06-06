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

class VehicleReceived extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Vehicle Received';

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
            $this->initTransportOrderDeliveredDate();
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
            $this->initTransportOrderDeliveredDate();
        }

        return $this->transportOrderDeliveredDate;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        return new RedFlag(
            name: self::NAME,
            description: 'Vehicle was payed longer than six days ago AND has not yet arrived (< TODAY -6)',
            isTriggered: false
        );
    }

    /**
     * Initialize transport order sent date.
     *
     * @return void
     */
    private function initTransportOrderDeliveredDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])) {
            return;
        }

        $transportOrder = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Inbound)?->first();

        if (! $transportOrder || $transportOrder->status->value < TransportOrderStatus::Delivered->value || $transportOrder->statuses->isEmpty()) {
            return;
        }

        $this->transportOrderDeliveredDateCarbonObject = $transportOrder->statuses->where('status', TransportOrderStatus::Delivered->value)->last()?->created_at;

        $this->transportOrderDeliveredDate = $this->transportOrderDeliveredDateCarbonObject->format(self::SUMMARY_DATE_FORMAT);
    }
}
