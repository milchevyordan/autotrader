<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\MustBeRegisteredOnNameOfClient;
use App\Services\Workflow\Companies\Vehicx\Steps\RdwAuthorizationSignedByClient;
use App\Services\Workflow\Companies\Vehicx\Steps\RegisteredOnNameClient;
use App\Services\Workflow\Companies\Vehicx\Steps\SendCodesOrDocuments;
use App\Services\Workflow\Companies\Vehicx\Steps\SentNlRegistrationCodes;
use App\Services\Workflow\Companies\Vehicx\Steps\SentOriginalDocuments;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class CodesOrDocumentsSentToClient extends Status
{
    public const NAME = 'Codes Or Documents Sent To Client';

    private const STEP_CLASSES = [
        SendCodesOrDocuments::class,
        SentNlRegistrationCodes::class,
        SentOriginalDocuments::class,
        MustBeRegisteredOnNameOfClient::class,
        RdwAuthorizationSignedByClient::class,
        RegisteredOnNameClient::class,
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
