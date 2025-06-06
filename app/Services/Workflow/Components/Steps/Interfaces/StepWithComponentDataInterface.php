<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Steps\Interfaces;

interface StepWithComponentDataInterface
{
    /**
     * @return mixed
     */
    public function getComponentData(): mixed;
}
