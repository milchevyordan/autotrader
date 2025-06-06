<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\PrintedSentBpmDeclaration;
use App\Services\Workflow\Companies\Vehicx\Steps\TaxationDate;
use App\Services\Workflow\Companies\Vehicx\Steps\TaxationPlanned;
use App\Services\Workflow\Companies\Vehicx\Steps\TaxationReportReceived;
use App\Services\Workflow\Companies\Vehicx\Steps\UploadBpmDeclaration;
use App\Services\Workflow\Companies\Vehicx\Steps\ValuationListTableTaxation;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class BpmDeclared extends Status
{
    public const NAME = 'BPM Declared';

    private const STEP_CLASSES = [
        ValuationListTableTaxation::class,
        TaxationPlanned::class,
        TaxationDate::class,
        TaxationReportReceived::class,
        UploadBpmDeclaration::class,
        PrintedSentBpmDeclaration::class,
    ];

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct($modelsWorkflow);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return __(self::NAME);
    }

    /**
     * @return Collection<string> Step classes
     */
    protected function getStepClasses(): Collection
    {
        return new Collection(self::STEP_CLASSES);
    }
}
