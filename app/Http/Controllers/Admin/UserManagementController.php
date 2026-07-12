<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.admin.users', [
            'users' => $users,
        ]);
    }
}
