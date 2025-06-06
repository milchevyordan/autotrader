<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportOrderStatus;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Illuminate\Support\Carbon;
use Throwable;

class VehiclePaymentBank extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface
{
    public const NAME = 'Vehicle Payment in Bank';

    public const MODAL_COMPONENT_NAME = 'Date';

    public const HAS_QUICK_DATE_FINISH = true;

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
        return $this->getFinishedStep() !== null;
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
        $yesterday = Carbon::today()->subDays(1);

        $transportOrder = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('status', '>', TransportOrderStatus::Concept->value)?->first();

        $issuedStatusesCreatedBeforeYesterday = $transportOrder ? $transportOrder?->statuses?->filter(function ($status) use ($yesterday) {
            $carbonCreatedAt = Carbon::parse($status->created_at);

            return $carbonCreatedAt->isBefore($yesterday);
        }) : null;

        $redFlagTriggered = $issuedStatusesCreatedBeforeYesterday && ! $this->isCompleted();

        return new RedFlag(
            name: self::NAME,
            description: 'Transport Order is Sent longer than a day ago (< TODAY -1) AND  Vehicle payment in bank is not checked',
            isTriggered: $redFlagTriggered
        );
    }
}
