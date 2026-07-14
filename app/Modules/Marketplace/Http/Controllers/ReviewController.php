<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Modules\Marketplace\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function store(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'completed') {
            return back()->with('error', __('You can only review completed orders.'));
        }

        if (Review::where('order_id', $order->id)->exists()) {
            return back()->with('error', __('You already reviewed this order.'));
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->load('listing.user');

        Review::create([
            'user_id' => auth()->id(),
            'listing_id' => $order->listing_id,
            'order_id' => $order->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        if ($order->listing?->user) {
            $this->notifications->send(
                $order->listing->user,
                'review',
                __('New review on :title', ['title' => $order->listing->title]),
                __('You received a :rating-star review.', ['rating' => $validated['rating']]),
                route('marketplace.show', $order->listing->slug)
            );
        }

        return back()->with('status', __('Thank you for your review.'));
    }
}
