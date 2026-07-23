<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserVerified;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($request->user()->homeRoute().'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
            UserVerified::dispatch((int) $request->user()->id);
        }

        return redirect()->intended($request->user()->homeRoute().'?verified=1');
    }
}
