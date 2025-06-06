<?php

declare(strict_types=1);

namespace App\Services\Images\Compressor;

use App\Services\Images\Compressor\Exception\CompressionException;
use App\Services\Images\Compressor\Exception\ImageInfoException;
use App\Services\Images\Compressor\Exception\ImageResizeException;
use App\Services\Images\Compressor\Exception\ImageResourceException;
use GdImage;
use Illuminate\Http\UploadedFile;

/**
 * Class Compressor.
 *
 * This class provides file compression functionalities.
 */
class Compressor
{
    /**
     * Quality of the image percent from 0 to 100.
     *
     * @var int
     */
    private int $quality = 75;

    /**
     * Width of new created image in pixels.
     *
     * @var int
     */
    private int $resizedImageWidth = 1920;

    /**
     * Set the value of quality.
     *
     * @param  int  $quality
     * @return self
     */
    public function setQuality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Set the value of resizedImageWidth.
     *
     * @param  int  $resizedImageWidth
     * @return self
     */
    public function setResizedImageWidth(int $resizedImageWidth): self
    {
        $this->resizedImageWidth = $resizedImageWidth;

        return $this;
    }

    /**
     * Check ifthe given file is a PDF.
     *
     * @param  UploadedFile $file
     * @return bool
     */
    private function isPdf(UploadedFile $file): bool
    {
        return $file->getClientMimeType() === 'application/pdf';
    }

    /**
     * Compresses and resizes an image file.
     *
     * @param  UploadedFile           $file
     * @return null|UploadedFile      The compressed image as an UploadedFile instance, or null if compression fails
     * @throws CompressionException
     * @throws ImageInfoException
     * @throws ImageResizeException
     * @throws ImageResourceException
     */
    public function compressAndResizeImage(UploadedFile $file): ?UploadedFile
    {
        $info = getimagesize($file->getPathname());

        if ($info === false) {
            throw new ImageInfoException();
        }

        $image = $this->createImageResource($file, $info);

        if (! $image) {
            throw new ImageResourceException();
        }

        $newImage = $this->resizeImage($image, $info);

        if (! $newImage) {
            throw new ImageResizeException();
        }

        return $this->saveCompressedImage($newImage, $file);
    }

    /**
     * Create image resource based on mime type.
     *
     * @param  UploadedFile  $file
     * @param  array         $info
     * @return GdImage|false
     */
    private function createImageResource(UploadedFile $file, array $info): GdImage|false
    {
        $sourcePath = $file->getPathname();

        switch ($info['mime']) {
            case 'image/jpeg':
                return imagecreatefromjpeg($sourcePath);
            case 'image/gif':
                return imagecreatefromgif($sourcePath);
            case 'image/png':
                return imagecreatefrompng($sourcePath);
            case 'image/webp':
                return imagecreatefromwebp($sourcePath);
            default:
                return false;
        }
    }

    /**
     * Resize image.
     *
     * @param  GdImage       $image
     * @param  array         $info
     * @return GdImage|false
     */
    private function resizeImage(GdImage $image, array $info)
    {
        $targetWidth = min($info[0], $this->resizedImageWidth);
        $aspectRatio = $targetWidth / $info[0];
        $newHeight = (int) ($info[1] * $aspectRatio);

        return imagescale($image, $targetWidth, $newHeight, \IMG_BILINEAR_FIXED);
    }

    /**
     * Save compressed image.
     *
     * @param  GdImage              $newImage
     * @param  UploadedFile         $file
     * @return null|UploadedFile    The path of the compressed image, or null if compression fails
     * @throws CompressionException
     */
    private function saveCompressedImage(GdImage $newImage, UploadedFile $file): ?UploadedFile
    {
        $exif = @exif_read_data($file->getPathname());

        // Apply rotation based on EXIF orientation
        if (! empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3:
                    $newImage = imagerotate($newImage, 180, 0);

                    break;
                case 6:
                    $newImage = imagerotate($newImage, -90, 0);

                    break;
                case 8:
                    $newImage = imagerotate($newImage, 90, 0);

                    break;
            }
        }

        $compressedPath = tempnam(sys_get_temp_dir(), 'compressed_image_').'.webp';
        $result = imagewebp($newImage, $compressedPath, $this->quality);

        // Free up memory
        imagedestroy($newImage);

        if (! $result) {
            throw new CompressionException();
        }

        // Create an UploadedFile instance from the compressed file
        return new UploadedFile(
            $compressedPath,
            pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME).'.webp',
            'image/webp',
            null,
            true // Delete the file after it's moved
        );
    }
}
