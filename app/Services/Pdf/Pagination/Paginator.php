<?php

declare(strict_types=1);

namespace App\Services\Pdf\Pagination;

use App\Services\Pdf\Canvas\CanvasTextRenderer;

class Paginator extends CanvasTextRenderer
{
    public function __construct(
        float $x,
        float $y,
        string $text = 'Page {PAGE_NUM} of {PAGE_COUNT}',
        ?string $font = null,
        ?int $size = 8,
        ?array $color = [0.5, 0.5, 0.5]
    ) {
        parent::__construct(
            x: $x,
            y: $y,
            isOnEveryPage: true,
            text: $text,
            font: $font,
            size: $size,
            color: $color
        );
    }
}
