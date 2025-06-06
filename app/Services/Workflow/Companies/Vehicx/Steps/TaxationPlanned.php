<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepCanBeDisabledInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Enums\BpmDeclaredOptions;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class TaxationPlanned extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface, StepCanBeDisabledInterface
{
    public const NAME = 'Taxation Planned';

    public const MODAL_COMPONENT_NAME = 'Date';

    public const HAS_QUICK_DATE_FINISH = true;

    private ValuationListTableTaxation $valuationListTableTaxation;

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
        $selectedOption = BpmDeclaredOptions::getCaseByValue($this->getValuationListTableTaxation()->getFinishedStep()?->additional_value, false)?->name;

        return 'Taxation' == $selectedOption ? self::MODAL_COMPONENT_NAME : null;
    }

    /**
     * @return bool
     */
    protected function isCompleted(): bool
    {
        $finishedStepValue = $this->getValuationListTableTaxation()->getFinishedStep()?->additional_value;

        return $this->getFinishedStep() !== null;
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        $selectedOption = BpmDeclaredOptions::getCaseByValue($this->getValuationListTableTaxation()->getFinishedStep()?->additional_value, false)?->name;

        return 'Taxation' == $selectedOption;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $valuationListTableTaxation = $this->getValuationListTableTaxation();

        return new RedFlag(
            name: self::NAME,
            description: 'If Vehicle Invoice is not paid within 5 days of sending Vehicle invoice (< TODAY -4)',
            isTriggered: ! $this->isCompleted() && $valuationListTableTaxation && 'Taxation' == $valuationListTableTaxation->getSummary() && $valuationListTableTaxation->getDateFinished()?->isBefore(Carbon::yesterday()),
        );
    }

    /**
     *Init the copyCompleteGoodQualityDocuments.
     *
     * @return void
     */
    public function initValuationListTableTaxation(): void
    {
        $this->valuationListTableTaxation = new ValuationListTableTaxation($this->getModelsWorkflow());
    }

    /**
     * Get the value of valuationListTableTaxation.
     *
     * @return ValuationListTableTaxation
     */
    public function getValuationListTableTaxation(): ValuationListTableTaxation
    {
        if (! isset($this->valuationListTableTaxation)) {
            $this->initValuationListTableTaxation();
        }

        return $this->valuationListTableTaxation;
    }

    /**
     * @return bool
     */
    public function getIsDisabled(): bool
    {

        return $this->getValuationListTableTaxation()->getSummary() == 'Valuation List';

    }
}
