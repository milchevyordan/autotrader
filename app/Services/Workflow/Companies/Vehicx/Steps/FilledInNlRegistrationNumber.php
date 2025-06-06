<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class FilledInNlRegistrationNumber extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface
{
    public const NAME = 'Filled in NL Registration Number';

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
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $uploadBpmDeclaration = new UploadBpmDeclaration($this->getModelsWorkflow());
        $uploadBpmDeclarationSentDate = $uploadBpmDeclaration->getSummary() ? Carbon::createFromFormat(self::SUMMARY_DATE_FORMAT, $uploadBpmDeclaration->getSummary()) : null;

        return new RedFlag(
            name: self::NAME,
            description: 'If Vehicle Invoice is not paid within 5 days of sending Vehicle invoice (< TODAY -4)',
            isTriggered: ! $this->isCompleted() && $uploadBpmDeclarationSentDate && $uploadBpmDeclarationSentDate->isBefore(Carbon::yesterday()),
        );
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return self::HAS_QUICK_DATE_FINISH;
    }
}
