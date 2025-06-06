<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\DocumentStatus;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Step;

class VehicleAllowedToBeDelivered extends Step implements StepWithDateInputInterface
{
    public const NAME = 'Vehicle Allowed To Be Delivered';

    public const MODAL_COMPONENT_NAME = 'Date';

    public const HAS_QUICK_DATE_FINISH = true;

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

        return (bool) $this->invoicePaidDate || $this->getFinishedStep() !== null;
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

        return $this->invoicePaidDate ?? $this->getFinishedStep()?->finished_at?->format(self::SUMMARY_DATE_FORMAT);
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

        $statuses = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'documents'])?->first()?->statuses?->where('status', DocumentStatus::Paid->value);

        if (! $statuses || $statuses->isEmpty()) {
            return;
        }

        $this->invoicePaidDate = $statuses->last()->created_at->format(self::SUMMARY_DATE_FORMAT);
    }
}
