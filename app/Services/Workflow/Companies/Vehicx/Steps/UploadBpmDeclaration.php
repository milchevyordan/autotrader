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

class UploadBpmDeclaration extends Step implements StepWithFilesInterface, StepWithRedFlagInterface
{
    public const NAME = 'Upload BPM Declaration';

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
        $valuationListTableTaxation = new ValuationListTableTaxation($this->getModelsWorkflow());

        return new RedFlag(
            name: self::NAME,
            description: 'no upload of the declaration has been done within same day of choise for Valuation list or Depreciation table (< TODAY)',
            isTriggered: ! $this->isCompleted() && $valuationListTableTaxation->getDateFinished()?->isBefore(Carbon::today()),
        );
    }
}
