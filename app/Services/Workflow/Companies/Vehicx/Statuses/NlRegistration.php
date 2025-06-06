<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\FilledInNlRegistrationNumber;
use App\Services\Workflow\Companies\Vehicx\Steps\FilledInRegistrationCodes;
use App\Services\Workflow\Companies\Vehicx\Steps\UploadedNLRegistration;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class NlRegistration extends Status
{
    public const NAME = 'NL Registration';

    private const STEP_CLASSES = [
        UploadedNLRegistration::class,
        FilledInNlRegistrationNumber::class,
        FilledInRegistrationCodes::class,
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
