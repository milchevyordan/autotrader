<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Imports\CompanyImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportCompaniesFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-companies-from-file {file} {creatorId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import companies from a given file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $fileName = $this->argument('file');
        $creatorId = $this->argument('creatorId');

        $filePath = Storage::path("imports/$fileName.csv");

        $creator = User::where('id', $creatorId)->first();

        if (! $creator) {
            $this->error("Creator with ID $creatorId not found.");

            return;
        }

        if (! $this->confirm("Are you sure you want to import companies from '{$fileName}' with creator: #{$creator->id} - {$creator->full_name}?")) {
            $this->error('Command aborted.');

            return;
        }

        $companyImportService = new CompanyImportService();

        $companyImportService->importFromCsv($filePath, $creator);

        $this->info('Import completed successfully.');
    }
}
