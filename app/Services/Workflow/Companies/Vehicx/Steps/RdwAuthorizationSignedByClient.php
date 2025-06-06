<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Steps\Interfaces\StepCanBeDisabledInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithImagesInterface;
use App\Services\Workflow\Components\Steps\Step;
use Illuminate\Database\Eloquent\Collection;

class RdwAuthorizationSignedByClient extends Step implements StepWithImagesInterface, StepCanBeDisabledInterface
{
    public const NAME = 'RDW Authorization Signed By Client';

    public const MODAL_COMPONENT_NAME = 'UploadImages';

    public const HAS_QUICK_DATE_FINISH = false;

    private MustBeRegisteredOnNameOfClient $mustBeRegisteredOnNameOfClient;

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
        return 'Yes' == $this->getMustBeRegisteredOnNameOfClient()->getSummary() ? $this->getFinishedStep() !== null : true;
    }

    /**
     * @return bool
     */
    protected function getHasQuickDateFinish(): bool
    {
        return 'Yes' == $this->getMustBeRegisteredOnNameOfClient()->getSummary() ? self::HAS_QUICK_DATE_FINISH : false;
    }

    /**
     * @return Collection
     */
    public function getImages(): Collection
    {
        return $this->getFinishedStep()?->images ?? new Collection();
    }

    /**
     * Get the value of mustBeRegisteredOnNameOfClient.
     *
     * @return MustBeRegisteredOnNameOfClient
     */
    public function getMustBeRegisteredOnNameOfClient(): MustBeRegisteredOnNameOfClient
    {
        if (! isset($this->mustBeRegisteredOnNameOfClient)) {
            $this->initMustBeRegisteredOnNameOfClient();
        }

        return $this->mustBeRegisteredOnNameOfClient;
    }

    /**
     * Init the mustBeRegisteredOnNameOfClient prop.
     *
     * @return void
     */
    private function initMustBeRegisteredOnNameOfClient(): void
    {
        $this->mustBeRegisteredOnNameOfClient = new MustBeRegisteredOnNameOfClient($this->getModelsWorkflow());
    }

    public function getIsDisabled(): bool
    {
        $mustBeRegisteredOnNameOfClient = $this->getMustBeRegisteredOnNameOfClient();

        return ! $mustBeRegisteredOnNameOfClient->isCompleted() || $mustBeRegisteredOnNameOfClient->getSummary() == 'No';
    }
}
