<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Account\AccountController;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Legacy profile entry — keep admins inside the admin shell.
     */
    public function edit(Request $request): RedirectResponse
    {
        return Redirect::route(AccountController::routePrefix($request).'.account.profile');
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        return app(AccountController::class)->updateProfile($request);
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()->hasRole('admin')) {
            abort(403);
        }

        return app(AccountController::class)->destroy($request);
    }
}
