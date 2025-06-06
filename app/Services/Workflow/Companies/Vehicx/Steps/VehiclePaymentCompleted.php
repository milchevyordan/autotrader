<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportOrderStatus;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Illuminate\Support\Carbon;
use Throwable;

class VehiclePaymentCompleted extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface
{
    public const NAME = 'Vehicle Payment Completed';

    public const MODAL_COMPONENT_NAME = 'Date';

    public const HAS_QUICK_DATE_FINISH = true;

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
        return $this->getFinishedStep() !== null;
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return self::HAS_QUICK_DATE_FINISH;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $yesterday = Carbon::today()->subDays(1);
        $vehiclePaymentBank = new VehiclePaymentBank($this->getModelsWorkflow());

        $transportOrder = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('status', '>', TransportOrderStatus::Concept->value)?->first();

        $redFlagTriggered = $transportOrder && $vehiclePaymentBank->getDateFinished()?->isBefore($yesterday) && ! $this->isCompleted();

        return new RedFlag(
            name: self::NAME,
            description: 'Transport Order is Sent AND Vehicle payment is in bank checked longer than one day ago (< TODAY -1) , but Payment not Completed yet',
            isTriggered: $redFlagTriggered
        );
    }
}
