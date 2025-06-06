<?php

declare(strict_types=1);

namespace App\Services\DataTable\Exports;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class Export
{
    abstract public function getStream(): string;

    abstract protected function getExtension(): string;

    public string $encryptedExportPath;

    protected UploadedFile $generatedFile;

    protected string $stream;

    protected string $extension;

    public function __construct(
        protected array $headers,
        protected array $rows
    ) {
    }

    /**
     * Generates file and set the file and the encryptedExportPath
     *
     * @return Export
     */
    public function generateFile(): self
    {
        $stream = $this->getStream();
        $uniqueFileName = 'export_'.Str::random(4).'_'.time().'.'.$this->getExtension();
        $relativePath = 'temp/'.$uniqueFileName;
        $stored = Storage::disk('public')->put($relativePath, $stream);

        if (! $stored) {

        }

        $fullPath = Storage::disk('public')->path($relativePath);
        $this->generatedFile = new UploadedFile($fullPath, $uniqueFileName, null, null, true);
        $this->encryptedExportPath = Crypt::encrypt($relativePath);

        return $this;
    }
}
