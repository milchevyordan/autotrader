<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\DocumentStatus;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Step;

class CreateVehicleInvoice extends Step
{
    public const NAME = 'Create Vehicle Invoice';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $invoiceInvoiceDate = null;

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
        if (! $this->invoiceInvoiceDate) {
            $this->initInvoiceDate();
        }

        return (bool) $this->invoiceInvoiceDate;
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
        if (! $this->invoiceInvoiceDate) {
            $this->initInvoiceDate();
        }

        return $this->invoiceInvoiceDate;
    }

    /**
     * Initialize Invoice date.
     *
     * @return void
     */
    private function initInvoiceDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])) {
            return;
        }

        $invoice = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])?->first();
        $statuses = $invoice?->statuses?->where('status', DocumentStatus::Create_invoice->value);

        if (! $invoice || $statuses->isEmpty() || $invoice->status->value < DocumentStatus::Create_invoice->value) {
            return;
        }

        $this->invoiceInvoiceDate = $statuses->last()->created_at->format(self::SUMMARY_DATE_FORMAT);
    }
}
