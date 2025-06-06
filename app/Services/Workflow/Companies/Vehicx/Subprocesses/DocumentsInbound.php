<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Subprocesses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Statuses\ReceivedAllDocuments;
use App\Services\Workflow\Companies\Vehicx\Statuses\WaitingDocuments;
use App\Services\Workflow\Components\Status;
use App\Services\Workflow\Components\Subprocesses\Subprocess;
use Illuminate\Support\Collection;

class DocumentsInbound extends Subprocess
{
    public const NAME = 'Documents Inbound';

    public const VUE_COMPONENT_NAME = 'CalendarCheck';

    private const STATUS_CLASSES = [
        WaitingDocuments::class,
        ReceivedAllDocuments::class,
    ];

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct($modelsWorkflow);
    }

    /**
     * @return string The name of the component
     */
    public function getName(): string
    {
        return __(self::NAME);
    }

    /**
     * @return string
     */
    protected function getVueComponentName(): string
    {
        return self::VUE_COMPONENT_NAME;
    }

    /**
     * @return Collection<string> Status classes
     */
    protected function getStatusClasses(): Collection
    {
        return new Collection(self::STATUS_CLASSES);
    }
}
