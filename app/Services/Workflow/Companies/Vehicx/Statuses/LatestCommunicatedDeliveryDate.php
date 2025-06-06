<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\DeliveryDateAccordingToAgreement;
use App\Services\Workflow\Companies\Vehicx\Steps\UpdatedExpectedDeliveryDateToClient;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class LatestCommunicatedDeliveryDate extends Status
{
    public const NAME = 'Latest Communicated Delivery Date';

    private const STEP_CLASSES = [
        DeliveryDateAccordingToAgreement::class,
        UpdatedExpectedDeliveryDateToClient::class,
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
