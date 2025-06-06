<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepCanBeDisabledInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Step;

class SentOriginalDocuments extends Step implements StepWithDateInputInterface, StepCanBeDisabledInterface
{
    public const NAME = 'Sent Original Documents';

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

    protected function getSummary(): ?string
    {
        return $this->getFinishedStep()?->finished_at->format(self::SUMMARY_DATE_FORMAT);
    }

    public function getIsDisabled(): bool
    {
        $sendCodesOrDocuments = new SendCodesOrDocuments($this->getModelsWorkflow());

        return ! $sendCodesOrDocuments->isCompleted() || $sendCodesOrDocuments->getSummary() == 'Codes';
    }
}
