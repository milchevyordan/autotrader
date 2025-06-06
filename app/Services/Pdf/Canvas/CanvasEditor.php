<?php

declare(strict_types=1);

namespace App\Services\Pdf\Canvas;

class CanvasEditor
{
    private float $x;

    private float $y;

    private ?string $text;

    private ?string $font;

    private ?int $size = 10;

    private array $color;

    public function __construct(
        float $x,
        float $y,
        string $text,
        ?string $font = null,
        ?int $size = 10,
        ?array $color = [0.5, 0.5, 0.5]
    ) {
        $this->x = $x;
        $this->y = $y;
        $this->text = $text;
        $this->font = $font;
        $this->size = $size;
        $this->color = $color;
    }

    public function toText(): array
    {
        return [
            $this->x,
            $this->y,
            $this->text,
            $this->font,
            $this->size,
            $this->color,
        ];
    }
}
