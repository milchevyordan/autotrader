<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

class ValidatePhpUploadLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maxFileUploads = (int) ini_get('max_file_uploads');

        $files = $request->allFiles();
        $flattenedFiles = $this->flattenFiles($files);
        $fileCount = count($flattenedFiles);

        if ($fileCount >= $maxFileUploads) {
            abort(403, __('Max upload files reached, max. allowed files are: '.$maxFileUploads - 1));
        }

        return $next($request);
    }

    protected function convertToBytes(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $bytes = (int) $size;

        switch ($unit) {
            case 'g':
                $bytes *= 1024;
            case 'm':
                $bytes *= 1024;
            case 'k':
                $bytes *= 1024;
        }

        return $bytes;
    }

    protected function flattenFiles(array $files): array
    {
        $flattened = [];

        foreach ($files as $file) {
            if (is_array($file)) {
                $flattened = array_merge($flattened, $this->flattenFiles($file));
            } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                $flattened[] = $file;
            }
        }

        return $flattened;
    }
}
