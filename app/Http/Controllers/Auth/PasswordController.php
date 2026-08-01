<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update or set the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $passwordIsSet = $user->hasPasswordSet();

        if ($passwordIsSet) {
            $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);
        } else {
            $validated = $request->validateWithBag('updatePassword', [
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'password_set_at' => now(),
        ])->save();

        return back()->with('status', $passwordIsSet ? 'password-updated' : 'password-set');
    }
}
