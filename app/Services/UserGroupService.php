<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\StoreUserGroupRequest;
use App\Http\Requests\UpdateUserGroupRequest;
use App\Models\User;
use App\Models\UserGroup;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;

class UserGroupService extends Service
{
    /**
     * User group instance.
     *
     * @var UserGroup
     */
    public UserGroup $userGroup;

    /**
     * ExpandGroupService instance.
     *
     * @var ExpandGroupService
     */
    public ExpandGroupService $expandGroupService;

    /**
     * Array holding the columns in main relation that will be selected.
     *
     * @var array|string[]
     */
    private array $relationColumnsToSelect = ['id', 'full_name', 'creator_id', 'email', 'company_id', 'users.created_at', 'users.updated_at'];

    /**
     * Array holding all relations that will be loaded.
     *
     * @var array|string[]
     */
    private array $relations = [
        'users.creator',
        'users.company:id,name',
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
     * The key represents the columns that we must search in.
     *
     * @var array<string>
     */
    private array $childFilterColumns = [
        'id', 'full_name', 'email',
    ];

    /**
     * The key represents the relation and the value represents the column name.
     *
     * @var array<string, string>
     */
    private array $childRelations = [
        'creator' => 'full_name',
        'company' => 'name',
    ];

    /**
     * Array holding main relation columns that are cast.
     *
     * @var array
     */
    private array $enumCasts = [
    ];

    /**
     * Create a new UserGroupService instance.
     */
    public function __construct()
    {
        $this->userGroup = new UserGroup();
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
            $this->userGroup
                ->inThisCompany()
                ->select(UserGroup::$defaultSelectFields),
            'users',
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
     * Get the value of userGroup.
     *
     * @return UserGroup
     */
    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    /**
     * Set the model of the user group.
     *
     * @param  UserGroup $userGroup
     * @return self
     */
    public function setUserGroup(UserGroup $userGroup): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }

    /**
     * User Group creation.
     *
     * @param StoreUserGroupRequest $request
     * @return $this
     */
    public function createUserGroup(StoreUserGroupRequest $request): self
    {
        $validatedRequest = $request->validated();

        $userGroup = new UserGroup();
        $userGroup->fill($validatedRequest);
        $userGroup->creator_id = auth()->id();
        $userGroup->save();

        $userGroup->users()->sync($validatedRequest['userIds']);
        $this->setUserGroup($userGroup);

        return $this;
    }

    /**
     * Update the vehicle group model.
     *
     * @param UpdateUserGroupRequest $request
     * @return $this
     */
    public function updateUserGroup(UpdateUserGroupRequest $request): static
    {
        $validatedRequest = $request->validated();

        $userGroup = $this->getUserGroup();

        $userGroup->update($validatedRequest);
        $userGroup->users()->sync($validatedRequest['userIds']);
        $this->setUserGroup($userGroup);

        return $this;
    }

    /**
     * Users datatable used in edit form.
     *
     * @return DataTable
     */
    public function getEditMethodDataTable(): DataTable
    {
        $userHasSearched = request(null)->input('filter.global');

        return $this->getUsersDataTable()
            ->run(config('app.default.pageResults', 10), function ($model) use ($userHasSearched) {
                return $userHasSearched ? $model :
                    $model->whereHas('userGroups', function ($query) {
                        $query->where('user_groups.id', $this->getUserGroup()->id);
                    });
            });
    }

    /**
     * Users datatable used in create form.
     *
     * @return DataTable
     */
    public function getUsersDataTable(): DataTable
    {
        return (new DataTable(
            User::inThisCompany()->role('Customer')->select(User::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setRelation('company', ['id', 'name'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('company.name', __('Company'), true, true)
            ->setColumn('full_name', __('Name'), true, true)
            ->setColumn('email', __('Email'), true, true)
            ->setTimestamps();
    }

    /**
     * Return datatable of Users Groups by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getUserGroupsDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(array_merge(UserGroup::$defaultSelectFields, ['created_at']))
        ))
            ->setColumn('id', '#', true, true)
            ->setColumn('name', __('Name'), true, true)
            ->setDateColumn('created_at', 'd.m.Y H:i');
    }
}
