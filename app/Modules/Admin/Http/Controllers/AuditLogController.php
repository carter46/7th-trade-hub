<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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

        $logs = $query->paginate(30)->withQueryString();

        return view('dashboard.admin.audit-logs', compact('logs'));
    }
}
