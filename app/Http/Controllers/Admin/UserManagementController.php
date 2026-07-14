<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(): View
    {
        $users = User::with('roles')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.admin.users', [
            'users' => $users,
            'roles' => Role::all(),
        ]);
    }

    public function suspend(User $user, Request $request): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('You cannot suspend your own account.'));
        }

        $user->update(['is_suspended' => ! $user->is_suspended]);
        $this->audit->log(auth()->id(), 'user.suspend_toggle', $user, null, $user->toArray(), $request->ip());

        return back()->with('status', __('User suspension updated.'));
    }

    public function assignRole(User $user, Request $request): RedirectResponse
    {
        if ($user->id === auth()->id() && $request->input('role') !== 'admin') {
            return back()->with('error', __('You cannot remove your own admin role.'));
        }

        $request->validate(['role' => ['required', 'in:admin,user']]);
        $user->syncRoles([$request->role]);
        $this->audit->log(auth()->id(), 'user.role_assigned', $user, null, ['role' => $request->role], $request->ip());

        return back()->with('status', __('Role updated.'));
    }
}
