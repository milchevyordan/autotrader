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

class AgreedDateReadyWithSupplier extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface
{
    public const NAME = 'Agreed date Ready with Supplier';

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
        $modelsWorkflow = $this->getModelsWorkflow();
        $latestExpectDateReady = new LatestUpdateExpectDateReady($modelsWorkflow);
        $latestExpectDateReadyDateFinished = $latestExpectDateReady->getDateFinished();
        $carbonLatestExpectDateReady = $latestExpectDateReadyDateFinished ? $latestExpectDateReady->getDateFinished() : null;
        $twoDaysFromNow = Carbon::today()->addDays(2);

        $transportOrder = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders'])?->where('status', '<', TransportOrderStatus::Issued->value)?->first();

        $redFlagTriggered = $carbonLatestExpectDateReady && $carbonLatestExpectDateReady->lessThan($twoDaysFromNow) && ! ($this->isCompleted() || $transportOrder);

        return new RedFlag(
            name: self::NAME,
            description: 'Latest expected date ready is less than two days in the future  (< TODAY +2) AND no Agreed date ready OR Transport Order is sent',
            isTriggered: $redFlagTriggered
        );
    }
}
