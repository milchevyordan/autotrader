<?php

declare(strict_types=1);

namespace Database\Seeders\Workflow;

use Database\Seeders\BaseSeeder;
use Illuminate\Support\Facades\File;
use SplFileInfo;

class WorkflowAllCompanyProcessesSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        collect(File::allFiles(base_path('database/seeders/Workflow/Companies')))
            ->filter(function (SplFileInfo $file) {
                return 'php' == $file->getExtension();
            })->map(function (SplFileInfo $file) {
                $class = 'Database\\Seeders\\Workflow\\Companies\\'.$file->getBasename('.php');

                (new $class())->run();
            });
    }
}
