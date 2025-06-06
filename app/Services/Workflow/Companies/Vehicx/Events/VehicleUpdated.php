<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Events;

use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Services\Workflow\Companies\Vehicx\Steps\ExpectedDateReadyInPurchaseOrder;
use App\Services\Workflow\Events\BaseVehicleUpdated;
use App\Services\Workflow\Events\Interfaces\EventInterface;
use App\Services\Workflow\WorkflowFinishedStepService;
use Carbon\Carbon;

class VehicleUpdated extends BaseVehicleUpdated implements EventInterface
{
    public function __construct(Vehicle $vehicle, array $validatedRequest)
    {
        parent::__construct($vehicle, $validatedRequest);
    }

    public function run(): void
    {
        if ($this->validatedRequest['expected_date_of_availability_from_supplier'] !== $this->vehicle->expected_date_of_availability_from_supplier) {
            $workflowFinishedStepService = new WorkflowFinishedStepService();
            $expectedDate = $this->validatedRequest['expected_date_of_availability_from_supplier']['from'][0] ?? null;

            if ($expectedDate) {
                $workflowFinishedStepService->upsertModelWorkflowStep($this->vehicle, ExpectedDateReadyInPurchaseOrder::class, null, Carbon::parse($expectedDate));
            }
        }
    }
}
