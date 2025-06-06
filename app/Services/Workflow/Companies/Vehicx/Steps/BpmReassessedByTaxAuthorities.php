<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithComponentDataInterface;
use App\Services\Workflow\Components\Steps\Step;

class BpmReassessedByTaxAuthorities extends Step implements StepWithComponentDataInterface
{
    public const NAME = 'Bpm Reassessed By Tax Authorities';

    public const MODAL_COMPONENT_NAME = 'YesOrNoResponse';

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
        return (bool) $this->getFinishedStep()?->additional_value;
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
        return (string) $this->getFinishedStep()?->additional_value;
    }

    /**
     * @return mixed
     */
    public function getComponentData(): mixed
    {
        return $this->getFinishedStep()?->additional_value;
    }
}
