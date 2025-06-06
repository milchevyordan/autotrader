<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Subprocesses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Statuses\BpmInvoice;
use App\Services\Workflow\Companies\Vehicx\Statuses\PaymentReceived;
use App\Services\Workflow\Companies\Vehicx\Statuses\PrepareInvoice;
use App\Services\Workflow\Companies\Vehicx\Statuses\ProformaVehicleInvoice;
use App\Services\Workflow\Companies\Vehicx\Statuses\VehicleInvoiced;
use App\Services\Workflow\Components\Status;
use App\Services\Workflow\Components\Subprocesses\Subprocess;
use Illuminate\Support\Collection;

class Invoicing extends Subprocess
{
    public const NAME = 'Invoicing';

    public const VUE_COMPONENT_NAME = 'Invoice';

    private const STATUS_CLASSES = [
        PrepareInvoice::class,
        ProformaVehicleInvoice::class,
        VehicleInvoiced::class,
        BpmInvoice::class,
        PaymentReceived::class,
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
