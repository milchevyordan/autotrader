<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\EmailStepInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class InformedSupplierAboutPickupDate extends Step implements EmailStepInterface, StepWithRedFlagInterface
{
    public const NAME = 'Informed Supplier about Pickup date';

    public const MODAL_COMPONENT_NAME = 'SendEmail';

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
        return $this->getFinishedStep()?->finished_at?->format(self::SUMMARY_DATE_FORMAT);
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
        return 'Pickup date';
    }

    /**
     * @return string
     */
    public function getEmailTemplateText(): string
    {
        return 'The pickup date for the transport order is:';
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $expectedPickupDate = new ExpectedPickupDate($this->getModelsWorkflow());

        return new RedFlag(
            name: self::NAME,
            description: 'Estimated Pick up date is filled in longer than two days ago (< TODAY -2) AND Estimated Pick up date is in the future (> TODAY) AND Supplier Informed not checked yet',
            isTriggered: ! $this->isCompleted() && $expectedPickupDate->getDateFinished()?->created_at?->isBefore(Carbon::today()->subDays(2)) && $expectedPickupDate->getDateFinished()->isAfter(Carbon::today()),
        );
    }
}
