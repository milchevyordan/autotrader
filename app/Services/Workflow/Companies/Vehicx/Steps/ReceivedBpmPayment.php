<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class ReceivedBpmPayment extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Received BPM Payment';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private SentBpmInvoice $sentBpmInvoice;

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
        return (bool) $this->getSentBpmInvoice()->getBpmInvoice()?->paid_at;
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
        return $this->getSentBpmInvoice()->getBpmInvoice()?->paid_at?->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $bpmInvoice = $this->getSentBpmInvoice()->getBpmInvoice();

        return new RedFlag(
            name: self::NAME,
            description: 'If BPM Invoice is not paid within 5 days of sending BPM invoice (< TODAY -4)',
            isTriggered: $bpmInvoice && ! $bpmInvoice->paid_at && $bpmInvoice->date?->isBefore(Carbon::today()->subDays(4)),
        );
    }

    /**
     * Get the value of sentBpmInvoice.
     *
     * @return SentBpmInvoice
     */
    public function getSentBpmInvoice(): SentBpmInvoice
    {
        if (! isset($this->sentBpmInvoice)) {
            $this->initSentBpmInvoice();
        }

        return $this->sentBpmInvoice;
    }

    private function initSentBpmInvoice(): void
    {
        $this->sentBpmInvoice = new SentBpmInvoice($this->getModelsWorkflow());
    }
}
