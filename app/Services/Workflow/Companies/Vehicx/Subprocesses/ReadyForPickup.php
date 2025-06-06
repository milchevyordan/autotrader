<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Subprocesses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Statuses\DefinitiveDateReady;
use App\Services\Workflow\Companies\Vehicx\Statuses\LatestExpectDateReady;
use App\Services\Workflow\Companies\Vehicx\Statuses\PaymentSupplier;
use App\Services\Workflow\Components\Status;
use App\Services\Workflow\Components\Subprocesses\Subprocess;
use Illuminate\Support\Collection;

class ReadyForPickup extends Subprocess
{
    public const NAME = 'Ready for pickup';

    public const VUE_COMPONENT_NAME = 'CalendarCheck';

    private const STATUS_CLASSES = [
        LatestExpectDateReady::class,
        DefinitiveDateReady::class,
        PaymentSupplier::class,
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
