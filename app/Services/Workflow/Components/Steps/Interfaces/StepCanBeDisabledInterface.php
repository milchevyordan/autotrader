<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Steps\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface StepCanBeDisabledInterface
{
    /**
     * Method to retrieve files associated with the step.
     *
     * @return bool
     */
    public function getIsDisabled(): bool;
}
