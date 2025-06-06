<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithFilesInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Illuminate\Database\Eloquent\Collection;

class ReceivedOriginalVehicleInvoice extends Step implements StepWithFilesInterface, StepWithRedFlagInterface
{
    public const NAME = 'Received Original Vehicle Invoice';

    public const MODAL_COMPONENT_NAME = 'UploadFiles';

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
        return $this->getFiles()?->first()?->created_at->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * @return Collection
     */
    public function getFiles(): Collection
    {
        return $this->getFinishedStep()?->files ?? new Collection();
    }

    public function getRedFlag(): RedFlag
    {
        $vehicleReceivedDate = (new VehicleReceived($this->getModelsWorkflow()))->transportOrderDeliveredDateCarbonObject;

        return new RedFlag(
            name: 'Received Original Vehicle Invoice',
            description: 'The vehicle was received but the ORIGINAL invoice is not uploaded within 14 days (<TODAY -14)',
            isTriggered: ! $this->isCompleted && $vehicleReceivedDate?->lessThan(today()->subDays(14)),
        );
    }
}
