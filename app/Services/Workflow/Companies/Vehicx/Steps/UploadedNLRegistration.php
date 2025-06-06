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

class UploadedNLRegistration extends Step implements StepWithFilesInterface, StepWithRedFlagInterface
{
    public const NAME = 'Uploaded NL Registration (BVI)';

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
        $uploadBpmDeclaration = new UploadBpmDeclaration($this->getModelsWorkflow());
        $uploadBpmDeclarationSentDate = $uploadBpmDeclaration->getSummary() ? Carbon::createFromFormat(self::SUMMARY_DATE_FORMAT, $uploadBpmDeclaration->getSummary()) : null;

        return new RedFlag(
            name: self::NAME,
            description: 'If Vehicle Invoice is not paid within 5 days of sending Vehicle invoice (< TODAY -4)',
            isTriggered: ! $this->isCompleted() && $uploadBpmDeclarationSentDate && $uploadBpmDeclarationSentDate->isBefore(Carbon::today()->subDays(5)),
        );
    }
}
