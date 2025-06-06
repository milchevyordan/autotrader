<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Step;

class PassedRandomInspection extends Step implements StepWithDateInputInterface
{
    public const NAME = 'Passed Random Inspection';

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
