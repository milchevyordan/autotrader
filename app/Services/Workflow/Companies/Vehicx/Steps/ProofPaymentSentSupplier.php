<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\EmailStepInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithFilesInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use Illuminate\Database\Eloquent\Collection;

class ProofPaymentSentSupplier extends Step implements EmailStepInterface, StepWithFilesInterface
{
    public const NAME = 'Proof of Payment Sent to supplier';

    public const MODAL_COMPONENT_NAME = 'UploadFiles';

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

    protected function getSummary(): ?string
    {
        return $this->getFinishedStep()?->files?->first()?->created_at?->format(self::SUMMARY_DATE_FORMAT);
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
        return 'Proof of payment';
    }

    /**
     * @return string
     */
    public function getEmailTemplateText(): string
    {
        return 'We are attaching the Proof of payment in this email';
    }
}
