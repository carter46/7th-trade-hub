<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use App\Modules\Marketplace\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    /**
     * Escrow Conversations: marketplace escrows for buyer/seller.
     * Active (locked/disputed) first; closed threads remain reachable for history.
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();
        $filter = $request->string('status')->toString() ?: 'active';
        if (! in_array($filter, ['active', 'closed', 'all'], true)) {
            $filter = 'active';
        }

        $query = Order::query()
            ->where('source', 'marketplace')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('listing', fn ($l) => $l->withTrashed()->where('user_id', $userId));
            })
            ->whereHas('escrow')
            ->with(['listing' => fn ($q) => $q->withTrashed()->with('user'), 'user', 'escrow'])
            ->orderByDesc('updated_at');

        if ($filter === 'active') {
            $query->whereHas('escrow', fn ($e) => $e->whereIn('status', ['locked', 'disputed']));
        } elseif ($filter === 'closed') {
            $query->whereHas('escrow', fn ($e) => $e->whereNotIn('status', ['locked', 'disputed']));
        }

        $threads = $query->paginate(20)->withQueryString();

        return view('dashboard.user.messages', [
            'threads' => $threads,
            'filter' => $filter,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('dashboard.messages')
            ->with('error', __('Escrow conversations open automatically when a marketplace purchase starts. You cannot message users directly.'));
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('dashboard.messages')
            ->with('error', __('Direct messaging is disabled. Use Escrow Conversations on an active order.'));
    }

    public function showOrder(Order $order): View|RedirectResponse
    {
        $this->authorizeOrderParticipant($order);

        $order->load([
            'listing' => fn ($q) => $q->withTrashed()->with('user'),
            'user',
            'escrow',
        ]);

        $messages = Message::query()
            ->where('order_id', $order->id)
            ->with(['fromUser', 'toUser'])
            ->orderBy('created_at')
            ->get();

        Message::query()
            ->where('order_id', $order->id)
            ->where('to_user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $canReply = $this->escrowAllowsReply($order);
        $counterpart = $this->counterpart($order);
        $isSeller = (int) ($order->listing?->user_id) === (int) auth()->id();

        return view('dashboard.user.messages-show', [
            'order' => $order,
            'messages' => $messages,
            'canReply' => $canReply,
            'counterpart' => $counterpart,
            'isSeller' => $isSeller,
        ]);
    }

    public function replyOrder(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrderParticipant($order);

        if (! $this->escrowAllowsReply($order)) {
            return back()->with('error', __('This escrow conversation is closed.'));
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $counterpart = $this->counterpart($order);
        if (! $counterpart) {
            return back()->with('error', __('The other party is unavailable for this order.'));
        }

        $message = Message::create([
            'from_user_id' => auth()->id(),
            'to_user_id' => $counterpart->id,
            'order_id' => $order->id,
            'subject' => 'Order '.$order->reference,
            'body' => $validated['body'],
            'folder' => 'inbox',
        ]);

        $this->notifications->send(
            $counterpart,
            'message',
            __('New escrow message from :name', ['name' => auth()->user()->name]),
            $message->subject,
            route('dashboard.messages.order', $order)
        );

        return redirect()
            ->route('dashboard.messages.order', $order)
            ->with('status', __('Message sent.'));
    }

    /** Legacy deep links: redirect into the order thread when possible. */
    public function show(Message $message): RedirectResponse
    {
        if (! in_array(auth()->id(), [$message->from_user_id, $message->to_user_id], true)
            && ! auth()->user()?->hasRole('admin')
            && ! auth()->user()?->can('support.manage')) {
            abort(403);
        }

        if ($message->order_id) {
            return redirect()->route('dashboard.messages.order', $message->order_id);
        }

        return redirect()
            ->route('dashboard.messages')
            ->with('error', __('That message is not part of an escrow conversation.'));
    }

    public function reply(Request $request, Message $message): RedirectResponse
    {
        if (! $message->order_id) {
            return redirect()
                ->route('dashboard.messages')
                ->with('error', __('That conversation is closed.'));
        }

        return $this->replyOrder($request, Order::findOrFail($message->order_id));
    }

    private function authorizeOrderParticipant(Order $order): void
    {
        $userId = (int) auth()->id();
        $order->loadMissing(['listing' => fn ($q) => $q->withTrashed()]);

        $isBuyer = (int) $order->user_id === $userId;
        $isSeller = (int) ($order->listing?->user_id) === $userId;
        $isStaff = auth()->user()?->hasRole('admin') || auth()->user()?->can('support.manage');

        abort_unless($isBuyer || $isSeller || $isStaff, 403);
        abort_unless($order->source === 'marketplace', 404);
    }

    private function escrowAllowsReply(Order $order): bool
    {
        $status = $order->escrow?->status;

        return in_array($status, ['locked', 'disputed'], true);
    }

    private function counterpart(Order $order): ?User
    {
        $order->loadMissing([
            'listing' => fn ($q) => $q->withTrashed()->with('user'),
            'user',
        ]);

        if ((int) $order->user_id === (int) auth()->id()) {
            return $order->listing?->user;
        }

        return $order->user;
    }
}
