<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Steps\Interfaces;

use App\Services\Workflow\RedFlags\RedFlag;

interface StepWithRedFlagInterface
{
    /**
     * @return RedFlag
     */
    public function getRedFlag(): RedFlag;
}
