<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\Files\FileManager;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Throwable;

class FileController extends Controller
{
    /**
     * Order files in a new way.
     *
     * @param  Request          $request
     * @return RedirectResponse
     */
    public function fileOrder(Request $request): RedirectResponse
    {
        try {
            FileManager::updateOrder($request->orderArray);

            return back()->with('success', __('File order has been successfully updated.'));
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['exception' => $e]);

            return redirect()->back()->withErrors([__('Error updating file order.')]);
        }
    }

    /**
     * Download file locally.
     *
     * @param                                      $path
     * @return RedirectResponse|BinaryFileResponse
     */
    public function download($path): BinaryFileResponse|RedirectResponse
    {
        try {
            return (new FileManager())->downloadFile($path);
        } catch (FileNotFoundException $e) {
            Log::error($e->getMessage(), ['exception' => $e]);

            return back()->with('danger', __('File was not found.'));
        }
    }

    public function downloadAndDelete(Request $request)
    {
        try {
            $requestPath = $request->get('path');
            $decryptedPath = Crypt::decrypt($requestPath);
            $realPath = Storage::disk('public')->path($decryptedPath);

            return response()->download($realPath)->deleteFileAfterSend(true);
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return back()->with('danger', __('File was not found or could not be downloaded.'));
        }
    }

    public function downloadArchived(Request $request)
    {
        $files = (new Collection($request->get('files')))->map(function ($data) {
            return new File($data);
        });
        $fileManager = new FileManager();

        $zipPath = $fileManager->archive($files);

        return response()
            ->download($zipPath)
            ->deleteFileAfterSend(true);

    }

    /**
     * Delete file.
     *
     * @param                   $path
     * @return RedirectResponse
     */
    public function destroy($path): RedirectResponse
    {
        try {
            (new FileManager())->destroy($path);

            return redirect()->back()->with('success', __('File has been deleted successfully.'));
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);

            return redirect()->back()->withErrors([__('Error deleting record')]);
        }
    }
}
