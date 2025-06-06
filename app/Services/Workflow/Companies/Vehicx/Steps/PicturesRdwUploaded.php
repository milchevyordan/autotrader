<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithImagesInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Step;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use App\Services\Workflow\RedFlags\RedFlag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PicturesRdwUploaded extends Step implements StepWithImagesInterface, StepWithRedFlagInterface
{
    public const NAME = 'Pictures for RDW Uploaded';

    public const MODAL_COMPONENT_NAME = 'UploadImages';

    public const HAS_QUICK_DATE_FINISH = true;

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct($modelsWorkflow);
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        return __(self::NAME);
    }

    /**
     * @return null|string
     */
    protected function getModalComponentName(): ?string
    {
        return self::MODAL_COMPONENT_NAME;
    }

    /**
     * @return bool
     */
    protected function isCompleted(): bool
    {
        return $this->getFinishedStep() !== null;
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return self::HAS_QUICK_DATE_FINISH;
    }

    protected function getSummary(): ?string
    {
        return $this->getFinishedStep()?->images?->first()?->created_at?->format(self::SUMMARY_DATE_FORMAT);
    }

    /**
     * @return Collection
     */
    public function getImages(): Collection
    {
        return $this->getFinishedStep()?->images ?? new Collection();
    }

    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag
    {
        $vehicleReceived = new VehicleReceived($this->getModelsWorkflow());

        $transportOrders = $this->getSelfProp(['modelsWorkflow', 'vehicleable', 'transportOrders']);
        $transportOrderInboundStatusCmr = $transportOrders?->where('transport_type', TransportType::Inbound->value)->where('status', TransportOrderStatus::Cmr_waybill)?->first();

        return new RedFlag(
            name: self::NAME,
            description: 'Vehicle was Received yesterday or before AND CMR not send (< TODAY)',
            isTriggered: $vehicleReceived->transportOrderDeliveredDateCarbonObject?->isBefore(Carbon::yesterday()) && ! $transportOrderInboundStatusCmr
        );
    }
}
