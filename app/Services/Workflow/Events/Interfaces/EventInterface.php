<?php

declare(strict_types=1);

namespace App\Services\Workflow\Events\Interfaces;

interface EventInterface
{
    public function run(): void;
}
