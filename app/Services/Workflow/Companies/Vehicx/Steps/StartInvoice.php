<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\DocumentableType;
use App\Models\Vehicle;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithUrlInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;

class StartInvoice extends Step implements StepWithRedFlagInterface, StepWithUrlInterface
{
    public const NAME = 'Start Invoice';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $invoiceCreatedDate = null;

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
        if (! $this->invoiceCreatedDate) {
            $this->initInvoiceCreatedDate();
        }

        return (bool) $this->invoiceCreatedDate;
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
        if (! $this->invoiceCreatedDate) {
            $this->initInvoiceCreatedDate();
        }

        return $this->invoiceCreatedDate;
    }

    /**
     * Initialize invoice created date.
     *
     * @return void
     */
    private function initInvoiceCreatedDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])) {
            return;
        }

        $this->invoiceCreatedDate = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])?->first()?->created_at?->format(self::SUMMARY_DATE_FORMAT);
    }

    public function getRedFlag(): RedFlag
    {
        $vehicleMayBeInvoiced = (new VehicleMayBeInvoiced($this->getModelsWorkflow()));

        return new RedFlag(
            name: 'Start Invoice',
            description: 'If start Invoice is not done within 1 day of "Vehicle may be invoiced date" (< TODAY -1)',
            isTriggered: ! $this->isCompleted && $vehicleMayBeInvoiced->finishedStep?->finished_at->lessThan(today()->subDay()),
        );
    }

    public function getUrl(): string
    {

        $vehicleable = $this->getSelfProp(['modelsWorkflow', 'vehicleable']);
        $document = $vehicleable->documents->first();

        if ($document) {
            return route('documents.edit', $document->id);
        }

        $documentableType = $vehicleable instanceof Vehicle ? DocumentableType::Vehicle->value : DocumentableType::Service_vehicle->value;

        return route(
            'documents.create',
            [
                'documentable_type' => $documentableType,
                'filter'            => [
                    'id' => $vehicleable->id,
                ],
            ]
        );
    }
}
