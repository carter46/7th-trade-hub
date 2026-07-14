<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Modules\Marketplace\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(): View
    {
        $userId = auth()->id();

        $messages = Message::query()
            ->where('to_user_id', $userId)
            ->orWhere('from_user_id', $userId)
            ->with(['fromUser', 'toUser'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.user.messages', compact('messages'));
    }

    public function create(): View
    {
        return view('dashboard.user.messages-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'to_email' => ['required', 'email', 'exists:users,email'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $recipient = User::where('email', $validated['to_email'])->firstOrFail();

        if ($recipient->id === auth()->id()) {
            return back()->withInput()->with('error', __('You cannot message yourself.'));
        }

        $message = Message::create([
            'from_user_id' => auth()->id(),
            'to_user_id' => $recipient->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'folder' => 'inbox',
        ]);

        $this->notifications->send(
            $recipient,
            'message',
            __('New message from :name', ['name' => auth()->user()->name]),
            $validated['subject'],
            route('dashboard.messages.show', $message)
        );

        return redirect()->route('dashboard.messages.show', $message)
            ->with('status', __('Message sent.'));
    }

    public function show(Message $message): View|RedirectResponse
    {
        if (! in_array(auth()->id(), [$message->from_user_id, $message->to_user_id], true)) {
            abort(403);
        }

        if ($message->to_user_id === auth()->id() && ! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return view('dashboard.user.messages-show', compact('message'));
    }

    public function reply(Request $request, Message $message): RedirectResponse
    {
        if (! in_array(auth()->id(), [$message->from_user_id, $message->to_user_id], true)) {
            abort(403);
        }

        $validated = $request->validate(['body' => ['required', 'string']]);

        $recipientId = auth()->id() === $message->from_user_id
            ? $message->to_user_id
            : $message->from_user_id;

        $reply = Message::create([
            'from_user_id' => auth()->id(),
            'to_user_id' => $recipientId,
            'order_id' => $message->order_id,
            'subject' => 'Re: '.$message->subject,
            'body' => $validated['body'],
            'folder' => 'inbox',
        ]);

        $recipient = User::findOrFail($recipientId);
        $this->notifications->send(
            $recipient,
            'message',
            __('New reply from :name', ['name' => auth()->user()->name]),
            $reply->subject,
            route('dashboard.messages.show', $reply)
        );

        return redirect()->route('dashboard.messages.show', $reply)
            ->with('status', __('Reply sent.'));
    }
}
