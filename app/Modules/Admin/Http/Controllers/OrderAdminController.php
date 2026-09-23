<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DomainRegistration;
use App\Models\Order;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\SystemSetting;
use App\Models\User;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Admin\Services\FinancialAuditLog;
use App\Modules\Catalog\Services\PlatformCheckoutService;
use App\Modules\Wallet\Services\WalletService;
use App\Services\Domains\DomainRegistrationFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class OrderAdminController extends Controller
{
    public function __construct(
        private PlatformCheckoutService $checkout,
        private FinancialAuditLog $financialAudit,
        private WalletService $walletService,
        private DomainRegistrationFulfillmentService $domainFulfillment,
        private AuditLogService $audit,
    ) {}

    public function index(Request $request): View
    {
        $query = Order::query()
            ->with(['user', 'items'])
            ->where('source', 'platform')
            ->orderByDesc('created_at');

        if ($request->string('filter')->toString() === 'awaiting_bank') {
            $query->where('payment_method', Order::PAYMENT_MANUAL_BANK_TRANSFER)
                ->where('status', 'pending');
        } elseif ($request->string('filter')->toString() === 'failed_bank') {
            $query->where('payment_method', Order::PAYMENT_MANUAL_BANK_TRANSFER)
                ->where('status', 'cancelled');
        } elseif ($request->string('filter')->toString() === 'pending_manual_domains') {
            $query->whereHas('domainRegistrations', function ($q) {
                $q->where('status', DomainRegistration::STATUS_PENDING_MANUAL);
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('dashboard.admin.orders.index', [
            'orders' => $orders,
            'filter' => $request->string('filter')->toString(),
        ]);
    }

    public function show(Order $order): View
    {
        $this->assertPlatformOrder($order);
        $order->load(['user', 'items.variant.product', 'paymentConfirmer', 'domainRegistrations']);

        $meta = $order->payment_metadata ?? [];
        $proofPath = $meta['proof_path'] ?? null;
        $proofDisk = $meta['proof_disk'] ?? config('media.documents.disk', 'local');
        $proofMime = null;
        if ($proofPath && Storage::disk($proofDisk)->exists($proofPath)) {
            $proofMime = Storage::disk($proofDisk)->mimeType($proofPath) ?: null;
        }

        return view('dashboard.admin.orders.show', [
            'order' => $order,
            'bankDetails' => SystemSetting::manualBankTransferDetails(),
            'pendingManualDomains' => $order->domainRegistrations
                ->where('status', DomainRegistration::STATUS_PENDING_MANUAL)
                ->values(),
            'proofMime' => $proofMime,
            'proofIsImage' => is_string($proofMime) && str_starts_with($proofMime, 'image/'),
            'proofIsPdf' => $proofMime === 'application/pdf',
        ]);
    }

    public function markDomainRegistered(Request $request, Order $order, DomainRegistration $registration): RedirectResponse
    {
        $this->assertPlatformOrder($order);
        abort_unless((int) $registration->order_id === (int) $order->id, 404);

        $data = $request->validate([
            'provider_reference' => ['nullable', 'string', 'max:191'],
            'nameservers' => ['nullable', 'string', 'max:1000'],
        ]);

        $nameservers = null;
        if (filled($data['nameservers'] ?? null)) {
            $nameservers = preg_split('/[\s,;]+/', (string) $data['nameservers'], -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        try {
            $updated = $this->domainFulfillment->markManualRegistered(
                $registration,
                $data['provider_reference'] ?? null,
                $nameservers,
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log(
            $request->user()?->id,
            'domains.manual_registered',
            $updated,
            ['status' => DomainRegistration::STATUS_PENDING_MANUAL],
            ['status' => $updated->status, 'fqdn' => $updated->fqdn, 'order_id' => $order->id],
            $request->ip(),
        );

        return back()->with('status', 'Marked '.$updated->fqdn.' as registered.');
    }

    public function create(): View
    {
        $products = PlatformProduct::query()
            ->visibleToPublic()
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'product_type']);

        $users = User::query()
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email']);

        return view('dashboard.admin.orders.create', [
            'products' => $products,
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_slug' => ['required', 'string', 'max:255'],
            'variant_id' => ['nullable', 'integer', 'exists:platform_product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'mark_paid' => ['nullable', 'boolean'],
        ]);

        $product = PlatformProduct::query()
            ->visibleToPublic()
            ->where('slug', $validated['product_slug'])
            ->firstOrFail();

        if (! empty($validated['variant_id'])) {
            PlatformProductVariant::query()
                ->whereKey($validated['variant_id'])
                ->where('platform_product_id', $product->id)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $user = User::query()->findOrFail((int) $validated['user_id']);

        $data = [
            'variant_id' => $validated['variant_id'] ?? null,
            'quantity' => (int) $validated['quantity'],
            'domain_mode' => 'none',
            'idempotency_key' => (string) Str::uuid(),
            'payment_method' => Order::PAYMENT_MANUAL_BANK_TRANSFER,
        ];

        try {
            $order = $this->checkout->createManualBankTransferOrderForUser(
                $user,
                $product,
                $data,
                $request->boolean('mark_paid'),
                (int) auth()->id(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->boolean('mark_paid')) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('status', __('Order created and marked paid. Reference: :ref', ['ref' => $order->reference]));
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', __('Pending order created. Reference: :ref', ['ref' => $order->reference]));
    }

    public function confirmManualPayment(Order $order, Request $request): RedirectResponse
    {
        $this->assertPlatformOrder($order);

        $platformBefore = $this->walletService->getPlatformWallet()->replicate();

        try {
            $this->checkout->fulfillPaidCatalogOrder(
                $order,
                [Order::PAYMENT_MANUAL_BANK_TRANSFER],
                (int) auth()->id(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $order->refresh();
        $platformAfter = $this->walletService->getPlatformWallet()->fresh();

        $this->financialAudit->logMoneyAction(
            auth()->id(),
            'order.manual_payment.confirmed',
            $order,
            $platformBefore,
            $platformAfter,
            $request->ip(),
            $request->userAgent(),
            $request->header('X-Request-Id'),
            ['order_id' => $order->id, 'reference' => $order->reference],
        );

        return back()->with('status', __('Payment confirmed and order fulfilled.'));
    }

    public function rejectManualPayment(Order $order, Request $request): RedirectResponse
    {
        $this->assertPlatformOrder($order);

        try {
            $this->checkout->cancelManualBankTransferOrder($order, $request->input('notes'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.orders')
            ->with('status', __('Order cancelled.'));
    }

    public function downloadPaymentProof(Request $request, Order $order): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
    {
        $this->assertPlatformOrder($order);

        if ($order->payment_method !== Order::PAYMENT_MANUAL_BANK_TRANSFER) {
            return back()->with('error', __('This order has no manual bank transfer proof.'));
        }

        $meta = $order->payment_metadata ?? [];
        $path = $meta['proof_path'] ?? null;
        $disk = $meta['proof_disk'] ?? config('media.documents.disk', 'local');

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            return back()->with('error', __('Payment proof not found.'));
        }

        $filename = 'order-proof-'.$order->reference;
        $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';

        if ($request->boolean('inline')) {
            return Storage::disk($disk)->response($path, $filename, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        return Storage::disk($disk)->download($path, $filename);
    }

    private function assertPlatformOrder(Order $order): void
    {
        if ($order->source !== 'platform') {
            abort(404);
        }
    }
}
