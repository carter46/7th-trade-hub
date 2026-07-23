<?php

namespace App\Modules\Support\Http\Controllers;

use App\Events\TicketOpened;
use App\Events\TicketReplied;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(): View
    {
        $tickets = SupportTicket::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.user.support.index', compact('tickets'));
    }

    public function create(): View
    {
        return view('dashboard.user.support.create', [
            'categories' => SupportTicket::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'in:'.implode(',', SupportTicket::CATEGORIES)],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => 'open',
        ]);

        TicketOpened::dispatch($ticket->id, (int) auth()->id());

        return redirect()->route('dashboard.support.index')
            ->with('status', __('Support ticket created.'));
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load('replies.user');

        return view('dashboard.user.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('reply', $ticket);

        $validated = $request->validate(['body' => ['required', 'string']]);

        $isStaff = auth()->user()->hasRole('admin');

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'is_staff' => $isStaff,
        ]);

        TicketReplied::dispatch($ticket->id, (int) auth()->id(), $isStaff);

        return back()->with('status', __('Reply sent.'));
    }
}
