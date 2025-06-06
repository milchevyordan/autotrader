<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('translations:clear-cache', function () {
    App\Helpers\Translator::clearCache();
    $this->info('Cache cleared');
})->purpose('Clear all translations cache');

Artisan::command('translate {locale}', function (string $locale) {
    App\Helpers\Translator::translate($locale);
    $this->info("Make {$locale}.json in lang dir.");
})->purpose('Creates an json file with all the keys to translate the corresponding locale from English.');

Artisan::command('generate-ts:enums', function () {
    collect(File::allFiles(base_path('app/Enums')))
        ->filter(function (SplFileInfo $file) {
            return 'php' == $file->getExtension();
        })->map(function (SplFileInfo $file) {
            $class = 'App\\Enums\\'.$file->getBasename('.php');
            $name = class_basename($class);
            $ts = $class::toTS();
            $filePath = "js/Enums/{$name}.ts";
            ! File::put(resource_path($filePath), $ts) ? $this->line("<error>[ERROR]</error> Could not generate {$filePath}") : $this->line("<info>[OK]</info> {$filePath}");
        });
})->purpose('Generate TypeScript Enums.');
