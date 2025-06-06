<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithFilesInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CopyCompleteGoodQualityDocuments extends Step implements StepWithFilesInterface, StepWithRedFlagInterface
{
    public const NAME = 'Copy complete and GOOD quality documents';

    public const MODAL_COMPONENT_NAME = 'UploadFiles';

    public const HAS_QUICK_DATE_FINISH = false;

    private AllOriginalDocumentsReceived $allOriginalDocumentsReceived;

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
        return $this->getFinishedStep() !== null || $this->getAllOriginalDocumentsReceived()->isCompleted();
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
            description: 'If the Vehicle was received longer than two days ago (< Today -2) AND no complete copy has been uploaded',
            isTriggered: $vehicleReceived->transportOrderDeliveredDateCarbonObject?->isBefore(Carbon::today()->subDays(2)) && ! $this->isCompleted()
        );
    }

    /**
     *Init the All Original Documents Received.
     *
     * @return void
     */
    public function initAllOriginalDocumentsReceived(): void
    {
        $this->allOriginalDocumentsReceived = new AllOriginalDocumentsReceived($this->getModelsWorkflow());
    }

    /**
     * Get the value of copyCompleteGoodQualityDocuments.
     *
     * @return AllOriginalDocumentsReceived
     */
    public function getAllOriginalDocumentsReceived(): AllOriginalDocumentsReceived
    {
        if (! isset($this->allOriginalDocumentsReceived)) {
            $this->initAllOriginalDocumentsReceived();
        }

        return $this->allOriginalDocumentsReceived;
    }
}
