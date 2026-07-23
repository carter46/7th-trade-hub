<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AdminNotification::query()
            ->orderByDesc('created_at')
            ->paginate(30);

        $unreadCount = AdminNotification::query()->whereNull('read_at')->count();

        return view('dashboard.admin.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(AdminNotification $notification): RedirectResponse
    {
        $notification->markAsRead();

        if ($notification->action_url) {
            return redirect()->to($notification->action_url);
        }

        return back()->with('status', __('Notification marked as read.'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        AdminNotification::query()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', __('All notifications marked as read.'));
    }
}
