<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Escrow;
use App\Models\User;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'active';
        if (! in_array($status, ['active', 'suspended'], true)) {
            $status = 'active';
        }

        $base = User::role('user');

        $activeCount = (clone $base)->where('is_suspended', false)->count();
        $suspendedCount = (clone $base)->where('is_suspended', true)->count();

        $users = User::role('user')
            ->when($status === 'suspended', fn ($q) => $q->where('is_suspended', true))
            ->when($status === 'active', fn ($q) => $q->where('is_suspended', false))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.admin.users', [
            'users' => $users,
            'status' => $status,
            'activeCount' => $activeCount,
            'suspendedCount' => $suspendedCount,
        ]);
    }

    public function show(User $user): View
    {
        $this->ensureMember($user);

        $user->load('wallet');

        return view('dashboard.admin.users.show', [
            'user' => $user,
            'activeTab' => 'overview',
            'wallet' => $user->wallet,
            'recentTransactions' => $user->transactions()->orderByDesc('created_at')->limit(5)->get(),
            'orderCount' => $user->orders()->count(),
            'listingCount' => $user->listings()->count(),
            'ticketCount' => $user->supportTickets()->count(),
        ]);
    }

    public function wallet(User $user): View
    {
        $this->ensureMember($user);
        $user->load('wallet');

        return view('dashboard.admin.users.show', [
            'user' => $user,
            'activeTab' => 'wallet',
            'wallet' => $user->wallet,
        ]);
    }

    public function transactions(User $user): View
    {
        $this->ensureMember($user);

        return view('dashboard.admin.users.show', [
            'user' => $user,
            'activeTab' => 'transactions',
            'transactions' => $user->transactions()->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function orders(User $user): View
    {
        $this->ensureMember($user);

        return view('dashboard.admin.users.show', [
            'user' => $user,
            'activeTab' => 'orders',
            'orders' => $user->orders()->with('listing')->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function listings(User $user): View
    {
        $this->ensureMember($user);

        return view('dashboard.admin.users.show', [
            'user' => $user,
            'activeTab' => 'listings',
            'listings' => $user->listings()->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function escrows(User $user): View
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

        return view('dashboard.admin.users.show', [
            'user' => $user,
            'activeTab' => 'escrows',
            'escrows' => $escrows,
        ]);
    }

    public function tickets(User $user): View
    {
        $this->ensureMember($user);

        return view('dashboard.admin.users.show', [
            'user' => $user,
            'activeTab' => 'tickets',
            'tickets' => $user->supportTickets()->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function activity(User $user): View
    {
        $this->ensureMember($user);

        $activity = AuditLog::query()
            ->where(function ($q) use ($user) {
                $q->where('model_type', User::class)->where('model_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.users.show', [
            'user' => $user,
            'activeTab' => 'activity',
            'activity' => $activity,
        ]);
    }

    public function security(User $user): View
    {
        $this->ensureMember($user);

        return view('dashboard.admin.users.show', [
            'user' => $user,
            'activeTab' => 'security',
        ]);
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
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'size:2'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $old = $user->only(['name', 'username', 'email', 'phone', 'country', 'bio']);

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->audit->log(auth()->id(), 'user.updated', $user, $old, $user->only([
            'name', 'username', 'email', 'phone', 'country', 'bio',
        ]), $request->ip());

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', __('User profile updated.'));
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
}
