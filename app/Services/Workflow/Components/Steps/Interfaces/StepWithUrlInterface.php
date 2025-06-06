<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Steps\Interfaces;

interface StepWithUrlInterface
{
    /**
     * @return null|string
     */
    public function getUrl(): ?string;
}
