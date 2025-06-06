<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\ImportOrOriginType;
use App\Models\SalesOrder;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\EmailStepInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithFilesInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use Illuminate\Database\Eloquent\Collection;

class SendCodesOrDocuments extends Step implements EmailStepInterface, StepWithFilesInterface
{
    public const NAME = 'Send Codes Or Documents';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    /**
     * @var null|SalesOrder
     */
    private ?SalesOrder $salesOrder;

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
        return (bool) $this->getSalesOrder()?->type_of_sale;
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return self::HAS_QUICK_DATE_FINISH;
    }

    /**
     * @return null|string Either 'Codes' or 'Documents'
     */
    protected function getSummary(): ?string
    {
        $salesOrder = $this->getSalesOrder();

        $codesOrDocuments = $salesOrder?->type_of_sale?->value == ImportOrOriginType::Import->value ? 'Codes' : 'Documents';

        return $salesOrder ? $codesOrDocuments : __('No SO');
    }

    /**
     * Get the value of salesOrder.
     *
     * @return null|SalesOrder
     */
    public function getSalesOrder(): ?SalesOrder
    {
        if (! isset($this->salesOrder)) {
            $this->initSalesOrder();
        }

        return $this->salesOrder;
    }

    /**
     * Inits the Sales order.
     *
     * @return void
     */
    private function initSalesOrder()
    {

        $this->salesOrder = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'salesOrder'])?->first();

    }

    /**
     * @return Collection
     */
    public function getFiles(): Collection
    {
        return $this->getFinishedStep()?->files ?? new Collection();
    }

    /**
     * @return null|string
     */
    public function getEmailRecipient(): ?string
    {
        $salesOrder = $this->getSalesOrder();

        return $salesOrder?->customer?->email;
    }

    /**
     * @return string
     */
    public function getEmailSubject(): string
    {
        $salesOrder = $this->getSalesOrder();

        $codesOrDocuments = $salesOrder?->type_of_sale?->value == ImportOrOriginType::Import->value ? 'Codes' : 'Documents';

        return $codesOrDocuments;
    }

    /**
     * @return string
     */
    public function getEmailTemplateText(): string
    {
        $salesOrder = $this->getSalesOrder();

        $codesOrDocuments = $salesOrder?->type_of_sale?->value == ImportOrOriginType::Import->value ? 'Codes' : 'Documents';

        return "We are attaching the $codesOrDocuments in this email";
    }
}
