<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PlatformProductType;
use App\Enums\UserToolStatus;
use App\Http\Controllers\Controller;
use App\Models\UserTool;
use App\Modules\Admin\Services\AuditLogService;
use App\Services\SiteIntegrations\DemoLaunchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class MyToolsController extends Controller
{
    public function __construct(
        private DemoLaunchService $launch,
        private AuditLogService $audit,
    ) {}

    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;
        $status = $request->string('status')->toString();
        $expiringSoon = $request->boolean('expiring_soon');
        $type = $request->string('type')->toString();
        $q = $request->string('q')->toString();

        $tools = UserTool::query()
            ->ownedBy($userId)
            ->with(['product', 'variant', 'integration'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($type !== '', function ($query) use ($type) {
                $enum = PlatformProductType::tryFrom($type);
                if ($enum) {
                    $query->whereHas('product', fn ($p) => $p->where('product_type', $enum));
                }
            })
            ->when($q !== '', function ($query) use ($q) {
                $term = '%'.$q.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('display_name', 'like', $term)
                        ->orWhere('site_url', 'like', $term)
                        ->orWhereHas('product', fn ($p) => $p->where('title', 'like', $term));
                });
            })
            ->when($expiringSoon, function ($query) {
                $query->where('status', UserToolStatus::Active)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '>', now())
                    ->where('expires_at', '<=', now()->addDays(7));
            })
            ->orderByDesc('purchased_at')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.user.my-tools.index', [
            'tools' => $tools,
            'toolTypes' => PlatformProductType::cases(),
            'filters' => [
                'q' => $q,
                'status' => $status,
                'type' => $type,
                'expiring_soon' => $expiringSoon,
            ],
        ]);
    }

    public function show(Request $request, UserTool $tool): View
    {
        abort_unless($tool->user_id === $request->user()->id, 404);
        $tool->load(['product', 'variant', 'integration']);

        return view('dashboard.user.my-tools.show', [
            'tool' => $tool,
        ]);
    }

    public function launchAdmin(Request $request, UserTool $tool): RedirectResponse
    {
        abort_unless($tool->user_id === $request->user()->id, 404);
        $tool->load('integration');

        try {
            $result = $this->launch->launchOwnedAdmin(
                $request->user(),
                $tool,
                $request->ip(),
                $request->userAgent()
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->away($result['redirect_url']);
    }

    public function copyPassword(Request $request, UserTool $tool): JsonResponse
    {
        abort_unless($tool->user_id === $request->user()->id, 404);

        if (! $tool->canRevealAdminPassword()) {
            return response()->json(['message' => 'Password is not available for this tool.'], 422)
                ->header('Cache-Control', 'no-store');
        }

        $this->audit->log($request->user()->id, 'user_tool.password_copied', $tool, null, [
            'tool_id' => $tool->id,
        ], $request->ip(), [
            'actor_type' => 'user',
            'actor_id' => $request->user()->id,
            'module' => 'site_integrations',
        ]);

        return response()->json([
            'password' => $tool->admin_password,
        ])->header('Cache-Control', 'no-store');
    }
}
