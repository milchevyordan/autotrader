<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class RandomInspectionPlanned extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface
{
    public const NAME = 'Random Inspection Planned';

    public const MODAL_COMPONENT_NAME = 'Date';

    private RdwRandomInspection $rdwRandomInspection;

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
        return 'Yes' == $this->getRdwRandomInspection()->getFinishedStep()?->additional_value ? self::MODAL_COMPONENT_NAME : null;
    }

    /**
     * @return bool
     */
    protected function isCompleted(): bool
    {
        return $this->getFinishedStep() !== null || $this->getRdwRandomInspection()->getFinishedStep()?->additional_value === 'No';
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return 'Yes' == $this->getRdwRandomInspection()->getFinishedStep()?->additional_value;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $rdwRandomInspection = new RdwRandomInspection($this->getModelsWorkflow());
        $rdwRandomInspectionPlannedDateSummary = $this->getSummary();
        $rdwRandomInspectionPlannedDate = $rdwRandomInspectionPlannedDateSummary ? Carbon::createFromFormat(self::SUMMARY_DATE_FORMAT, $rdwRandomInspectionPlannedDateSummary) : null;

        return new RedFlag(
            name: self::NAME,
            description: 'If damage repair is necessary, but not planned within 2 days (<TODAY -1)',
            isTriggered: 'Yes' == $rdwRandomInspection->getSummary() && ! $this->isCompleted() && $rdwRandomInspectionPlannedDate && Carbon::today()->subDays(2)->isBefore($rdwRandomInspectionPlannedDate),
        );
    }

    /**
     *Init the copyCompleteGoodQualityDocuments.
     *
     * @return void
     */
    public function initRdwRandomInspection(): void
    {
        $this->rdwRandomInspection = new RdwRandomInspection($this->getModelsWorkflow());
    }

    /**
     * Get the value of copyCompleteGoodQualityDocuments.
     *
     * @return CopyCompleteGoodQualityDocuments
     */
    public function getRdwRandomInspection(): RdwRandomInspection
    {
        if (! isset($this->rdwRandomInspection)) {
            $this->initRdwRandomInspection();
        }

        return $this->rdwRandomInspection;
    }
}
