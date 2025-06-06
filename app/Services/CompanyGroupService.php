<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Country;
use App\Models\CompanyGroup;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;

class CompanyGroupService extends Service
{
    /**
     * Company group model.
     *
     * @var CompanyGroup
     */
    public CompanyGroup $companyGroup;

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
    private array $relationColumnsToSelect = ['id', 'company_group_id', 'main_user_id', 'country', 'name', 'postal_code', 'city', 'phone'];

    /**
     * Array holding all relations that will be loaded.
     *
     * @var array|string[]
     */
    private array $relations = [
        'companies.mainUser:id,full_name',
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
        'name', 'phone', 'city', 'postal_code',
    ];

    /**
     * The key represents the relation and the value represents the column name of relation's searchable columns.
     *
     * @var array<string, string>
     */
    private array $childRelations = [
        'mainUser' => 'full_name',
    ];

    /**
     * Array holding main relation columns that are cast.
     *
     * @var array|class-string[]
     */
    private array $enumCasts = [
        'country' => Country::class,
    ];

    /**
     * Create a new CompanyGroupService instance.
     */
    public function __construct()
    {
        $this->companyGroup = new CompanyGroup();
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
            $this->companyGroup
                ->inThisCompany()
                ->select(CompanyGroup::$defaultSelectFields),
            'companies',
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
     * Return datatable of Company Groups by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getCompanyGroupsDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(array_merge(CompanyGroup::$defaultSelectFields, ['created_at']))
        ))
            ->setColumn('id', '#', true, true)
            ->setColumn('name', __('Name'), true, true)
            ->setDateColumn('created_at', 'd.m.Y H:i');
    }
}
