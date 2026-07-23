<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Modules\Catalog\Services\CatalogBrowseService;
use App\Modules\Catalog\Services\CatalogContentResolver;
use App\Services\Analytics\UserActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DiscoverServicesController extends Controller
{
    public function __construct(
        private CatalogBrowseService $browse,
        private CatalogContentResolver $content,
        private UserActivityRecorder $activity,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->activity->record($user->id, 'viewed', null, 'discover.services');

        $q = $request->string('q')->toString();
        $groups = $this->browse->groupCards($this->content);
        $types = $this->browse->allGroupTypeValues();

        $searchResults = null;
        if ($q !== '') {
            $searchResults = PlatformProduct::query()
                ->published()
                ->ofTypeMany($types)
                ->with(['productType.serviceCategory', 'activeVariants'])
                ->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('short_description', 'like', "%{$q}%");
                })
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->paginate(12)
                ->withQueryString();
        }

        $popular = PlatformProduct::query()
            ->published()
            ->ofTypeMany($types)
            ->with(['productType.serviceCategory', 'activeVariants'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $recentlyViewed = $this->activity->recentSubjects($user->id, PlatformProduct::class, 6);

        $purchasedProductIds = collect();
        if (Schema::hasTable('order_items')) {
            $purchasedProductIds = OrderItem::query()
                ->whereHas('order', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->whereIn('status', ['paid', 'completed', 'processing', 'delivered']);
                })
                ->where(function ($q) {
                    $q->where('item_type', 'platform_product')
                        ->orWhereNotNull('platform_product_variant_id');
                })
                ->with('variant:id,platform_product_id')
                ->orderByDesc('id')
                ->limit(20)
                ->get()
                ->map(fn (OrderItem $item) => $item->item_type === 'platform_product'
                    ? $item->item_id
                    : $item->variant?->platform_product_id)
                ->filter()
                ->unique();
        }

        $recentlyPurchased = $purchasedProductIds->isNotEmpty()
            ? PlatformProduct::query()->published()->whereIn('id', $purchasedProductIds)->limit(6)->get()
            : collect();

        $suggested = $popular;
        $wallet = $user->wallet ?? null;

        return view('dashboard.user.discover.services', compact(
            'groups',
            'searchResults',
            'q',
            'popular',
            'suggested',
            'recentlyViewed',
            'recentlyPurchased',
            'wallet',
        ));
    }
}
