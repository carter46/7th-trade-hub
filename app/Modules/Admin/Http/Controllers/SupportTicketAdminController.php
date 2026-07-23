<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Events\TicketReplied;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketAdminController extends Controller
{
    public const STATUSES = ['open', 'pending', 'awaiting_user', 'resolved', 'closed'];

    public function __construct(private AuditLogService $audit) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'open';
        if (! in_array($status, self::STATUSES, true)) {
            $status = 'open';
        }

        $search = trim($request->string('q')->toString());

        $query = SupportTicket::with(['user', 'assignee'])->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%');
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        } else {
            $query->where('status', $status);
        }

        $tickets = $query->paginate(20)->withQueryString();

        $counts = [];
        foreach (self::STATUSES as $s) {
            $counts[$s] = SupportTicket::where('status', $s)->count();
        }

        $data = compact('tickets', 'status', 'counts', 'search');

        if ($this->wantsTabPartial($request)) {
            return view('dashboard.admin.tickets._panel', $data);
        }

        return view('dashboard.admin.tickets', $data);
    }

    public function create(): View
    {
        $users = User::role('user')->orderBy('name')->limit(200)->get(['id', 'name', 'email']);

        return view('dashboard.admin.tickets.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'category' => ['required', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'priority' => ['nullable', 'string', 'max:20'],
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $validated['user_id'],
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'priority' => $validated['priority'] ?? 'normal',
            'status' => 'open',
            'assigned_to' => auth()->id(),
        ]);

        $this->audit->log(auth()->id(), 'support.created_for_user', $ticket, null, $ticket->toArray(), $request->ip());

        \App\Events\TicketOpened::dispatch($ticket->id, (int) $ticket->user_id);

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('status', __('Ticket opened on behalf of user.'));
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('manage', $ticket);

        $ticket->load(['user', 'replies.user', 'assignee']);
        $staff = User::role('admin')->orderBy('name')->get(['id', 'name', 'email']);

        return view('dashboard.admin.ticket-show', compact('ticket', 'staff'));
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

        if (in_array($ticket->status, ['closed', 'resolved'], true)) {
            $ticket->update(['status' => 'open']);
        } elseif ($ticket->status === 'open') {
            $ticket->update(['status' => 'awaiting_user']);
        }

        TicketReplied::dispatch($ticket->id, auth()->id(), true);

        $this->audit->log(auth()->id(), 'support.replied', $ticket, null, ['body' => $validated['body']], $request->ip());

        return back()->with('status', __('Staff reply sent.'));
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('manage', $ticket);

        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
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

    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('manage', $ticket);

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $old = $ticket->assigned_to;
        $ticket->update(['assigned_to' => $validated['assigned_to']]);

        $this->audit->log(
            auth()->id(),
            'support.assigned',
            $ticket,
            ['assigned_to' => $old],
            ['assigned_to' => $validated['assigned_to']],
            $request->ip()
        );

        return back()->with('status', __('Ticket assignment updated.'));
    }

    private function wantsTabPartial(Request $request): bool
    {
        return $request->header('X-Dashboard-Tab') === '1'
            || $request->boolean('partial');
    }
}
