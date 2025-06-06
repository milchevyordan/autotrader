<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepCanBeDisabledInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;

class SentNlRegistrationCodes extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface, StepCanBeDisabledInterface
{
    public const NAME = 'Sent Nl Registration Codes';

    public const MODAL_COMPONENT_NAME = 'Date';

    public const HAS_QUICK_DATE_FINISH = true;

    private SendCodesOrDocuments $sendCodesOrDocuments;

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
        return $this->getIsDisabled() ? null : self::MODAL_COMPONENT_NAME;
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

    public function getRedFlag(): RedFlag
    {
        $sendCodesOrDocuments = new SendCodesOrDocuments($this->getModelsWorkflow());

        return new RedFlag(
            name: self::NAME,
            description: 'Codes is chosen (or taken from the workflow because NL registration) AND the codes are present AND the vehicle is paid by client',
            isTriggered: 'Codes' == $sendCodesOrDocuments->getSummary() && ! $this->isCompleted()
        );
    }

    /**
     * Get the value of sendCodesOrDocuments.
     *
     * @return SendCodesOrDocuments
     */
    public function getSendCodesOrDocuments(): SendCodesOrDocuments
    {
        if (! isset($this->sendCodesOrDocuments)) {
            $this->initSendCodesOrDocuments();
        }

        return $this->sendCodesOrDocuments;
    }

    /**
     * Init the SendCodesOrDocuments.
     *
     * @return void
     */
    private function initSendCodesOrDocuments()
    {
        $this->sendCodesOrDocuments = new SendCodesOrDocuments($this->getModelsWorkflow());
    }

    public function getIsDisabled(): bool
    {
        $sendCodesOrDocuments = $this->getSendCodesOrDocuments();

        return ! $sendCodesOrDocuments->isCompleted() || $this->getSendCodesOrDocuments()->getSummary() == 'Documents';

    }
}
