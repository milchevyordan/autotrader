<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithFilesInterface;
use App\Services\Workflow\Components\Steps\Step;
use Illuminate\Database\Eloquent\Collection;

class CopyDocumentsBadQuality extends Step implements StepWithFilesInterface
{
    public const NAME = 'Copy documents BAD quality';

    public const MODAL_COMPONENT_NAME = 'UploadFiles';

    public const HAS_QUICK_DATE_FINISH = false;

    private CopyCompleteGoodQualityDocuments $copyCompleteGoodQualityDocuments;

    private AllOriginalDocumentsReceived $allOriginalDocumentsReceived;

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
        return $this->getCopyCompleteGoodQualityDocuments()->getFinishedStep()?->files ? null : self::MODAL_COMPONENT_NAME;
    }

    /**
     * @return bool
     */
    protected function isCompleted(): bool
    {
        return $this->getFinishedStep() !== null || $this->getCopyCompleteGoodQualityDocuments()->getFinishedStep() !== null || $this->getAllOriginalDocumentsReceived()->isCompleted();
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
     *Init the copyCompleteGoodQualityDocuments.
     *
     * @return void
     */
    public function initCopyCompleteGoodQualityDocuments(): void
    {
        $this->copyCompleteGoodQualityDocuments = new CopyCompleteGoodQualityDocuments($this->getModelsWorkflow());
    }

    /**
     * Get the value of copyCompleteGoodQualityDocuments.
     *
     * @return CopyCompleteGoodQualityDocuments
     */
    public function getCopyCompleteGoodQualityDocuments(): CopyCompleteGoodQualityDocuments
    {
        if (! isset($this->copyCompleteGoodQualityDocuments)) {
            $this->initCopyCompleteGoodQualityDocuments();
        }

        return $this->copyCompleteGoodQualityDocuments;
    }

    /**
     *Init the All Original Documents Received.
     *
     * @return void
     */
    public function initAllOriginalDocumentsReceived(): void
    {
        $this->allOriginalDocumentsReceived = new AllOriginalDocumentsReceived($this->getModelsWorkflow());
    }

    /**
     * Get the value of copyCompleteGoodQualityDocuments.
     *
     * @return AllOriginalDocumentsReceived
     */
    public function getAllOriginalDocumentsReceived(): AllOriginalDocumentsReceived
    {
        if (! isset($this->allOriginalDocumentsReceived)) {
            $this->initAllOriginalDocumentsReceived();
        }

        return $this->allOriginalDocumentsReceived;
    }
}
