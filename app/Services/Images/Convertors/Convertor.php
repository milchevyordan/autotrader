<?php

declare(strict_types=1);

namespace App\Services\Images\Convertors;

/**
 * Class Convertor.
 *
 * This class provides base functionalities for image conversion.
 */
abstract class Convertor
{
    protected string $imagePath;

    protected $imageResource;

    protected int $width;

    protected int $height;

    abstract protected function loadImage(): void;

    abstract protected function outputImage(string $outputPath, int $quality = 90): void;

    public function __construct(string $imagePath)
    {
        $this->imagePath = $imagePath;
        $this->loadImage();
    }

    public function toBase64(): string
    {
        ob_start();
        $this->outputImage('php://output');
        $imageData = ob_get_contents();
        ob_end_clean();

        return base64_encode($imageData);
    }

    public function resize(int $newWidth, int $newHeight): void
    {
        $aspectRatio = $this->width / $this->height;

        if ($newWidth / $newHeight > $aspectRatio) {
            $newWidth = (int) ($newHeight * $aspectRatio);
        } else {
            $newHeight = (int) ($newWidth / $aspectRatio);
        }

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $this->imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $this->width, $this->height);

        $this->imageResource = $resizedImage;
        $this->width = $newWidth;
        $this->height = $newHeight;
    }

    public function save(string $outputPath, int $quality = 90): string
    {
        $this->outputImage($outputPath, $quality);

        return $outputPath;
    }

    public function __destruct()
    {
        if ($this->imageResource) {
            imagedestroy($this->imageResource);
        }
    }
}
