<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\DocumentStatus;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;

class ReceivedVehicleInvoicePayment extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Received Vehicle Invoice Payment';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $invoicePaidDate = null;

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
        if (! $this->invoicePaidDate) {
            $this->initPaidDate();
        }

        return (bool) $this->invoicePaidDate;
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
        if (! $this->invoicePaidDate) {
            $this->initPaidDate();
        }

        return $this->invoicePaidDate;
    }

    public function getRedFlag(): RedFlag
    {
        $vehicleInvoiceSentToClient = (new VehicleInvoiceSentToClient($this->getModelsWorkflow()));

        return new RedFlag(
            name: self::NAME,
            description: 'If Vehicle Invoice is not paid within 5 days of sending Vehicle invoice (< TODAY -4)',
            isTriggered: ! $this->isCompleted && $vehicleInvoiceSentToClient->invoiceSentToCustomerDateCarbonObject?->lessThan(today()->subDays(4)),
        );
    }

    /**
     * Initialize Invoice Paid date.
     *
     * @return void
     */
    private function initPaidDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])) {
            return;
        }

        $invoice = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])?->first();

        if (! $invoice) {
            return;
        }

        $statuses = $invoice->statuses->where('status', DocumentStatus::Paid->value);

        if ($statuses->isEmpty() || $invoice->status->value < DocumentStatus::Paid->value) {
            return;
        }

        $this->invoicePaidDate = $statuses->last()->created_at->format(self::SUMMARY_DATE_FORMAT);
    }
}
