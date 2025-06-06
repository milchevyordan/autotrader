<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components;

use ReflectionClass;
use RuntimeException;

abstract class Component
{
    /**
     * @return string
     */
    abstract protected function getName(): string;

    /**
     * @return bool
     */
    abstract protected function isCompleted(): bool;

    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        if (! $reflection->hasConstant('NAME')) {
            throw new RuntimeException("Class {$reflection->getName()} must define a constant NAME.");
        }
    }
}
