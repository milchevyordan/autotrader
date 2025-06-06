<?php

declare(strict_types=1);

namespace App\Services\Workflow\RedFlags;

final class RedFlag
{
    /**
     * @var bool
     */
    public bool $isTriggered;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $description;

    public function __construct(string $name, string $description, bool $isTriggered)
    {
        $this->name = $name;
        $this->description = $description;
        $this->isTriggered = $isTriggered;
    }
}
