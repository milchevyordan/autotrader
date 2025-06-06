<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Steps\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface StepWithFilesInterface
{
    /**
     * Method to retrieve files associated with the step.
     *
     * @return Collection
     */
    public function getFiles(): Collection;
}
