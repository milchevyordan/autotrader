<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\DocumentStatus;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class ApproveProformaVehicleInvoice extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Approve (Proforma) Vehicle Invoice';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $invoiceApprovedDate = null;

    public ?Carbon $invoiceApprovedDateCarbonObject = null;

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
        if (! $this->invoiceApprovedDate) {
            $this->initApproveDate();
        }

        return (bool) $this->invoiceApprovedDate;
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
        if (! $this->invoiceApprovedDate) {
            $this->initApproveDate();
        }

        return $this->invoiceApprovedDate;
    }

    /**
     * Initialize Invoice Approve date.
     *
     * @return void
     */
    private function initApproveDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])) {
            return;
        }

        $invoice = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])?->first();
        $statuses = $invoice?->statuses?->where('status', DocumentStatus::Approved->value);

        if ($statuses?->isEmpty() || ! $invoice?->status?->value || $invoice?->status?->value < DocumentStatus::Approved->value) {
            return;
        }

        $this->invoiceApprovedDateCarbonObject = $statuses->last()?->created_at;
        $this->invoiceApprovedDate = $this->invoiceApprovedDateCarbonObject->format(self::SUMMARY_DATE_FORMAT);
    }

    public function getRedFlag(): RedFlag
    {
        $createProformaInvoice = (new CreateProformaVehicleInvoice($this->getModelsWorkflow()));

        return new RedFlag(
            name: 'Approve (Proforma) Vehicle Invoice',
            description: 'If Approve proforma is not done within same day of Create proforma vehicle invoice (< TODAY)',
            isTriggered: ! $this->isCompleted && $createProformaInvoice->invoiceProFormaDateCarbon?->lessThan(today()),
        );
    }
}
