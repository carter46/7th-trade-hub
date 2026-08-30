<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\SiteIntegrationStatus;
use App\Http\Controllers\Controller;
use App\Models\PlatformProduct;
use App\Models\SiteIntegration;
use App\Services\SiteIntegrations\DemoLaunchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DemoLaunchController extends Controller
{
    public function __construct(
        private DemoLaunchService $launch,
    ) {}

    public function __invoke(Request $request, PlatformProduct $product, string $role): RedirectResponse
    {
        $role = strtolower($role);
        abort_unless(in_array($role, ['user', 'admin'], true), 404);

        $integration = SiteIntegration::query()
            ->where('platform_product_id', $product->id)
            ->where('status', SiteIntegrationStatus::Active)
            ->first();

        if (! $integration) {
            return back()->with('error', 'Demo is not available for this product.');
        }

        try {
            $result = $this->launch->launchDemo(
                $request->user(),
                $integration,
                $role,
                $request->ip(),
                $request->userAgent()
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->away($result['redirect_url']);
    }
}
