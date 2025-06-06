<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdateProfileImageRequest;
use App\Services\Files\UploadHelper;
use App\Services\Images\Compressor\Compressor;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     *
     * @return Response
     */
    public function edit(): Response
    {
        $user = request()->user()->only(['first_name', 'prefix', 'last_name', 'email', 'mobile', 'other_phone', 'gender', 'images', 'language']);

        return Inertia::render('profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'user'            => $user,
            'images'          => request()->user()->getGroupedImages(['profileImages']),
        ]);
    }

    /**
     * Update the user's profile information.
     *
     * @param  ProfileUpdateRequest $request
     * @return RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     *
     * @param  Request          $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update user profile image.
     *
     * @param  UpdateProfileImageRequest $request
     * @return RedirectResponse
     */
    public function updateProfileImage(UpdateProfileImageRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();
            $compressor = (new Compressor())->setResizedImageWidth(500);
            $user->saveWithImages(UploadHelper::uploadMultipleImages($request->validated(), 'profileImages', $compressor), 'profileImages');

            return back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }
}
