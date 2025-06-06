<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends BaseSeeder
{
    /**
     * Permission table names from config.
     *
     * @var array
     */
    private array $spatieTableNames;

    /**
     * Array holding all management permissions.
     *
     * @var array
     */
    private array $managementPermissions = [
        'view-any-crm-company', 'create-crm-company', 'edit-crm-company', 'delete-crm-company', 'restore-crm-company',
        'view-any-crm-company-group', 'create-crm-company-group', 'edit-crm-company-group', 'delete-crm-company-group',
        'view-any-user-group', 'create-user-group', 'edit-user-group', 'delete-user-group',
        'view-any-crm-user', 'view-crm-user', 'create-crm-user', 'edit-crm-user', 'delete-crm-user',
        'view-any-purchase-order', 'create-purchase-order', 'edit-purchase-order', 'submit-purchase-order', 'approve-purchase-order', 'delete-purchase-order', 'restore-purchase-order',
        'view-any-pre-order', 'create-pre-order', 'edit-pre-order', 'submit-pre-order', 'approve-pre-order', 'delete-pre-order', 'restore-pre-order',
        'view-any-sales-order', 'create-sales-order', 'edit-sales-order', 'submit-sales-order', 'approve-sales-order', 'delete-sales-order', 'restore-sales-order',
        'view-any-service-order', 'create-service-order', 'edit-service-order', 'submit-service-order', 'delete-service-order', 'restore-service-order',
        'view-any-service-vehicle', 'create-service-vehicle', 'edit-service-vehicle', 'delete-service-vehicle', 'restore-service-vehicle',
        'view-any-work-order', 'create-work-order', 'edit-work-order', 'delete-work-order', 'restore-work-order',
        'view-work-order-task', 'create-work-order-task', 'edit-work-order-task', 'delete-work-order-task',
        'view-any-transport-order', 'create-transport-order', 'edit-transport-order', 'delete-transport-order', 'restore-transport-order',
        'view-any-service-level', 'create-service-level', 'edit-service-level', 'view-service-level', 'delete-service-level', 'restore-service-level',
        'view-any-document', 'create-document', 'edit-document', 'approve-document', 'delete-document', 'restore-document', 'duplicate-document',
        'view-any-vehicle', 'create-vehicle', 'edit-vehicle', 'duplicate-vehicle', 'transfer-vehicle', 'delete-vehicle', 'restore-vehicle',
        'view-any-management', 'view-any-overview', 'view-any-following',
        'view-any-pre-order-vehicle', 'create-pre-order-vehicle', 'edit-pre-order-vehicle', 'delete-pre-order-vehicle', 'restore-pre-order-vehicle',
        'view-any-quote', 'create-quote', 'edit-quote', 'delete-quote', 'approve-quote', 'duplicate-quote', 'reserve-quote', 'restore-quote',
        'view-any-quote-invitation', 'view-quote-invitation', 'create-quote-invitation', 'edit-quote-invitation', 'delete-quote-invitation',
        'create-workflow', 'view-workflow',
        'view-any-mail',
        'view-any-item', 'create-item', 'edit-item', 'delete-item', 'restore-item',
        'view-any-make', 'create-make', 'edit-make', 'delete-make',
        'view-any-vehicle-model', 'create-vehicle-model', 'edit-vehicle-model', 'delete-vehicle-model',
        'view-any-vehicle-group', 'create-vehicle-group', 'edit-vehicle-group', 'delete-vehicle-group',
        'view-any-engine', 'create-engine', 'edit-engine', 'delete-engine',
        'view-any-variant', 'create-variant', 'edit-variant', 'delete-variant',
        'edit-setting', 'delete-file', 'delete-image',
        'view-any-email-template', 'edit-email-template',
        'view-any-ownership', 'create-ownership', 'view-ownership', 'accept-or-reject-ownership',
    ];

    /**
     * Roles and permissions - The key of the array elements represents the role name.
     * The inner array holds the permissions for the current role.
     *
     * @var array
     */
    private array $rolesAndPermissions;

    /**
     * Create a new RolePermissionSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->spatieTableNames = config('permission.table_names');

        $this->rolesAndPermissions = [
            'Root' => [
                'view-any-base-user', 'create-base-user', 'edit-base-user',
                'view-any-base-company', 'create-base-company', 'edit-base-company',
                'view-any-configuration', 'create-configuration', 'edit-configuration', 'delete-configuration',
                'view-any-role', 'create-role', 'edit-role', 'delete-role', 'restore-role',
            ],
            'Company Administrator' => [
                'view-any-base-user', 'create-base-user', 'edit-base-user', 'view-base-user',
                'delete-image',
            ],
            'Super Manager' => [
                ...$this->managementPermissions,
                'super-change-status', 'super-edit',
                'update-company-logo',
            ],
            'Management' => [
                ...$this->managementPermissions,
            ],
            'Company Purchaser' => [
                ...$this->managementPermissions,
            ],
            'Manager SalesPurchasing' => [
                ...$this->managementPermissions,
            ],
            'Back Office Employee' => [
                ...$this->managementPermissions,
            ],
            'Back Office Manager' => [
                ...$this->managementPermissions,
            ],
            'Logistics Employee' => [
                ...$this->managementPermissions,
            ],
            'Finance Employee' => [
                ...$this->managementPermissions,
            ],
            'Finance Manager' => [
                ...$this->managementPermissions,
            ],
            'Supplier' => [
            ],
            'Transport Supplier' => [
            ],
            'Intermediary' => [
            ],
            'Purchaser' => [
            ],
            'Customer' => [
                'view-any-quote-invitation', 'view-quote-invitation', 'accept-or-reject-quote-invitation',
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->insertRoles();
        $this->insertPermissions();
        $this->insertRolePermissions();
    }

    /**
     * Insert all roles.
     *
     * @return void
     */
    private function insertRoles(): void
    {
        $rolesInsertData = collect(array_keys($this->rolesAndPermissions))->map(function ($role) {
            return [
                'name'       => $role,
                'guard_name' => 'web',
            ];
        })->toArray();

        DB::table($this->spatieTableNames['roles'])->insert($rolesInsertData);
    }

    /**
     * Insert all permissions.
     *
     * @return void
     */
    private function insertPermissions(): void
    {
        $uniquePermissions = collect($this->rolesAndPermissions)
            ->flatMap(function ($permissions) {
                return $permissions;
            })
            ->unique()
            ->toArray();

        // Create an array of permission data for each permission
        $permissionData = collect($uniquePermissions)->map(function ($permission) {
            return [
                'name'       => $permission,
                'guard_name' => 'web',
            ];
        });

        DB::table($this->spatieTableNames['permissions'])->insert($permissionData->toArray());
    }

    /**
     * Insert all role permissions connections.
     *
     * @return void
     */
    private function insertRolePermissions(): void
    {
        $presentRoles = Role::all()->pluck('id', 'name')->toArray();
        $presentPermissions = Permission::all()->pluck('id', 'name')->toArray();

        $rolePermissions = [];
        foreach ($this->rolesAndPermissions as $roleName => $permissions) {
            $roleId = $presentRoles[$roleName];

            foreach ($permissions as $permissionName) {
                $permissionId = $presentPermissions[$permissionName];

                $rolePermissions[] = [
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                ];
            }
        }

        DB::table($this->spatieTableNames['role_has_permissions'])->insert($rolePermissions);
    }
}
