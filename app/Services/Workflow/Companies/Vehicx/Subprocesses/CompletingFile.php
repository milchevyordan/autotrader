<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Subprocesses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Statuses\CodesOrDocumentsSentToClient;
use App\Services\Workflow\Companies\Vehicx\Statuses\CompletingFinancially;
use App\Services\Workflow\Companies\Vehicx\Statuses\FinalisedBpmDeclaration;
use App\Services\Workflow\Companies\Vehicx\Statuses\LatestCommunicatedDateDocuments;
use App\Services\Workflow\Companies\Vehicx\Statuses\MissingPartsDamageReclamation;
use App\Services\Workflow\Companies\Vehicx\Statuses\VatDepositRefund;
use App\Services\Workflow\Components\Status;
use App\Services\Workflow\Components\Subprocesses\Subprocess;
use Illuminate\Support\Collection;

class CompletingFile extends Subprocess
{
    public const NAME = 'Completing File';

    public const VUE_COMPONENT_NAME = 'CheckListIncompleted';

    private const STATUS_CLASSES = [
        LatestCommunicatedDateDocuments::class,
        CodesOrDocumentsSentToClient::class,
        MissingPartsDamageReclamation::class,
        VatDepositRefund::class,
        FinalisedBpmDeclaration::class,
        CompletingFinancially::class,
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
