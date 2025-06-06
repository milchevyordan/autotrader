<?php

declare(strict_types=1);

namespace App\Services\Pdf\Canvas;

class CanvasRenderer
{
    protected float $x;

    protected float $y;

    public bool $isOnEveryPage;

    public function __construct(
        float $x,
        float $y,
        $isOnEveryPage = false,
    ) {
        $this->x = $x;
        $this->y = $y;
        $this->isOnEveryPage = $isOnEveryPage;
    }
}
