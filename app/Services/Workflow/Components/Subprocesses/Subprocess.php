<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Subprocesses;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Components\Component;
use App\Services\Workflow\Components\Statuses\Status;
use Illuminate\Support\Collection;

abstract class Subprocess extends Component
{
    /**
     * @return Collection<string> - Step classes
     */
    abstract protected function getStatusClasses(): Collection;

    /**
     * @return string
     */
    abstract protected function getVueComponentName(): string;

    /**
     * @var string
     */
    public string $vueComponentIcon;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var Collection
     */
    public Collection $statuses;

    /**
     * @var bool
     */
    public bool $isCompleted;

    /**
     * @var ModelsWorkflow
     */
    private ModelsWorkflow $modelsWorkflow;

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct();

        $this->modelsWorkflow = $modelsWorkflow;
        $this->name = $this->getName();
        $this->vueComponentIcon = $this->getVueComponentName();
        $this->statuses = $this->getStatuses();
        $this->isCompleted = $this->isCompleted();
    }

    public function isCompleted(): bool
    {
        return $this->getStatuses()->every(function (Status $status) {
            return $status->isCompleted();
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
     * @return Collection<Status>
     */
    protected function getStatuses(): Collection
    {
        if (! isset($this->statuses)) {
            $this->initStatuses();
        }

        return $this->statuses;
    }

    /**
     * @return void
     */
    private function initStatuses(): void
    {
        $modelsWorkflow = $this->getModelsWorkflow();

        $this->statuses = $this->getStatusClasses()->map(fn ($status) => new $status($modelsWorkflow));
    }
}
