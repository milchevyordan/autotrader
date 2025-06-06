<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithFilesInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AllOriginalDocumentsReceived extends Step implements StepWithFilesInterface, StepWithRedFlagInterface
{
    public const NAME = 'All Original Documents Received';

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

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $vehicleReceived = new VehicleReceived($this->getModelsWorkflow());

        return new RedFlag(
            name: self::NAME,
            description: 'If the Vehicle was received longer than five days ago (< Today -5) AND no original documents have been uploaded',
            isTriggered: $vehicleReceived->transportOrderDeliveredDateCarbonObject?->isBefore(Carbon::today()->subDays(5)) && ! $this->isCompleted()
        );
    }
}
