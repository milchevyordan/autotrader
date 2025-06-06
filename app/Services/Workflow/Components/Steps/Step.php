<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Steps;

use App\Models\Workflow as ModelsWorkflow;
use App\Models\WorkflowFinishedStep;
use App\Services\Workflow\Components\Component;
use App\Services\Workflow\Components\Steps\Interfaces\EmailStepInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepCanBeDisabledInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepThatCreateModuleInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithComponentDataInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithFilesInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithImagesInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithRedFlagInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithUrlInterface;
use App\Services\Workflow\RedFlags\RedFlag;
use App\Services\Workflow\Traits\SelfPropCheck;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use ReflectionClass;

abstract class Step extends Component
{
    use SelfPropCheck;

    /**
     *  The format if the format in summary is date
     */
    public const SUMMARY_DATE_FORMAT = 'd/m/Y';

    /**
     * @return bool
     */
    abstract protected function getHasQuickDateFinish(): bool;

    /**
     * @return null|string
     */
    abstract protected function getModalComponentName(): ?string;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public ?string $modalComponentName;

    /**
     * @var bool
     */
    public bool $hasQuickDateFinish;

    /**
     * @var bool
     */
    public bool $isCompleted;

    /**
     * @var bool
     */
    public bool $isDisabled;

    /**
     * @var null|string
     */
    public ?string $summary;

    /**
     * @var null|string
     */
    public ?string $finishedStepAdditionalValue;

    /**
     * @var mixed
     */
    public mixed $componentData;

    /**
     * @var null|string
     */
    public ?string $emailRecipient;

    /**
     * @var null|string
     */
    public ?string $emailSubject;

    /**
     * @var null|string
     */
    public ?string $emailTemplateText;

    /**
     * @var null|string
     */
    public ?string $url;

    /**
     * @var null|Collection
     */
    public ?Collection $files;

    /**
     * @var null|Collection
     */
    public ?Collection $images;

    /**
     * @var null|Carbon
     */
    public ?Carbon $dateFinished;

    /**
     * @var null|RedFlag
     */
    public ?RedFlag $redFlag;

    /**
     * @var string
     */
    public string $className;

    /**
     * @var string
     */
    protected string $namespace;

    /**
     * @var null|WorkflowFinishedStep
     */
    protected ?WorkflowFinishedStep $finishedStep;

    /**
     * @var ModelsWorkflow
     */
    private ModelsWorkflow $modelsWorkflow;

    /**
     * @param string         $name
     * @param ModelsWorkflow $modelsWorkflow
     */
    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct();

        $reflection = new ReflectionClass($this);
        $this->className = $reflection->getShortName();
        $this->namespace = $reflection->getName();
        $this->initFinishedStep($modelsWorkflow);
        $this->modelsWorkflow = $modelsWorkflow;
        $this->name = $this->getName();
        $this->modalComponentName = $this->getModalComponentName();
        $this->isCompleted = $this->isCompleted();
        $this->hasQuickDateFinish = $this->getHasQuickDateFinish();
        $this->dateFinished = $this->getFinishedStep()?->finished_at;
        $this->summary = $this->getSummary();
        $this->finishedStepAdditionalValue = $this->finishedStep?->additional_value;

        if ($this instanceof StepWithFilesInterface) {
            $this->files = $this->getFiles();
        }

        if ($this instanceof StepWithImagesInterface) {
            $this->images = $this->getImages();
        }

        if ($this instanceof StepWithComponentDataInterface) {
            $this->componentData = $this->getComponentData();
        }

        if ($this instanceof EmailStepInterface) {
            $this->emailRecipient = $this->getEmailRecipient();
            $this->emailSubject = $this->getEmailSubject();
            $this->emailTemplateText = $this->getEmailTemplateText();
        }

        if ($this instanceof StepWithRedFlagInterface) {
            $this->redFlag = $this->getRedFlag();
        }

        if ($this instanceof StepWithUrlInterface) {
            $this->url = $this->getUrl();
        }

        if ($this instanceof StepCanBeDisabledInterface) {
            $this->isDisabled = $this->getIsDisabled();
        }

    }

    /**
     * Get the value of finishedStepAdditionalValue.
     *
     * @return ?string
     */
    public function getFinishedStepAdditionalValue(): ?string
    {
        return $this->finishedStepAdditionalValue;
    }

    /**
     * Get the value of dateFinished.
     *
     * @return ?Carbon
     */
    public function getDateFinished(): ?Carbon
    {
        return $this->dateFinished;
    }

    /**
     * Get the value of finishedStep.
     *
     * @return null|WorkflowFinishedStep
     */
    public function getFinishedStep(): ?WorkflowFinishedStep
    {
        return $this->finishedStep;
    }

    /**
     * Get the value of modelsWorkflow.
     *
     * @return ModelsWorkflow
     */
    protected function getModelsWorkflow(): ModelsWorkflow
    {
        return $this->modelsWorkflow;
    }

    private function initFinishedStep(ModelsWorkflow $modelsWorkflow)
    {
        $this->finishedStep = $modelsWorkflow
            ->finishedSteps
            ->where('workflow_step_namespace', $this->namespace)
            ->first();
    }

    /**
     * @return null|string
     */
    protected function getSummary(): ?string
    {
        $finishedStep = $this->getFinishedStep();

        return $finishedStep ? $finishedStep->finished_at->format(self::SUMMARY_DATE_FORMAT) : null;
    }
}
