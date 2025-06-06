<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Steps\ProformaOrInvoice;
use App\Services\Workflow\Companies\Vehicx\Steps\ProofPaymentSentSupplier;
use App\Services\Workflow\Companies\Vehicx\Steps\VehiclePaymentBank;
use App\Services\Workflow\Companies\Vehicx\Steps\VehiclePaymentCompleted;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

class PaymentSupplier extends Status
{
    public const NAME = 'Payment Supplier';

    private const STEP_CLASSES = [
        ProformaOrInvoice::class,
        VehiclePaymentBank::class,
        VehiclePaymentCompleted::class,
        ProofPaymentSentSupplier::class,
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
