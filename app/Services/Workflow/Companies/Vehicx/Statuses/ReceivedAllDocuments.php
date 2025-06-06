<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\AllOriginalDocumentsReceived;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class ReceivedAllDocuments extends Status
{
    public const NAME = 'Received all Documents';

    private const STEP_CLASSES = [
        AllOriginalDocumentsReceived::class,
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
