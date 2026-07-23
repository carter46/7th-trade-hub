<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('admin')->orderByDesc('created_at');

        if ($action = $request->string('action')->toString()) {
            $query->where('action', 'like', '%'.$action.'%');
        }

        if ($module = $request->string('module')->toString()) {
            $query->where('module', $module);
        }

        if ($adminId = $request->integer('admin_id') ?: null) {
            $query->where('admin_id', $adminId);
        }

        if ($from = $request->string('date_from')->toString()) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->string('date_to')->toString()) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(30)->withQueryString();

        $admins = User::role('admin')->orderBy('name')->get(['id', 'name', 'email']);
        $modules = AuditLog::query()
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        return view('dashboard.admin.audit-logs', [
            'logs' => $logs,
            'admins' => $admins,
            'modules' => $modules,
            'filters' => [
                'action' => $request->string('action')->toString(),
                'module' => $request->string('module')->toString(),
                'admin_id' => $request->integer('admin_id') ?: null,
                'date_from' => $request->string('date_from')->toString(),
                'date_to' => $request->string('date_to')->toString(),
            ],
        ]);
    }
}
