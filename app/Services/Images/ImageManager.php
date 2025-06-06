<?php

declare(strict_types=1);

namespace App\Services\Images;

use App\Models\Image;
use App\Services\Files\FileManager;
use App\Services\Images\Convertors\WebpToJpegConvertor;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FileManagementService.
 *
 * This class provides file management functionalities such as uploading and deleting files.
 */
class ImageManager extends FileManager
{
    /**
     * Create a new ImageManager instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Update file order by given array with id's in database.
     *
     * @param  array $orderArray
     * @return void
     */
    public static function updateOrder(array $orderArray): void
    {
        foreach ($orderArray as $index => $id) {
            $image = Image::find($id);

            if (! $id) {
                continue;
            }

            $image->update(['order' => $index]);

            if (0 == $index) {
                $image->imageable->updateProfileImage();
            }
        }
    }

    /**
     * Set base64 for vehicle images.
     *
     * @param  Model $model
     * @return void
     */
    public static function setVehicleImagesBase64(Model &$model): void
    {
        foreach ($model->vehicles as &$vehicle) {
            self::setModelImagesBase64($vehicle);
        }
    }

    /**
     * Set base64 for vehicle images.
     *
     * @param  Model $model
     * @return void
     */
    public static function setModelImagesBase64(Model &$model): void
    {
        if (! $model->images->isEmpty()) {
            foreach ($model->images as &$image) {
                $imagePath = storage_path("app/public/{$image->path}");

                $webpToJpgConverter = new WebpToJpegConvertor($imagePath);

                $image->base64 = $webpToJpgConverter->toBase64();
            }
        }
    }
}
