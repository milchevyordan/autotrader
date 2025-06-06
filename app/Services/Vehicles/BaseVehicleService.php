<?php

declare(strict_types=1);

namespace App\Services\Vehicles;

use App\Http\Requests\StorePreOrderVehicleRequest;
use App\Http\Requests\StoreVehicleRequest;
use App\Models\Calculation;
use App\Models\PreOrderVehicle;
use App\Models\Vehicle;
use App\Services\Files\UploadHelper;

class BaseVehicleService
{
    /**
     * @param  PreOrderVehicle|Vehicle                         $model
     * @param  array $validatedRequest
     * @return PreOrderVehicle|Vehicle
     */
    public static function createVehicle(PreOrderVehicle|Vehicle $model, array $validatedRequest): PreOrderVehicle|Vehicle
    {
        $model->fill($validatedRequest);
        $model->creator_id = auth()->id();

        $model
            ->saveWithImages(UploadHelper::uploadMultipleImages($validatedRequest, 'internalImages'), 'internalImages')
            ->saveWithImages(UploadHelper::uploadMultipleImages($validatedRequest, 'externalImages'), 'externalImages')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'internalFiles'), 'internalFiles')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'externalFiles'), 'externalFiles');

        $calculation = new Calculation();
        $calculation->fill($validatedRequest);

        $model->calculation()->save($calculation);

        return $model;
    }
}
