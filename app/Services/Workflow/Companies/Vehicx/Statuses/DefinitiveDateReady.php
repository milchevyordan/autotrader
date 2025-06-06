<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\AgreedDateReadyWithSupplier;
use App\Services\Workflow\Companies\Vehicx\Steps\PickupLocationAddress;
use App\Services\Workflow\Companies\Vehicx\Steps\SendEmailSupplier;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class DefinitiveDateReady extends Status
{
    public const NAME = 'Definitive Date Ready';

    private const STEP_CLASSES = [
        PickupLocationAddress::class,
        SendEmailSupplier::class,
        AgreedDateReadyWithSupplier::class,
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
