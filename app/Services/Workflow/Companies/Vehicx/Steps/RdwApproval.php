<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class RdwApproval extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface
{
    public const NAME = 'RDW Approval';

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
        $modelsWorkflow = $this->getModelsWorkflow();
        $rdwIdentification = new RdwIdentification($modelsWorkflow);
        $rdwRandomInspection = new RdwRandomInspection($modelsWorkflow);
        $randomInspectionDate = new RandomInspectionDate($modelsWorkflow);

        return new RedFlag(
            name: self::NAME,
            description: 'Approval is not completed AND RDW Identification (VIA) was made more than 4 days ago (< TODAY -4). If Sample inspection YES, this is red after two days of Sample inspection date (< TODAY -2)',
            isTriggered: ! $this->isCompleted() && $rdwIdentification->getDateFinished()?->isBefore(Carbon::today()->subDays(4)) || 'Yes' == $rdwRandomInspection->getSummary() && $randomInspectionDate->getDateFinished()?->isBefore(Carbon::today()->subDays(2))
        );
    }
}
