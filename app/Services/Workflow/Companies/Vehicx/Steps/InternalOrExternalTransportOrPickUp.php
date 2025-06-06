<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\DeliveryTransportType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithComponentDataInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use App\Support\StringHelper;
use Carbon\Carbon;

class InternalOrExternalTransportOrPickUp extends Step implements StepWithComponentDataInterface, StepWithRedFlagInterface
{
    public const NAME = 'Internal Or External Transport Or Pick-up';

    public const MODAL_COMPONENT_NAME = 'ModalWithSelectInput';

    public const HAS_QUICK_DATE_FINISH = false;

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

    protected function getSummary(): ?string
    {
        $selectedOption = DeliveryTransportType::getCaseByValue($this->getFinishedStep()?->additional_value, false)?->name;
        $selectedOptionReplacedUnderscores = StringHelper::replaceUnderscores($selectedOption);

        return __($selectedOptionReplacedUnderscores);
    }

    public function getComponentData(): mixed
    {
        return DeliveryTransportType::toArray();
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $modelsWorkflow = $this->getModelsWorkflow();
        $vehicleReadyToBeDelivered = new VehicleReadyToBeDelivered($modelsWorkflow);
        $vehicleAllowedToBeDelivered = new VehicleAllowedToBeDelivered($modelsWorkflow);
        $startTransportOrderInbound = new StartTransportOrderInbound($modelsWorkflow);
        $carbonToday = Carbon::today();

        return new RedFlag(
            name: self::NAME,
            description: 'If the date according to update delivery has past but vehicle is not ready to be delivered',
            isTriggered: $vehicleReadyToBeDelivered->getDateFinished()?->isBefore($carbonToday) && $vehicleAllowedToBeDelivered->getDateFinished()?->isBefore($carbonToday) && $startTransportOrderInbound->getTransportOrderInbound()
        );
    }
}
