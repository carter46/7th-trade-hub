<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KycController extends Controller
{
    public function show(): View
    {
        $submission = auth()->user()->kycSubmissions()->latest()->first();

        return view('dashboard.user.kyc', [
            'submission' => $submission,
            'kycLevel' => auth()->user()->kyc_level,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'document_type' => ['required', 'string', 'max:50'],
            'document_number' => ['required', 'string', 'max:100'],
        ]);

        $user = auth()->user();

        if ($user->kyc_level >= 1) {
            return back()->with('status', __('KYC Level 1 already approved.'));
        }

        KycSubmission::create([
            'user_id' => $user->id,
            'level_requested' => 1,
            'documents' => [
                'type' => $request->document_type,
                'number' => $request->document_number,
            ],
            'status' => 'pending',
        ]);

        return back()->with('status', __('KYC submission received. We will review it shortly.'));
    }
}
