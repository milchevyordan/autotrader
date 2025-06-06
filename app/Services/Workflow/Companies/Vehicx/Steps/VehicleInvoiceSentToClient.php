<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\DocumentStatus;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class VehicleInvoiceSentToClient extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Vehicle Invoice Sent To Client';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $invoiceSentToCustomerDate = null;

    public ?Carbon $invoiceSentToCustomerDateCarbonObject = null;

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
        if (! $this->invoiceSentToCustomerDate) {
            $this->initSentToCustomerDate();
        }

        return (bool) $this->invoiceSentToCustomerDate;
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
        if (! $this->invoiceSentToCustomerDate) {
            $this->initSentToCustomerDate();
        }

        return $this->invoiceSentToCustomerDate;
    }

    /**
     * Initialize Invoice Sent To Customer date.
     *
     * @return void
     */
    private function initSentToCustomerDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])) {
            return;
        }

        $invoice = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])?->first();

        if (! $invoice) {
            return;
        }

        $statuses = $invoice->statuses->where('status', DocumentStatus::Sent_to_customer->value);

        if ($statuses->isEmpty() || $invoice->status->value < DocumentStatus::Sent_to_customer->value) {
            return;
        }

        $this->invoiceSentToCustomerDateCarbonObject = $statuses->last()?->created_at;
        $this->invoiceSentToCustomerDate = $this->invoiceSentToCustomerDateCarbonObject->format(self::SUMMARY_DATE_FORMAT);
    }

    public function getRedFlag(): RedFlag
    {
        $approveProformaInvoice = (new ApproveProformaVehicleInvoice($this->getModelsWorkflow()));

        return new RedFlag(
            name: 'Vehicle Invoice Sent To Client',
            description: 'If Vehicle invoice sent to client is not done within the same day as Approve proforma vehicle invoice (< TODAY)',
            isTriggered: ! $this->isCompleted && $approveProformaInvoice->invoiceApprovedDateCarbonObject?->lessThan(today()),
        );
    }
}
