<?php

declare(strict_types=1);

namespace App\Services\Pdf\Canvas;

use Illuminate\Contracts\Support\Arrayable;

class CanvasTextRenderer extends CanvasRenderer implements Arrayable
{
    private string $text;

    private ?string $font;

    private int $size;

    private array $color;

    public function __construct(
        float $x,
        float $y,
        bool $isOnEveryPage,
        string $text,
        ?string $font = null,
        int $size = 8,
        array $color = [0.5, 0.5, 0.5],
    ) {
        parent::__construct($x, $y, $isOnEveryPage);

        $this->text = $text;
        $this->font = $font;
        $this->size = $size;
        $this->color = $color;
    }

    public function toArray(): array
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
