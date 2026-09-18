<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlatformProductType;
use App\Enums\UserToolStatus;
use App\Http\Controllers\Controller;
use App\Models\UserTool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchasedWebsiteController extends Controller
{
    /**
     * Same website product types as member My Tools.
     *
     * @return list<PlatformProductType>
     */
    private function websiteProductTypes(): array
    {
        return [
            PlatformProductType::WebsitePackage,
            PlatformProductType::WebsiteTemplate,
        ];
    }

    /**
     * Purchased website tools for non-anonymized owners.
     */
    private function websiteToolsQuery(): Builder
    {
        $websiteTypes = array_map(
            fn (PlatformProductType $type) => $type->value,
            $this->websiteProductTypes()
        );

        return UserTool::query()
            ->whereHas('product', fn ($q) => $q->ofTypeMany($websiteTypes))
            ->whereHas('user', fn ($q) => $q->whereNull('anonymized_at'));
    }

    /**
     * Status counts using the same effective-status rules as the list filters.
     *
     * @return array{total: int, active: int, expired: int, suspended: int, other: int}
     */
    private function statusCounts(): array
    {
        $base = $this->websiteToolsQuery();

        $active = (clone $base)
            ->where('status', UserToolStatus::Active)
            ->where(function ($inner) {
                $inner->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();

        $expired = (clone $base)
            ->where(function ($inner) {
                $inner->where('status', UserToolStatus::Expired)
                    ->orWhere(function ($past) {
                        $past->whereNotNull('expires_at')
                            ->where('expires_at', '<', now())
                            ->whereNotIn('status', [
                                UserToolStatus::Suspended->value,
                                UserToolStatus::Cancelled->value,
                                UserToolStatus::Inactive->value,
                                UserToolStatus::PendingSetup->value,
                            ]);
                    });
            })
            ->count();

        $suspended = (clone $base)
            ->where('status', UserToolStatus::Suspended)
            ->count();

        $total = (clone $base)->count();
        $other = max(0, $total - $active - $expired - $suspended);

        return [
            'total' => $total,
            'active' => $active,
            'expired' => $expired,
            'suspended' => $suspended,
            'other' => $other,
        ];
    }

    public function index(Request $request): View
    {
        $statusFilter = $request->string('status')->toString();
        $allowedStatuses = array_column(UserToolStatus::cases(), 'value');
        if ($statusFilter !== '' && ! in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = '';
        }

        $search = trim($request->string('q')->toString());

        $tools = $this->websiteToolsQuery()
            ->with(['user', 'product'])
            ->when($search !== '', function ($q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('display_name', 'like', $term)
                        ->orWhere('site_url', 'like', $term)
                        ->orWhereHas('product', fn ($p) => $p->where('title', 'like', $term))
                        ->orWhereHas('user', function ($u) use ($term) {
                            $u->where('name', 'like', $term)
                                ->orWhere('email', 'like', $term)
                                ->orWhere('username', 'like', $term);
                        });
                });
            })
            ->when($statusFilter === UserToolStatus::Expired->value, function ($q) {
                // Match UserTool::effectiveStatus(): past clock counts as expired unless pending/admin shutdown.
                $q->where(function ($inner) {
                    $inner->where('status', UserToolStatus::Expired)
                        ->orWhere(function ($past) {
                            $past->whereNotNull('expires_at')
                                ->where('expires_at', '<', now())
                                ->whereNotIn('status', [
                                    UserToolStatus::Suspended->value,
                                    UserToolStatus::Cancelled->value,
                                    UserToolStatus::Inactive->value,
                                    UserToolStatus::PendingSetup->value,
                                ]);
                        });
                });
            })
            ->when($statusFilter === UserToolStatus::Active->value, function ($q) {
                $q->where('status', UserToolStatus::Active)
                    ->where(function ($inner) {
                        $inner->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->when(
                $statusFilter !== ''
                    && ! in_array($statusFilter, [
                        UserToolStatus::Expired->value,
                        UserToolStatus::Active->value,
                    ], true),
                fn ($q) => $q->where('status', $statusFilter)
            )
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.admin.websites.index', [
            'tools' => $tools,
            'filters' => [
                'q' => $search,
                'status' => $statusFilter,
            ],
            'statuses' => UserToolStatus::cases(),
            'statusCounts' => $this->statusCounts(),
        ]);
    }
}
