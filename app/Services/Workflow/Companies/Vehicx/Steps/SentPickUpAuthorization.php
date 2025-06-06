<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportType;
use App\Models\TransportOrder;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithUrlInterface;
use App\Services\Workflow\Components\Steps\Step;

class SentPickUpAuthorization extends Step implements StepWithUrlInterface
{
    public const NAME = 'Sent Pick up Authorization';

    public const MODAL_COMPONENT_NAME = 'Date';

    public const HAS_QUICK_DATE_FINISH = true;

    /**
     * The step transport Order if exists
     *
     * @var TransportOrder|null
     */
    private ?TransportOrder $transportOrder;

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

        return (bool) $this->getTransportOrder()?->files?->where('section', 'generatedPickupAuthorizationPdf')?->isNotEmpty();
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return self::HAS_QUICK_DATE_FINISH;
    }

    /**
     * @return null|string
     */
    protected function getSummary(): ?string
    {

        return $this->getFinishedStep()?->finished_at->format(self::SUMMARY_DATE_FORMAT) ?? $this->getTransportOrder()
            ?->files
            ->where('section', 'generatedPickupAuthorizationPdf')
            ->first()?->created_at->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {

        $transportOrder = $this->getTransportOrder();

        return $transportOrder ? route('transport-orders.edit', $transportOrder) : null;
    }

    /**
     * Initialize transport order sent date.
     *
     * @return void
     */
    private function initTransportOrder(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])) {
            $this->transportOrder = null;

            return;
        }

        $transportOrder = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('transport_type', TransportType::Inbound)?->first();

        $this->transportOrder = $transportOrder;

    }

    /**
     * @return TransportOrder|null
     */
    public function getTransportOrder(): ?TransportOrder
    {
        if (! isset($this->transportOrder)) {
            $this->initTransportOrder();
        }

        return $this->transportOrder;
    }
}
