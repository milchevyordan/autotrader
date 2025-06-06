<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\DamagesOrItemsReclamation;
use App\Services\Workflow\Companies\Vehicx\Steps\MissingPartsOrIncompleteItems;
use App\Services\Workflow\Companies\Vehicx\Steps\PicturesRdwUploaded;
use App\Services\Workflow\Companies\Vehicx\Steps\SecondKeyMissing;
use App\Services\Workflow\Companies\Vehicx\Steps\VehicleIntakeFormUploaded;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class Intake extends Status
{
    public const NAME = 'Intake';

    private const STEP_CLASSES = [
        VehicleIntakeFormUploaded::class,
        PicturesRdwUploaded::class,
        SecondKeyMissing::class,
        MissingPartsOrIncompleteItems::class,
        DamagesOrItemsReclamation::class,
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
