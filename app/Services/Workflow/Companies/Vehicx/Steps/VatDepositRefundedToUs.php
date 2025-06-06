<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;

class VatDepositRefundedToUs extends Step implements StepWithDateInputInterface, StepWithRedFlagInterface
{
    public const NAME = 'VAT Deposit Refunded To Us';

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

    public function getRedFlag(): RedFlag
    {
        $sentVatTransportDeclaration = new SentVatTransportDeclaration($this->getModelsWorkflow());
        $sentVatTransportDeclarationSummary = $sentVatTransportDeclaration->getSummary();

        return new RedFlag(
            name: self::NAME,
            description: 'VAT Declaration is not refunded within 10 days after sending the declaration (<TODAY -10)',
            isTriggered: ! $this->isCompleted() &&
                $sentVatTransportDeclarationSummary &&
                Carbon::parse($sentVatTransportDeclarationSummary)->isBefore(Carbon::today()->subDays(10)),
        );
    }
}
