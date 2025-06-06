<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\PassedRandomInspection;
use App\Services\Workflow\Companies\Vehicx\Steps\RandomInspectionDate;
use App\Services\Workflow\Companies\Vehicx\Steps\RandomInspectionPlanned;
use App\Services\Workflow\Companies\Vehicx\Steps\RdwIdentification;
use App\Services\Workflow\Companies\Vehicx\Steps\RdwRandomInspection;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class RdwIdentificationApplied extends Status
{
    public const NAME = 'RDW Identification Applied';

    private const STEP_CLASSES = [
        RdwIdentification::class,
        RdwRandomInspection::class,
        RandomInspectionPlanned::class,
        RandomInspectionDate::class,
        PassedRandomInspection::class,
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
