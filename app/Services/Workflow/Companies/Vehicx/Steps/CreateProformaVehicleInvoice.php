<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\DocumentStatus;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class CreateProformaVehicleInvoice extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Create Proforma Vehicle Invoice';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $invoiceProFormaDate = null;

    public ?Carbon $invoiceProFormaDateCarbon = null;

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
        if (! $this->invoiceProFormaDate) {
            $this->initProformaDate();
        }

        return (bool) $this->invoiceProFormaDate;
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
        if (! $this->invoiceProFormaDate) {
            $this->initProformaDate();
        }

        return $this->invoiceProFormaDate;
    }

    /**
     * Initialize Invoice Pro forma date.
     *
     * @return void
     */
    private function initProformaDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])) {
            return;
        }

        $invoice = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])?->first();
        $statuses = $invoice?->statuses?->where('status', DocumentStatus::Pro_forma->value);

        if ($statuses?->isEmpty() || $invoice?->status->value < DocumentStatus::Pro_forma->value) {
            return;
        }

        $this->invoiceProFormaDateCarbon = $statuses->last()->created_at;
        $this->invoiceProFormaDate = $this->invoiceProFormaDateCarbon?->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $defineBpmForVehicleInvoice = new DefineBpmForVehicleInvoice($this->getModelsWorkflow());

        return new RedFlag(
            name: self::NAME,
            description: 'If  Create proforma vehicle invoice is not done within 1 day of Define BPM for vehicle invoice (< TODAY -1)',
            isTriggered: ! $this->isCompleted() && $defineBpmForVehicleInvoice->getDateFinished()?->isBefore(Carbon::today()),
        );
    }
}
