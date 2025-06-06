<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Services\Files\FileManager;
use App\Services\Images\ImageManager as ImagesImageManager;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImageController extends Controller
{
    /**
     * Order images in a new way.
     *
     * @param  Request          $request
     * @return RedirectResponse
     */
    public function order(Request $request): RedirectResponse
    {
        try {
            ImagesImageManager::updateOrder($request->orderArray);

            return back()->with('success', __('Image order has been updated successfully.'));
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['exception' => $e]);

            return redirect()->back()->withErrors([__('Error updating image order.')]);
        }
    }

    /**
     * Delete image.
     *
     * @param                   $path
     * @return RedirectResponse
     */
    public function destroy($path): RedirectResponse
    {
        try {
            $image = Image::where('path', $path)->first();

            (new FileManager())->delete($image);

            $imageable = $image->imageable;
            $imageable->updateProfileImage();

            return back()->with('success', __('Image has been deleted successfully.'));
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);

            return back()->with('danger', __('Image was not deleted.'));
        }
    }
}
