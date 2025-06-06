<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\BpmAppealClosed;
use App\Services\Workflow\Companies\Vehicx\Steps\BpmAppealFiled;
use App\Services\Workflow\Companies\Vehicx\Steps\BpmReassessedByTaxAuthorities;
use App\Services\Workflow\Companies\Vehicx\Steps\SubmitBpmDeclarationArticle;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class FinalisedBpmDeclaration extends Status
{
    public const NAME = 'Finalised BPM declaration';

    private const STEP_CLASSES = [
        SubmitBpmDeclarationArticle::class,
        BpmReassessedByTaxAuthorities::class,
        BpmAppealFiled::class,
        BpmAppealClosed::class,
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
