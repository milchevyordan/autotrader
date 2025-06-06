<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportType;
use App\Models\TransportOrder;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithUrlInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class StickerSheetPrinted extends Step implements StepWithRedFlagInterface, StepWithUrlInterface
{
    public const NAME = 'Sticker Sheet Printed';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

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

        return (bool) $this->getTransportOrder()?->files?->where('section', 'generatedStickervelPdf')?->isNotEmpty();
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return self::HAS_QUICK_DATE_FINISH;
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $sentTransportOrderInboundSummary = (new SentTransportOrderInbound($this->getModelsWorkflow()))->getTransportOrderSentDateCarbon();
        $sentTransportOrderInboundDate = $sentTransportOrderInboundSummary ? Carbon::parse($sentTransportOrderInboundSummary) : null;

        return new RedFlag(
            name: self::NAME,
            description: 'Transport Order is Sent but sheet is not printed within 4 days  (<TODAY -4), this can also be done in the TO',
            isTriggered: ! $this->isCompleted() && $sentTransportOrderInboundDate && $sentTransportOrderInboundDate->isBefore(Carbon::today()->subDays(4)),
        );
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
