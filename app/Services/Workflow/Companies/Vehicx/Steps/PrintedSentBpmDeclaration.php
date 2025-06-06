<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class PrintedSentBpmDeclaration extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface
{
    public const NAME = 'Printed and sent BPM Declaration';

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
        $uploadBpmDeclaration = new UploadBpmDeclaration($this->getModelsWorkflow());

        return new RedFlag(
            name: self::NAME,
            description: 'Printed and sent is not completed within the same day as the uploading of the declaration or the taxation report (< TODAY)',
            isTriggered: ! $this->isCompleted() && $uploadBpmDeclaration->getDateFinished()?->isBefore(Carbon::today()),
        );
    }
}
