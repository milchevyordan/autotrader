<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Subprocesses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Statuses\BpmDeclared;
use App\Services\Workflow\Companies\Vehicx\Statuses\NlRegistration;
use App\Services\Workflow\Companies\Vehicx\Statuses\RdwApproved;
use App\Services\Workflow\Companies\Vehicx\Statuses\RdwIdentificationApplied;
use App\Services\Workflow\Components\Status;
use App\Services\Workflow\Components\Subprocesses\Subprocess;
use Illuminate\Support\Collection;

class Import extends Subprocess
{
    public const NAME = 'Import';

    public const VUE_COMPONENT_NAME = '';

    private const STATUS_CLASSES = [
        RdwIdentificationApplied::class,
        RdwApproved::class,
        BpmDeclared::class,
        NlRegistration::class,
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
