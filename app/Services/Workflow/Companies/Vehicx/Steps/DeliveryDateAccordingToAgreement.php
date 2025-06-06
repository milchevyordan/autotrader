<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class DeliveryDateAccordingToAgreement extends Step implements StepWithRedFlagInterface
{
    public const NAME = 'Delivery Date According To Agreement';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    private ?string $salesOrderDeliveryDate = null;

    private ?Carbon $salesOrderDeliveryDateCarbonObject = null;

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
        if (! $this->salesOrderDeliveryDate) {
            $this->initSalesOrderDeliveryDate();
        }

        return (bool) $this->salesOrderDeliveryDate;
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
        if (! $this->salesOrderDeliveryDate) {
            $this->initSalesOrderDeliveryDate();
        }

        return $this->salesOrderDeliveryDate;
    }

    /**
     * Initialize Invoice Paid date.
     *
     * @return void
     */
    private function initSalesOrderDeliveryDate(): void
    {
        if (! $this->hasFilledSelfProp(['modelsWorkflow', 'vehicleable', 'salesOrder'])) {
            return;
        }

        $deliveryWeek = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'salesOrder'])?->first()?->delivery_week;

        if (! $deliveryWeek) {
            return;
        }

        if (! $deliveryWeek['to']) {
            return;
        }

        $this->salesOrderDeliveryDateCarbonObject = Carbon::parse($deliveryWeek['to'][0]);

        $this->salesOrderDeliveryDate = $this->salesOrderDeliveryDateCarbonObject->format(self::SUMMARY_DATE_FORMAT);
    }

    public function getRedFlag(): RedFlag
    {
        $vehicleReceived = (new VehicleReceived($this->getModelsWorkflow()))->isCompleted;

        return new RedFlag(
            name: 'Delivery Date According To Agreement',
            description: 'If the date according to agreement has past but delivery has not been done yet and no update was sent',
            isTriggered: $this->salesOrderDeliveryDateCarbonObject?->isPast() && ! $vehicleReceived
        );
    }
}
