<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\MultiSelectService;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithComponentDataInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use App\Services\Workflow\RedFlags\RedFlag;
use Illuminate\Database\Eloquent\Collection;

class PickupLocationAddress extends Step implements StepWithComponentDataInterface, StepWithRedFlagInterface
{
    public const NAME = 'Pick Up Location/ Address';

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

    public function getComponentData(): mixed
    {
        $componentData = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'supplierCompany', 'addresses']) ?? new Collection();

        return MultiSelectService::dataForSelectFromArray(dataArray: $componentData->toArray(), textColumnName: 'address');
    }

    public function getRedFlag(): RedFlag
    {
        $agreedDateReadyWithSupplier = new AgreedDateReadyWithSupplier($this->getModelsWorkflow());

        $refFlagTriggered = $agreedDateReadyWithSupplier?->isCompleted && ! $this->isCompleted();

        return new RedFlag(
            name: self::NAME,
            description: 'Pick up location/address is not confirmed or filled AND Agreed date is set',
            isTriggered: $refFlagTriggered
        );
    }
}
