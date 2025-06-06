<?php

declare(strict_types=1);

namespace App\Services\Vehicles;

use App\Http\Requests\StorePreOrderVehicleRequest;
use App\Http\Requests\UpdatePreOrderVehicleRequest;
use App\Models\PreOrderVehicle;
use App\Services\DataTable\DataTable;
use App\Services\Files\UploadHelper;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class PreOrderVehicleService extends BaseVehicleService
{
    /**
     * The preOrderVehicle model.
     *
     * @var PreOrderVehicle
     */
    public PreOrderVehicle $preOrderVehicle;

    /**
     * Create a new PreOrderVehicleService instance.
     */
    public function __construct()
    {
        $this->setPreOrderVehicle(new PreOrderVehicle());
    }

    /**
     * Return preorder vehicles datatable shown in index page.
     *
     * @param  null|array $additionalRelations
     * @param  null|bool  $withTrashed
     * @param  ?array     $additionalSelectFields
     * @return DataTable
     */
    public function getIndexMethodTable(?array $additionalRelations = [], ?bool $withTrashed = false, ?array $additionalSelectFields = []): DataTable
    {
        $dataTable = (new DataTable(
            PreOrderVehicle::inThisCompany()
                ->when($withTrashed, function ($query) {
                    return $query->withTrashed();
                })
                ->select(array_merge(PreOrderVehicle::$defaultSelectFields, $additionalSelectFields))
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('engine', ['id', 'name'])
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('engine.name', __('Engine'), true, true)
            ->setTimestamps();

        foreach ($additionalRelations as $relation => $relatedColumns) {
            $dataTable->setRelation($relation, $relatedColumns);
        }

        return $dataTable;
    }

    /**
     * Get the model of the preOrderVehicle.
     *
     * @return PreOrderVehicle
     */
    public function getPreOrderVehicle(): PreOrderVehicle
    {
        return $this->preOrderVehicle;
    }

    /**
     * Set the model of the preOrderVehicle.
     *
     * @param  PreOrderVehicle $preOrderVehicle
     * @return self
     */
    public function setPreOrderVehicle(PreOrderVehicle $preOrderVehicle): self
    {
        $this->preOrderVehicle = $preOrderVehicle;

        return $this;
    }

    /**
     * PreOrderVehicle creation.
     *
     * @param StorePreOrderVehicleRequest $request
     * @return self
     */
    public function createPreOrderVehicle(StorePreOrderVehicleRequest $request): self
    {
        $validatedRequest = $request->validated();

        $preOrderVehicle = $this::createVehicle(new PreOrderVehicle(), $validatedRequest);
        $preOrderVehicle->sendInternalRemarks($validatedRequest);
        $this->setPreOrderVehicle($preOrderVehicle);

        return $this;
    }

    /**
     * PreOrderVehicle update.
     *
     * @param UpdatePreOrderVehicleRequest $request
     * @return self
     */
    public function updatePreOrderVehicle(UpdatePreOrderVehicleRequest $request): self
    {
        $validatedRequest = $request->validated();

        $preOrderVehicle = $this->getPreOrderVehicle();

        $preOrderVehicle
            ->saveWithImages(UploadHelper::uploadMultipleImages($validatedRequest, 'internalImages'), 'internalImages')
            ->saveWithImages(UploadHelper::uploadMultipleImages($validatedRequest, 'externalImages'), 'externalImages')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'internalFiles'), 'internalFiles')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'externalFiles'), 'externalFiles');

        $preOrderVehicle->update($validatedRequest);
        $preOrderVehicle->calculation->update($validatedRequest);
        $preOrderVehicle->sendInternalRemarks($validatedRequest);

        $this->setPreOrderVehicle($preOrderVehicle);

        return $this;
    }

    /**
     * Return datatable of Pre Order Vehicles by the provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getPreOrderVehiclesDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(PreOrderVehicle::$defaultSelectFields)
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('engine', ['id', 'name'])
            ->setColumn('id', '#', true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('engine.name', __('Engine'), true, true)
            ->setTimestamps();

        return $dataTable;
    }
}
