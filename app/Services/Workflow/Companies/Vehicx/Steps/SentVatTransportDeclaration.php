<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\PurchaseOrder;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\EmailStepInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithFilesInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use Illuminate\Database\Eloquent\Collection;

class SentVatTransportDeclaration extends Step implements EmailStepInterface, StepWithFilesInterface
{
    public const NAME = 'Sent VAT Transport Declaration';

    public const MODAL_COMPONENT_NAME = 'UploadFiles';

    public const HAS_QUICK_DATE_FINISH = false;

    /**
     * @var null|PurchaseOrder
     */
    private ?PurchaseOrder $purchaseOrder;

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
        return $this->getPurchaseOrder()?->vat_deposit ? $this->getFinishedStep() !== null : true;
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return $this->getPurchaseOrder()?->vat_deposit ? self::HAS_QUICK_DATE_FINISH : false;
    }

    /**
     * @return null|string
     */
    protected function getSummary(): ?string
    {
        return $this->getFiles()?->first()?->created_at->format(self::SUMMARY_DATE_FORMAT);
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
        return $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'supplier'])?->email ?? $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'supplierCompany'])?->email;

    }

    /**
     * @return string
     */
    public function getEmailSubject(): string
    {
        return 'Vat transport declaration';
    }

    /**
     * @return string
     */
    public function getEmailTemplateText(): string
    {
        return 'We are attaching the VAT transport declaration in this email';
    }

    /**
     * Get the value of purchaseOrder.
     *
     * @return null|PurchaseOrder
     */
    public function getPurchaseOrder(): ?PurchaseOrder
    {
        if (! isset($this->purchaseOrder)) {
            $this->initPurchaseOrder();
        }

        return $this->purchaseOrder;
    }

    /**
     * Inits the Purchase order.
     *
     * @return void
     */
    private function initPurchaseOrder(): void
    {

        $this->purchaseOrder = $this?->getSelfProp(['modelsWorkflow', 'vehicleable', 'purchaseOrder'])?->first();

    }
}
