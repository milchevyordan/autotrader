<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\VehicleModelSeeder;
use Database\Seeders\Vehicles\PreOrderVehicleSeeder;
use Database\Seeders\Vehicles\ServiceVehicleSeeder;
use Database\Seeders\Vehicles\SystemVehicleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends BaseSeeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->clearDatabase();

        $this->call([
            RolePermissionSeeder::class,
            RootUserSeeder::class,
            CompanySeeder::class,
            UserSeeder::class,
            MakeSeeder::class,
            VehicleModelSeeder::class,
            EngineSeeder::class,
            VariantSeeder::class,
            BpmSeeder::class,
            ConfigurationSeeder::class,
            EmailTemplateSeeder::class,
            CrmUserSeeder::class,
            CompanyGroupSeeder::class,
            UserGroupSeeder::class,
            CrmDataSeeder::class,
            VehicleGroupSeeder::class,
            SystemVehicleSeeder::class,
            VehicleOwnershipSeeder::class,
            PreOrderVehicleSeeder::class,
            ServiceVehicleSeeder::class,
            ServiceVehicleOwnershipSeeder::class,
            ItemSeeder::class,
            ServiceLevelSeeder::class,
            PurchaseOrderSeeder::class,
            PurchaseOrderOwnershipSeeder::class,
            PreOrderSeeder::class,
            PreOrderOwnershipSeeder::class,
            SalesOrderSeeder::class,
            SalesOrderOwnershipSeeder::class,
            ServiceOrderSeeder::class,
            ServiceOrderOwnershipSeeder::class,
            TransportOrderSeeder::class,
            TransportOrderOwnershipSeeder::class,
            WorkOrderSeeder::class,
            WorkOrderOwnershipSeeder::class,
            DocumentSeeder::class,
            DocumentOwnershipSeeder::class,
            QuoteSeeder::class,
            QuoteOwnershipSeeder::class,
        ]);

        Artisan::call('optimize:clear');
    }

    /**
     * Truncate all tables data.
     *
     * @return void
     */
    private function clearDatabase(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->getAllTableNames() as $name) {
            if ('migrations' == $name) {
                continue;
            }
            DB::table($name)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Return an array of all tables in a database.
     *
     * @return array
     */
    private function getAllTableNames(): array
    {
        $tables = [];

        // Get all table names from the database
        $tablesRaw = DB::select('SHOW TABLES');

        // Extract table names from the result set
        foreach ($tablesRaw as $tableRaw) {
            foreach ($tableRaw as $tableName) {
                $tables[] = $tableName;
            }
        }

        return $tables;
    }
}
