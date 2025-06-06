<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class AddPermission extends Command
{
    protected $signature = 'app:add-permission {name?} {--a|all} {--guard=web}';

    protected $description = 'Add one or many permissions for a resource';

    public function handle()
    {
        $name = $this->argument('name') ?? $this->ask('Enter the permission name ( <create-report> OR if using optional argument <-a> singular, e.g. <report> -a, )');
        $addAll = $this->option('all');
        $guard = $this->option('guard') ?? 'web';

        if (empty($name)) {
            $this->error('You must provide a resource name.');

            return 1;
        }

        $actions = $addAll
            ? [
                'view-any-{name}',
                'view-{name}',
                'create-{name}',
                'update-{name}',
                'delete-{name}',
                'restore-{name}',
                'force-delete-{name}',
            ]
            : ['{name}'];

        $permissionsCreated = 0;

        $permissions = new Collection();
        foreach ($actions as $action) {
            $permissionName = str_replace('{name}', strtolower($name), $action);

            if (Permission::where('name', $permissionName)->where('guard_name', $guard)->exists()) {
                $this->error("Permission '{$permissionName}' already exists for guard '{$guard}'.");
                continue;
            }

            $permission = new Permission();
            $permission->name = $permissionName;
            $permission->guard_name = $guard;

            $permissions->push($permission);

            $this->info("Permission '{$permissionName}' created with guard '{$guard}'.");
            $permissionsCreated++;
        }

        Permission::insert($permissions->toArray());

        $this->info("Done. {$permissionsCreated} permission(s) created.");

        return 0;
    }
}
