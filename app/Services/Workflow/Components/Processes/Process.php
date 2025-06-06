<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Processes;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Component;
use App\Services\Workflow\Components\Subprocesses\Subprocess;
use Illuminate\Support\Collection;

abstract class Process extends Component
{
    /**
     * @return Collection<Subprocess>
     */
    abstract protected function getSubprocessClasses(): Collection;

    /**
     * @var Collection<Subprocess>
     */
    public Collection $subprocesses;

    /**
     * @var string
     */
    public string $name;

    private ModelsWorkflow $modelsWorkflow;

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct();

        $this->modelsWorkflow = $modelsWorkflow;
        $this->name = $this->getName();
        $this->subprocesses = $this->getSubprocesses();
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->getSubprocesses()->every(function (Subprocess $subprocess) {
            return $subprocess->isCompleted();
        });
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
     * @return Collection<BaseStep>
     */
    protected function getSubprocesses(): Collection
    {
        if (! isset($this->steps)) {
            $this->initSubprocesses();
        }

        return $this->subprocesses;
    }

    /**
     * @return void
     */
    private function initSubprocesses(): void
    {
        $modelsWorkflow = $this->getModelsWorkflow();

        $this->subprocesses = $this->getSubprocessClasses()->map(fn ($subprocess) => new $subprocess($modelsWorkflow));
    }
}
