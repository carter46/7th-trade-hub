<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketAdminController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(): View
    {
        $tickets = SupportTicket::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.tickets', compact('tickets'));
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('manage', $ticket);

        $ticket->load(['user', 'replies.user']);

        return view('dashboard.admin.ticket-show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('manage', $ticket);

        $validated = $request->validate(['body' => ['required', 'string']]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'is_staff' => true,
        ]);

        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        $this->audit->log(auth()->id(), 'support.replied', $ticket, null, ['body' => $validated['body']], $request->ip());

        return back()->with('status', __('Staff reply sent.'));
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('manage', $ticket);

        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);

        $old = $ticket->status;
        $ticket->update(['status' => $validated['status']]);

        $this->audit->log(
            auth()->id(),
            'support.status_updated',
            $ticket,
            ['status' => $old],
            ['status' => $validated['status']],
            $request->ip()
        );

        return back()->with('status', __('Ticket status updated.'));
    }
}
