<?php

declare(strict_types=1);

namespace App\Services\Images\Convertors;

use RuntimeException;

/**
 * Class WebpToJpegConvertor.
 *
 * This class converts WebP images to JPEG format.
 */
class WebpToJpegConvertor extends Convertor
{
    /**
     * @return void
     */
    protected function loadImage(): void
    {
        $this->imageResource = imagecreatefromwebp($this->imagePath);
        if ($this->imageResource === false) {
            throw new RuntimeException("Failed to load WebP image from: {$this->imagePath}");
        }
        $this->width = imagesx($this->imageResource);
        $this->height = imagesy($this->imageResource);
    }

    /**
     * @param  string $outputPath
     * @param  int    $quality
     * @return void
     */
    protected function outputImage(string $outputPath, int $quality = 90): void
    {
        if ($outputPath === 'php://output') {
            imagejpeg($this->imageResource, null, $quality);
        } else {
            imagejpeg($this->imageResource, $outputPath, $quality);
        }
    }
}
