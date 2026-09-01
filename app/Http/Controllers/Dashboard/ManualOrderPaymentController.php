<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SystemSetting;
use App\Modules\Catalog\Services\PlatformCheckoutService;
use App\Services\Media\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManualOrderPaymentController extends Controller
{
    public function __construct(
        private PlatformCheckoutService $checkout,
        private MediaUploadService $media,
    ) {}

    public function show(Order $order): View|RedirectResponse
    {
        if (! $this->canAccessManualPayment($order)) {
            return $this->denyManualPayment($order);
        }

        return view('dashboard.user.orders.manual-payment', [
            'order' => $order,
            'bankDetails' => SystemSetting::manualBankTransferDetails(),
        ]);
    }

    public function submitProof(Order $order, Request $request): RedirectResponse
    {
        if (! $this->canAccessManualPayment($order)) {
            return $this->denyManualPayment($order);
        }

        $validated = $request->validate([
            'payer_bank_name' => ['required', 'string', 'max:100'],
            'transfer_reference' => ['required', 'string', 'max:100'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $proofMeta = [
            'payer_bank_name' => $validated['payer_bank_name'],
            'transfer_reference' => $validated['transfer_reference'],
        ];

        if ($request->hasFile('proof')) {
            $stored = $this->media->storeDocument($request->file('proof'), $request->user());
            $proofMeta['proof_path'] = $stored['path'];
            $proofMeta['proof_disk'] = $stored['disk'];
            $proofMeta['proof_media_asset_id'] = $stored['media_asset_id'];
        }

        try {
            $this->checkout->submitManualBankTransferProof($order, $proofMeta);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('status', __('Payment details submitted. We will confirm your transfer shortly.'));
    }

    private function canAccessManualPayment(Order $order): bool
    {
        return (int) $order->user_id === (int) auth()->id()
            && $order->source === 'platform'
            && $order->payment_method === Order::PAYMENT_MANUAL_BANK_TRANSFER
            && $order->status === 'pending';
    }

    private function denyManualPayment(Order $order): RedirectResponse
    {
        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }

        if ($order->payment_method !== Order::PAYMENT_MANUAL_BANK_TRANSFER) {
            return redirect()->route('dashboard.service-orders')
                ->with('error', __('This order does not use manual bank transfer.'));
        }

        if ($order->status === 'paid') {
            return redirect()->route('dashboard.service-orders')
                ->with('status', __('Order :ref is already paid.', ['ref' => $order->reference]));
        }

        if ($order->status === 'cancelled') {
            return redirect()->route('dashboard.service-orders')
                ->with('error', __('This order was cancelled.'));
        }

        abort(404);
    }
}
