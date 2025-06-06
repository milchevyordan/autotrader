<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Builder;

class RoleService extends Service
{
    /**
     * Roles that should be hidden
     *
     * @var array
     */
    protected array $hidden = [
        'Root',
        'Company Administrator',
    ];

    /**
     * Array holding all crm role names.
     *
     * @var string[]
     */
    protected static array $crmRoleNames = [
        'Supplier',
        'Transport Supplier',
        'Intermediary',
        'Purchaser',
        'Customer',
    ];

    /**
     * Array holding all main role names.
     *
     * @var string[]
     */
    protected static array $mainCompanyRoles = [
        'Super Manager',
        'Management',
        'Company Purchaser',
        'Manager SalesPurchasing',
        'Back Office Employee',
        'Back Office Manager',
        'Logistics Employee',
        'Finance Employee',
    ];

    public function createRoleAndAttachPermissions(StoreRoleRequest $request): \Spatie\Permission\Contracts\Role|\Spatie\Permission\Models\Role
    {
        $validatedRequest = $request->validated();
        $role = Role::create(['name' => $validatedRequest['name']]);
        $role->permissions()->attach($validatedRequest['permissions']);

        return $role;
    }

    public function updateRoleAndPermissions(UpdateRoleRequest $request, Role $role): Role
    {
        $validatedRequest = $request->validated();

        $changeLoggerService = new ChangeLoggerService($role, ['permissions'])->setOmittedFields(['guard_name']);

        $role->update($validatedRequest);
        $role->permissions()->sync($validatedRequest['permissions']);

        $changeLoggerService->logChanges($role);

        return $role;
    }

    /**
     * @return DataTable
     */
    public function getIndexMethodDataTable(): DataTable
    {
        return new DataTable(
            Role::whereNotIn('name', $this->hidden)
        )
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('name', __('Name'), true, true);
    }

    /**
     * Return users in the company that are having main role.
     *
     * @return Builder
     */
    public static function getCompanyMainRolesUsers(): Builder
    {
        $mainCompanyRoles = self::getMainCompanyRoles();

        return User::InThisCompany()->role($mainCompanyRoles->pluck('id'));
    }

    /**
     * Get all main roles.
     *
     * @return Builder
     */
    public static function getMainCompanyRoles(): Builder
    {
        return Role::whereIn('name', self::$mainCompanyRoles);
    }

    /**
     * Get all crm roles.
     *
     * @return Builder
     */
    public static function getCrmRoles(): Builder
    {
        return Role::whereIn('name', self::$crmRoleNames);
    }

    /**
     * The main company role names
     *
     * @return array<string>
     */
    public static function getMainCompanyRoleNames(): array
    {
        return self::$mainCompanyRoles;
    }

    /**
     * The CRM company role names
     *
     * @return array<string>
     */
    public static function getCrmCompanyRoleNames(): array
    {
        return self::$crmRoleNames;
    }
}
