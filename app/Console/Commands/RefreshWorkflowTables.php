<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshWorkflowTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop and recreate workflow tables and seed the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::statement('DROP TABLE IF EXISTS workflow_steps;');
        DB::statement('DROP TABLE IF EXISTS workflow_statuses;');
        DB::statement('DROP TABLE IF EXISTS workflow_subprocesses;');
        DB::statement('DROP TABLE IF EXISTS workflow_processes;');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $this->call('migrate:refresh', ['--path' => 'database/migrations/2023_12_13_074151_create_workflow_processes_table.php']);
        $this->call('migrate:refresh', ['--path' => 'database/migrations/2023_12_13_074201_create_workflow_subprocesses_table.php']);
        $this->call('migrate:refresh', ['--path' => 'database/migrations/2023_12_13_075514_create_workflow_statuses_table.php']);
        $this->call('migrate:refresh', ['--path' => 'database/migrations/2023_12_13_075516_create_workflow_steps_table.php']);
        $this->call('db:seed', ['--class' => 'WorkflowSeeder']);
    }
}
