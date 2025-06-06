<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FuelType;
use App\Enums\VehicleBody;
use App\Models\VehicleGroup;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;

class VehicleGroupService extends Service
{
    /**
     * Vehicle group instance.
     *
     * @var VehicleGroup
     */
    public VehicleGroup $vehicleGroup;

    /**
     * ExpandGroupService instance.
     *
     * @var ExpandGroupService
     */
    public ExpandGroupService $expandGroupService;

    /**
     * Array holding the columns in main relation that will be selected.
     *
     * @var array<string>
     */
    private array $relationColumnsToSelect = [
        'id',
        'creator_id',
        'make_id',
        'vehicle_model_id',
        'variant_id',
        'engine_id',
        'vehicle_group_id',
        'body',
        'fuel',
        'specific_exterior_color',
        'specific_interior_color',
        'kilometers',
        'first_registration_date',
        'identification_code',
        'image_path',
    ];

    /**
     * Array holding all relations that will be loaded.
     *
     * @var array<string>
     */
    private array $relations = [
        'vehicles.engine:id,name',
        'vehicles.make:id,name',
        'vehicles.variant:id,name',
        'vehicles.vehicleModel:id,name',
        'vehicles.creator:id,full_name',
        'vehicles.workflow:id,vehicleable_type,vehicleable_id',
    ];

    /**
     * Columns that will be searchable in main group model.
     *
     * @var array|string[]
     */
    private array $parentFilterColumns = [
        'name',
    ];

    /**
     * The key represents the columns that we must search in the main relation.
     *
     * @var array<string>
     */
    private array $childFilterColumns = [
        'kilometers', 'first_registration_date', 'identification_code',
    ];

    /**
     * The key represents the relation and the value represents the column name of relation's searchable columns.
     *
     * @var array<string, string>
     */
    private array $childRelations = [
        'engine'       => 'name',
        'make'         => 'name',
        'variant'      => 'name',
        'vehicleModel' => 'name',
        'creator'      => 'full_name',
    ];

    /**
     * Array holding main relation columns that are cast.
     *
     * @var array|string[]
     */
    private array $enumCasts = [
        'body' => VehicleBody::class,
        'fuel' => FuelType::class,
    ];

    /**
     * Create a new VehicleGroupService instance.
     */
    public function __construct()
    {
        $this->vehicleGroup = new VehicleGroup();
    }

    /**
     * Init the groups LengthAwarePaginator.
     *
     * @param  int                $perPage
     * @param  int                $collapsedGroupVehicles
     * @return ExpandGroupService
     */
    public function getExpandGroupService(int $perPage = 10, int $collapsedGroupVehicles = 2): ExpandGroupService
    {
        $searchText = request(null)->input('filter.global');

        return $this->expandGroupService = new ExpandGroupService(
            $this->vehicleGroup
                ->inThisCompany()
                ->select(VehicleGroup::$defaultSelectFields),
            'vehicles',
            $this->relations,
            $this->childRelations,
            $collapsedGroupVehicles,
            $this->childFilterColumns,
            $searchText,
            $this->relationColumnsToSelect,
            $this->parentFilterColumns,
            $this->enumCasts,
            $perPage,
        );
    }

    /**
     * Return datatable of Vehicle Groups by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getVehicleGroupsDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(array_merge(VehicleGroup::$defaultSelectFields, ['created_at']))
        ))
            ->setColumn('id', '#', true, true)
            ->setColumn('name', __('Name'), true, true)
            ->setDateColumn('created_at', 'd.m.Y H:i');
    }
}
