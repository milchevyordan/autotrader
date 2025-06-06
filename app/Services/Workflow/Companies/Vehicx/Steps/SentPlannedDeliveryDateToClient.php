<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\EmailStepInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;

class SentPlannedDeliveryDateToClient extends Step implements StepWithDateInputInterface, EmailStepInterface
{
    public const NAME = 'Sent Planned Delivery Date To Client';

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
        return 'Planned transport date';
    }

    /**
     * @return string
     */
    public function getEmailTemplateText(): string
    {
        return 'We`ve planned date for the transport at:';
    }
}
