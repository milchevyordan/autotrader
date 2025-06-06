<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\DocumentLineType;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;

class SentBpmInvoice extends Step
{
    public const NAME = 'Sent BPM Invoice';

    public const MODAL_COMPONENT_NAME = null;

    public const HAS_QUICK_DATE_FINISH = false;

    /**
     * @var null|Document
     */
    private ?Document $bpmInvoice;

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
        return (bool) $this->getBpmInvoice();
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
        return $this->getBpmInvoice()?->date?->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * Get the value of bpmInvoice.
     *
     * @return null|Document
     */
    public function getBpmInvoice(): ?Document
    {
        if (! isset($this->bpmInvoice)) {
            $this->initBpmInvoice();
        }

        return $this->bpmInvoice;
    }

    private function initBpmInvoice(): void
    {
        $documents = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'documents']);

        if (! $documents) {
            $this->bpmInvoice = null;

            return;
        }

        $bpmInvoice = $documents->filter(function (Document $document) {
            return $document->lines->filter(function (DocumentLine $line) {
                DocumentLineType::Bpm->value == $line->type->value;
            });
        })?->first();

        $this->bpmInvoice = $bpmInvoice;

    }
}
