<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * @param  Request          $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $request['email'] = auth()->user()->email;

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::min(8)->mixedCase()->symbols()->letters()->numbers()->uncompromised(), 'confirmed', 'different:current_password', 'different:email'],
        ]);

        $user = $request->user();

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back();
    }
}
