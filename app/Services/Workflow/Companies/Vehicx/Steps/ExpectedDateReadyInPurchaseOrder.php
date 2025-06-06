<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Illuminate\Support\Carbon;

class ExpectedDateReadyInPurchaseOrder extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Expected date Ready in PO';

    public const MODAL_COMPONENT_NAME = null;

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
        return (bool) $this->getFinishedStep();
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
        $purchaseOrder = $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'purchaseOrder']);

        return ! $purchaseOrder ? ' | '.'There is no PO' : $this->getFinishedStep()?->finished_at->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $redFlagTriggered = false;

        $expectedDateReadyInPurchaseOrder = $this->getFinishedStep()?->finished_at;

        if ($expectedDateReadyInPurchaseOrder) {
            $expectedDate = Carbon::parse($expectedDateReadyInPurchaseOrder);
            $twoDaysFromNow = Carbon::today()->addDays(2);

            $agreedDateReadyWithSupplier = new AgreedDateReadyWithSupplier($this->getModelsWorkflow());
            $agreedDate = $agreedDateReadyWithSupplier->getFinishedStep()?->finished_at;

            if ($expectedDate->lessThan($twoDaysFromNow) && ! $agreedDate) {
                $redFlagTriggered = true;
            }

            if ($agreedDate) {
                $carbonAgreedDate = Carbon::parse($agreedDate);
                $oneWeekFromExpectedDate = $expectedDate->copy()->addWeek();

                if ($carbonAgreedDate->greaterThan($oneWeekFromExpectedDate)) {
                    $redFlagTriggered = true;
                }
            }
        }

        return new RedFlag(
            name: self::NAME,
            description: 'Expected date ready in PO is less than two days in the future (< TODAY +2) AND no Agreed date ready is set.
                      Must turn and stay red if Agreed date ready is later than one week after the expected date ready in PO.',
            isTriggered: $redFlagTriggered
        );
    }
}
