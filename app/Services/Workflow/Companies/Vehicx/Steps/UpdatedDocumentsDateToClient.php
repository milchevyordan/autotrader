<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\EmailStepInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithComponentDataInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithWeekInputInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use App\Support\JsonHelper;
use Carbon\Carbon;

class UpdatedDocumentsDateToClient extends Step implements StepWithComponentDataInterface, EmailStepInterface, StepWithWeekInputInterface
{
    public const NAME = 'Updated Documents Date To Client';

    public const MODAL_COMPONENT_NAME = 'WeekInput';

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

    /**
     * @return null|string
     */
    protected function getSummary(): ?string
    {
        $finishedStep = $this->getFinishedStep();

        if (! $finishedStep) {
            return null;
        }

        return Carbon::parse(JsonHelper::convertJsonToArray($finishedStep->additional_value)['to'][1])->format(self::SUMMARY_DATE_FORMAT);
    }

    public function getComponentData(): mixed
    {
        return JsonHelper::convertJsonToArray($this->getFinishedStep()?->additional_value);
    }

    /**
     * @return null|string
     */
    public function getEmailRecipient(): ?string
    {
        return $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'supplier'])?->email ?? $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'supplierCompany'])?->email;
    }

    /**
     * @return string
     */
    public function getEmailSubject(): string
    {
        return 'Estimated delivery';
    }

    /**
     * @return string
     */
    public function getEmailTemplateText(): string
    {
        return 'Hello client, The estimated delivery date is ';
    }
}
