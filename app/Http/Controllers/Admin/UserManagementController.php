<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\AuditLog;
use App\Models\Escrow;
use App\Models\User;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private WalletProvisioningService $walletProvisioning,
    ) {}

    public function create(): View
    {
        return view('dashboard.admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'country' => isset($data['country']) ? strtoupper($data['country']) : null,
            'bio' => $data['bio'] ?? null,
            'terms_accepted_at' => now(),
        ]);

        if ($request->boolean('email_verified')) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->assignRole('user');

        if (isset($data['kyc_level'])) {
            $user->forceFill(['kyc_level' => (int) $data['kyc_level']])->save();
        }

        if ($request->boolean('is_suspended')) {
            $user->suspend(auth()->id());
        }

        $walletCreated = false;
        if ($request->boolean('provision_wallet')) {
            try {
                $this->walletProvisioning->createWallet($user->fresh());
                $walletCreated = true;
            } catch (\Throwable $e) {
                $this->audit->log(auth()->id(), 'user.created', $user, null, [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'wallet_error' => $e->getMessage(),
                ], $request->ip());

                return redirect()
                    ->route('admin.users.show', $user)
                    ->with('status', __('User created, but wallet was not provisioned: :error', ['error' => $e->getMessage()]));
            }
        }

        $this->audit->log(auth()->id(), 'user.created', $user, null, [
            'user_id' => $user->id,
            'email' => $user->email,
            'email_verified' => $request->boolean('email_verified'),
            'wallet_created' => $walletCreated,
            'kyc_level' => $user->fresh()->kyc_level,
        ], $request->ip());

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', __('User created.'));
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'active';
        if (! in_array($status, ['active', 'suspended'], true)) {
            $status = 'active';
        }

        $base = User::role('user');

        $activeCount = (clone $base)->where('is_suspended', false)->count();
        $suspendedCount = (clone $base)->where('is_suspended', true)->count();

        $search = trim($request->string('q')->toString());

        $users = User::role('user')
            ->when($status === 'suspended', fn ($q) => $q->where('is_suspended', true))
            ->when($status === 'active', fn ($q) => $q->where('is_suspended', false))
            ->when($search !== '', fn ($q) => \App\Support\Search::apply($q, $search))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        $data = [
            'users' => $users,
            'status' => $status,
            'activeCount' => $activeCount,
            'suspendedCount' => $suspendedCount,
            'search' => $search,
        ];

        if ($this->wantsTabPartial($request)) {
            return view('dashboard.admin.users._table', $data);
        }

        return view('dashboard.admin.users', $data);
    }

    public function show(User $user, Request $request): View
    {
        $this->ensureMember($user);

        $user->load('wallet');

        return $this->userTabView($request, $user, 'overview', [
            'wallet' => $user->wallet,
            'recentTransactions' => $user->transactions()->orderByDesc('created_at')->limit(5)->get(),
            'orderCount' => $user->orders()->count(),
            'listingCount' => $user->listings()->count(),
            'ticketCount' => $user->supportTickets()->count(),
        ]);
    }

    public function wallet(User $user, Request $request): View
    {
        $this->ensureMember($user);
        $user->load('wallet');

        return $this->userTabView($request, $user, 'wallet', [
            'wallet' => $user->wallet,
        ]);
    }

    public function transactions(User $user, Request $request): View
    {
        $this->ensureMember($user);

        return $this->userTabView($request, $user, 'transactions', [
            'transactions' => $user->transactions()->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function orders(User $user, Request $request): View
    {
        $this->ensureMember($user);

        return $this->userTabView($request, $user, 'orders', [
            'orders' => $user->orders()->with('listing')->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function listings(User $user, Request $request): View
    {
        $this->ensureMember($user);

        return $this->userTabView($request, $user, 'listings', [
            'listings' => $user->listings()->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function escrows(User $user, Request $request): View
    {
        $this->ensureMember($user);

        $walletIds = $user->wallet()->pluck('id');

        $escrows = Escrow::query()
            ->where(function ($q) use ($user, $walletIds) {
                $q->whereHas('order', fn ($order) => $order->where('user_id', $user->id));
                if ($walletIds->isNotEmpty()) {
                    $q->orWhereIn('buyer_wallet_id', $walletIds)
                        ->orWhereIn('seller_wallet_id', $walletIds);
                }
            })
            ->with('order')
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->userTabView($request, $user, 'escrows', [
            'escrows' => $escrows,
        ]);
    }

    public function tickets(User $user, Request $request): View
    {
        $this->ensureMember($user);

        return $this->userTabView($request, $user, 'tickets', [
            'tickets' => $user->supportTickets()->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function activity(User $user, Request $request): View
    {
        $this->ensureMember($user);

        $activity = AuditLog::query()
            ->where(function ($q) use ($user) {
                $q->where('model_type', User::class)->where('model_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->userTabView($request, $user, 'activity', [
            'activity' => $activity,
        ]);
    }

    public function security(User $user, Request $request): View
    {
        $this->ensureMember($user);

        return $this->userTabView($request, $user, 'security');
    }

    public function edit(User $user): View
    {
        $this->ensureMember($user);

        return view('dashboard.admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureMember($user);

        if ($user->anonymized_at !== null) {
            return back()->with('error', __('This account has been permanently deleted and cannot be edited.'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'size:2'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'kyc_level' => ['nullable', 'integer', 'min:0', 'max:4'],
        ]);

        if (isset($data['country'])) {
            $data['country'] = strtoupper($data['country']);
        }

        $old = $user->only(['name', 'username', 'email', 'phone', 'country', 'bio', 'kyc_level']);

        $user->fill(collect($data)->except('kyc_level')->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if (array_key_exists('kyc_level', $data) && $data['kyc_level'] !== null) {
            $user->forceFill(['kyc_level' => (int) $data['kyc_level']])->save();
        }

        $this->audit->log(auth()->id(), 'user.updated', $user, $old, $user->only([
            'name', 'username', 'email', 'phone', 'country', 'bio', 'kyc_level',
        ]), $request->ip());

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', __('User profile updated.'));
    }

    public function sendPasswordReset(Request $request, User $user): RedirectResponse
    {
        $this->ensureMember($user);

        if ($user->anonymized_at !== null) {
            return back()->with('error', __('Cannot reset password for a deleted account.'));
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        $this->audit->log(auth()->id(), 'user.password_reset_link_sent', $user, null, [
            'user_id' => $user->id,
            'status' => $status,
        ], $request->ip());

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->with('error', __($status));
        }

        return back()->with('status', __('Password reset link sent to :email.', ['email' => $user->email]));
    }

    public function verifyEmail(Request $request, User $user): RedirectResponse
    {
        $this->ensureMember($user);

        if ($user->anonymized_at !== null) {
            return back()->with('error', __('Cannot verify a deleted account.'));
        }

        $user->forceFill(['email_verified_at' => now()])->save();

        $this->audit->log(auth()->id(), 'user.email_verified', $user, null, [
            'user_id' => $user->id,
        ], $request->ip());

        return back()->with('status', __('Email marked as verified.'));
    }

    public function unverifyEmail(Request $request, User $user): RedirectResponse
    {
        $this->ensureMember($user);

        if ($user->anonymized_at !== null) {
            return back()->with('error', __('Cannot modify a deleted account.'));
        }

        $user->forceFill(['email_verified_at' => null])->save();

        $this->audit->log(auth()->id(), 'user.email_unverified', $user, null, [
            'user_id' => $user->id,
        ], $request->ip());

        return back()->with('status', __('Email marked as unverified.'));
    }

    public function provisionWallet(Request $request, User $user): RedirectResponse
    {
        $this->ensureMember($user);

        if ($user->anonymized_at !== null) {
            return back()->with('error', __('Cannot provision a wallet for a deleted account.'));
        }

        if ($user->wallet) {
            return back()->with('error', __('User already has a wallet.'));
        }

        try {
            $this->walletProvisioning->createWallet($user);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log(auth()->id(), 'user.wallet_provisioned', $user, null, [
            'user_id' => $user->id,
            'wallet_id' => $user->fresh()->wallet?->id,
        ], $request->ip());

        return back()->with('status', __('Wallet provisioned.'));
    }

    public function suspend(User $user, Request $request): RedirectResponse
    {
        $this->ensureMember($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', __('You cannot suspend your own account.'));
        }

        if ($user->anonymized_at !== null) {
            return back()->with('error', __('This account has been permanently deleted.'));
        }

        $user->suspend(auth()->id());

        $this->audit->log(auth()->id(), 'user.suspended', $user, null, [
            'user_id' => $user->id,
            'is_suspended' => true,
        ], $request->ip());

        return back()->with('status', __('User suspended.'));
    }

    public function restore(User $user, Request $request): RedirectResponse
    {
        $this->ensureMember($user);

        if ($user->anonymized_at !== null) {
            return back()->with('error', __('Anonymized accounts cannot be restored.'));
        }

        $user->restoreAccess();

        $this->audit->log(auth()->id(), 'user.restored', $user, null, [
            'user_id' => $user->id,
            'is_suspended' => false,
        ], $request->ip());

        return back()->with('status', __('User restored.'));
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        $this->ensureMember($user);

        if ($user->hasRole('admin')) {
            abort(403, 'Administrators cannot be permanently deleted.');
        }

        if ($user->anonymized_at !== null) {
            return back()->with('error', __('This account is already permanently deleted.'));
        }

        if (! $user->is_suspended) {
            return back()->with('error', __('Suspend the user before permanently deleting.'));
        }

        $oldEmail = $user->email;
        $user->anonymize(auth()->id());

        $this->audit->log(auth()->id(), 'user.anonymized', $user, [
            'email' => $oldEmail,
        ], [
            'user_id' => $user->id,
            'anonymized_at' => optional($user->fresh())->anonymized_at?->toIso8601String(),
        ], $request->ip());

        return redirect()
            ->route('admin.users', ['status' => 'suspended'])
            ->with('status', __('User permanently deleted (anonymized).'));
    }

    /**
     * Legacy role assignment endpoint — role changes belong on Administrators.
     */
    public function assignRole(User $user, Request $request): RedirectResponse
    {
        abort(403, 'Role assignment is managed from Administrators.');
    }

    private function ensureMember(User $user): void
    {
        abort_unless($user->hasRole('user') && ! $user->hasRole('admin'), 404);
    }

    private function wantsTabPartial(Request $request): bool
    {
        return $request->headers->get('X-Dashboard-Tab') === '1';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function userTabView(Request $request, User $user, string $activeTab, array $data = []): View
    {
        $payload = array_merge($data, [
            'user' => $user,
            'activeTab' => $activeTab,
        ]);

        if ($this->wantsTabPartial($request)) {
            return view('dashboard.admin.users.show-panel', $payload);
        }

        return view('dashboard.admin.users.show', $payload);
    }
}
