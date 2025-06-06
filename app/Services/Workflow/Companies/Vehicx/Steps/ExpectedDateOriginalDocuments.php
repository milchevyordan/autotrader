<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class ExpectedDateOriginalDocuments extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface
{
    public const NAME = 'Expected date Original Documents';

    public const MODAL_COMPONENT_NAME = 'Date';

    public const HAS_QUICK_DATE_FINISH = true;

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
        return $this->getAllOriginalDocumentsReceived()->isCompleted() ? null : self::MODAL_COMPONENT_NAME;
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

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $vehicleReceived = new VehicleReceived($this->getModelsWorkflow());

        return new RedFlag(
            name: self::NAME,
            description: 'if Vehicle is received AND Expected date was more than 2 days ago (< TODAY -2)',
            isTriggered: $this->isCompleted() && $this->getDateFinished()?->isBefore(Carbon::today()->subDays(2)) && ! $vehicleReceived->isCompleted()
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
