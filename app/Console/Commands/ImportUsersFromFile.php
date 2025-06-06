<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Imports\UserImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportUsersFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-users-from-file {file} {creatorId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from a given file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fileName = $this->argument('file');
        $creatorId = $this->argument('creatorId');

        $filePath = Storage::path("imports/$fileName.csv");

        $creator = User::where('id', $creatorId)->first();

        if (! $creator) {
            $this->error("Creator with ID $creatorId not found.");

            return;
        }

        if (! $this->confirm("Are you sure you want to import users from '{$fileName}' with creator: #{$creator->id} - {$creator->full_name}?")) {
            $this->error('Command aborted.');

            return;
        }

        $userImportService = new UserImportService();

        $userImportService->importFromCsv($filePath, $creator);

        $this->info('Import completed successfully.');
    }
}
