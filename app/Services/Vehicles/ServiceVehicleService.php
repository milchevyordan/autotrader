<?php

declare(strict_types=1);

namespace App\Services\Vehicles;

use App\Models\ServiceVehicle;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;

class ServiceVehicleService
{
    /**
     * Return service vehicles in this company datatable shown in index page.
     *
     * @param  null|bool $withTrashed
     * @return DataTable
     */
    public function getIndexMethodTable(?bool $withTrashed = false): DataTable
    {
        return self::getDataTable($withTrashed)
            ->setRelation('serviceOrder', ['id', 'service_vehicle_id'])
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setTimestamps();
    }

    /**
     * Return dataTable of Service Vehicles by provided builder.
     *
     * @param  Builder   $builder
     * @param  bool      $full
     * @return DataTable
     */
    public static function getServiceVehiclesDataTableByBuilder(Builder $builder, bool $full = false): DataTable
    {
        $dataTable = (new DataTable(
            $builder->select(ServiceVehicle::$defaultSelectFields)
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('serviceOrder', ['id', 'service_vehicle_id'])
            ->setRelation('workflow', ['id', 'vehicleable_type', 'vehicleable_id'])
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', __('VIN'), true, true);

        if ($full) {
            $dataTable->setRelation('creator')
                ->setColumn('creator.full_name', __('Creator'), true);
        }

        $dataTable->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setTimestamps();

        return $dataTable;
    }

    /**
     * Duplicate code of retrieving service vehicles in the company datatable.
     *
     * @param  bool      $withTrashed
     * @return DataTable
     */
    public static function getDataTable(?bool $withTrashed = false, ?bool $forTransportOrder = false): DataTable
    {
        return (new DataTable(
            ServiceVehicle::inThisCompany()
                ->when($withTrashed, function ($query) {
                    return $query->withTrashed();
                })
                ->select(ServiceVehicle::$defaultSelectFields)
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('variant', ['id', 'name'])
            ->setRelation('vehicleModel', ['id', 'name'])
            ->setRelation('creator')
            ->setColumn('action', $forTransportOrder ? __('Action / Location / Price') : __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('vin', __('Vin'), true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('vehicleModel.name', __('Model'), true, true)
            ->setColumn('variant.name', __('Variant'), true, true);
    }
}
