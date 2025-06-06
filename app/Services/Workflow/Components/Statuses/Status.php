<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Statuses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Component;
use App\Services\Workflow\Components\Steps\Step;
use Illuminate\Support\Collection;
use InvalidArgumentException;

abstract class Status extends Component
{
    /**
     * @return Collection<string> - Step classes
     */
    abstract protected function getStepClasses(): Collection;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var null|string
     */
    public ?string $summary;

    /**
     * @var bool
     */
    public bool $isCompleted;

    /**
     * @var Collection<Step>
     */
    public Collection $steps;

    /**
     * @var ModelsWorkflow
     */
    private ModelsWorkflow $modelsWorkflow;

    /**
     * @param string           $name
     * @param string           $isCompleted
     * @param Collection<Step> $steps
     * @param ModelsWorkflow   $modelsWorkflow
     */
    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct();

        $this->modelsWorkflow = $modelsWorkflow;
        $this->name = $this->getName();
        $this->isCompleted = $this->isCompleted();
        $this->steps = $this->getSteps();
        $this->summary = $this->getSummary();

        $this->validateStepClasses();
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->getSteps()->every(function (Step $step) {
            return $step->isCompleted();
        });
    }

    /**
     * @return Collection<BaseStep>
     */
    public function getSteps(): Collection
    {
        if (! isset($this->steps)) {
            $this->initSteps();
        }

        return $this->steps;
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

    /**
     * @return null|string
     */
    protected function getSummary(): ?string
    {
        return $this->getSteps()->max(fn (Step $step) => $step->getFinishedStep()?->finished_at)?->format('d/m/Y');

    }

    /**
     * @return void
     */
    private function initSteps(): void
    {
        $modelsWorkflow = $this->getModelsWorkflow();

        $this->steps = $this->getStepClasses()->map(fn ($step) => new $step($modelsWorkflow));
    }

    private function validateStepClasses(): void
    {
        foreach ($this->getStepClasses() as $stepClass) {
            if (! is_subclass_of($stepClass, Step::class)) {
                throw new InvalidArgumentException("{$stepClass} must implement Step interface.");
            }
        }
    }
}
