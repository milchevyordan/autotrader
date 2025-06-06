<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\ExpectedPickupDate;
use App\Services\Workflow\Companies\Vehicx\Steps\InformedSupplierAboutPickupDate;
use App\Services\Workflow\Companies\Vehicx\Steps\PickupConfirmed;
use App\Services\Workflow\Companies\Vehicx\Steps\StickerSheetPrinted;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class WaitingDelivery extends Status
{
    public const NAME = 'Waiting for Delivery';

    private const STEP_CLASSES = [
        ExpectedPickupDate::class,
        InformedSupplierAboutPickupDate::class,
        PickupConfirmed::class,
        StickerSheetPrinted::class,
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
