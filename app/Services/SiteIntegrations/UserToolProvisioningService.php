<?php

namespace App\Services\SiteIntegrations;

use App\Enums\PlatformProductType;
use App\Enums\UserToolStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Models\UserTool;
use App\Models\UserToolIntegration;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserToolProvisioningService
{
    public function __construct(
        private IntegrationCredentialGenerator $credentials,
        private ConnectionCheckService $connectionCheck,
        private SubscriptionSyncService $subscriptionSync,
        private AuditLogService $audit,
        private IntegrationOutboundUrlGuard $urlGuard,
    ) {}

    public function createFromOrderItem(Order $order, OrderItem $item): ?UserTool
    {
        if ($item->item_type !== 'platform_product') {
            return null;
        }

        $product = PlatformProduct::query()->find($item->item_id);
        if (! $product) {
            return null;
        }

        $options = $item->options ?? [];
        if ($product->product_type === PlatformProductType::Domain || filled($options['domain_quote_id'] ?? null)) {
            return null;
        }

        $existing = UserTool::query()
            ->where('order_item_id', $item->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $sequence = (int) UserTool::query()
            ->where('user_id', $order->user_id)
            ->where('platform_product_id', $product->id)
            ->count() + 1;

        $variant = $item->variant;
        $duration = (int) ($variant?->duration_months ?? 0);

        return UserTool::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'platform_product_id' => $product->id,
            'platform_product_variant_id' => $variant?->id,
            'instance_sequence' => $sequence,
            'display_name' => $sequence > 1
                ? $product->title.' #'.$sequence
                : $product->title,
            'status' => UserToolStatus::PendingSetup,
            'purchased_at' => now(),
            'duration_months' => $duration > 0 ? $duration : null,
        ]);
    }

    /**
     * Initial setup for pending_setup tools only. Starts the paid clock once.
     *
     * @param  array{site_url: string, admin_login_url: string, admin_email: string, admin_password: string}  $data
     * @return array{tool: UserTool, credentials: array<string, mixed>}
     */
    public function setup(UserTool $tool, array $data, ?User $admin = null, ?string $ip = null): array
    {
        if ($tool->status !== UserToolStatus::PendingSetup) {
            throw new InvalidArgumentException('Tool is already configured. Use reconfigure or rotate credentials instead.');
        }

        $this->assertHttpsUrls($data);

        $duration = (int) ($tool->duration_months
            ?: $tool->variant?->duration_months
            ?: 0);

        if ($duration < 1) {
            throw new InvalidArgumentException('Tool is missing a valid plan duration.');
        }

        $creds = $this->credentials->generate();

        $tool = DB::transaction(function () use ($tool, $data, $admin, $ip, $duration, $creds) {
            $locked = UserTool::query()->whereKey($tool->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== UserToolStatus::PendingSetup) {
                throw new InvalidArgumentException('Tool is already configured. Use reconfigure or rotate credentials instead.');
            }

            $locked->fill([
                'site_url' => rtrim($data['site_url'], '/'),
                'admin_login_url' => $data['admin_login_url'],
                'admin_email' => strtolower(trim($data['admin_email'])),
                'admin_password' => $data['admin_password'],
                'status' => UserToolStatus::Active,
                'configured_at' => now(),
                'expires_at' => now()->addMonths($duration),
                'duration_months' => $duration,
            ]);
            $locked->save();

            $integration = $locked->integration;
            if ($integration) {
                $integration->fill([
                    'integration_id' => $creds['integration_id'],
                    'client_id' => $creds['client_id'],
                    'client_secret' => $creds['client_secret'],
                    'webhook_secret' => $creds['webhook_secret'],
                    'capabilities' => UserToolIntegration::defaultCapabilities(),
                    'connection_status' => 'unchecked',
                    'last_error' => null,
                ]);
                $integration->save();
            } else {
                UserToolIntegration::create([
                    'user_tool_id' => $locked->id,
                    'integration_id' => $creds['integration_id'],
                    'client_id' => $creds['client_id'],
                    'client_secret' => $creds['client_secret'],
                    'webhook_secret' => $creds['webhook_secret'],
                    'capabilities' => UserToolIntegration::defaultCapabilities(),
                    'connection_status' => 'unchecked',
                ]);
            }

            $this->audit->log($admin?->id, 'user_tool.setup', $locked, null, [
                'site_url' => $locked->site_url,
                'admin_email' => $locked->admin_email,
                'expires_at' => $locked->expires_at?->toIso8601String(),
                'integration_id' => $creds['integration_id'],
            ], $ip, ['module' => 'site_integrations']);

            return $locked->fresh(['integration']);
        });

        // HTTP outside the DB transaction
        $check = $this->connectionCheck->checkOwned($tool->fresh(['integration']));
        $this->subscriptionSync->push($tool->fresh(['integration']));

        return [
            'tool' => $tool->fresh(['integration', 'product', 'variant']),
            'credentials' => [
                ...$creds,
                'webhook_url' => url('/webhooks/site-integrations/'.$creds['integration_id']),
                'connection_ok' => $check['ok'],
                'connection_message' => $check['message'],
            ],
        ];
    }

    /**
     * Update URLs / admin identity / password without changing expires_at or rotating keys.
     *
     * @param  array{site_url: string, admin_login_url: string, admin_email: string, admin_password?: string|null}  $data
     * @return array{tool: UserTool}
     */
    public function reconfigure(UserTool $tool, array $data, ?User $admin = null, ?string $ip = null): array
    {
        if ($tool->status === UserToolStatus::PendingSetup) {
            throw new InvalidArgumentException('Tool is still pending setup. Use setup first.');
        }

        $this->assertHttpsUrls($data);

        $previousExpires = $tool->expires_at?->copy();

        $tool = DB::transaction(function () use ($tool, $data, $admin, $ip, $previousExpires) {
            $locked = UserTool::query()->whereKey($tool->id)->lockForUpdate()->firstOrFail();

            $fill = [
                'site_url' => rtrim($data['site_url'], '/'),
                'admin_login_url' => $data['admin_login_url'],
                'admin_email' => strtolower(trim($data['admin_email'])),
            ];
            if (! empty($data['admin_password'])) {
                $fill['admin_password'] = $data['admin_password'];
            }

            $locked->fill($fill);
            // Never extend or reset subscription clock on reconfigure.
            if ($previousExpires) {
                $locked->expires_at = $previousExpires;
            }
            $locked->save();

            $this->audit->log($admin?->id, 'user_tool.reconfigure', $locked, null, [
                'site_url' => $locked->site_url,
                'admin_email' => $locked->admin_email,
                'expires_at' => $locked->expires_at?->toIso8601String(),
            ], $ip, ['module' => 'site_integrations']);

            return $locked->fresh(['integration']);
        });

        $this->connectionCheck->checkOwned($tool->fresh(['integration']));
        $this->subscriptionSync->push($tool->fresh(['integration']));

        return ['tool' => $tool->fresh(['integration', 'product', 'variant'])];
    }

    /**
     * Explicit credential rotation — does not change expires_at.
     *
     * @return array{tool: UserTool, credentials: array<string, mixed>}
     */
    public function rotateCredentials(UserTool $tool, ?User $admin = null, ?string $ip = null): array
    {
        if ($tool->status === UserToolStatus::PendingSetup || ! $tool->integration) {
            throw new InvalidArgumentException('Tool must be configured before rotating credentials.');
        }

        $creds = $this->credentials->generate();
        $previousExpires = $tool->expires_at?->copy();

        $tool = DB::transaction(function () use ($tool, $creds, $admin, $ip, $previousExpires) {
            $locked = UserTool::query()->whereKey($tool->id)->lockForUpdate()->with('integration')->firstOrFail();
            $integration = $locked->integration;
            if (! $integration) {
                throw new InvalidArgumentException('Provisioning integration is missing.');
            }

            $integration->fill([
                'integration_id' => $creds['integration_id'],
                'client_id' => $creds['client_id'],
                'client_secret' => $creds['client_secret'],
                'webhook_secret' => $creds['webhook_secret'],
                'connection_status' => 'unchecked',
                'last_error' => null,
            ]);
            $integration->save();

            if ($previousExpires) {
                $locked->expires_at = $previousExpires;
                $locked->save();
            }

            $this->audit->log($admin?->id, 'user_tool.rotate_credentials', $locked, null, [
                'integration_id' => $creds['integration_id'],
                'expires_at' => $locked->expires_at?->toIso8601String(),
            ], $ip, ['module' => 'site_integrations']);

            return $locked->fresh(['integration']);
        });

        $check = $this->connectionCheck->checkOwned($tool->fresh(['integration']));
        $this->subscriptionSync->push($tool->fresh(['integration']));

        return [
            'tool' => $tool->fresh(['integration', 'product', 'variant']),
            'credentials' => [
                ...$creds,
                'webhook_url' => url('/webhooks/site-integrations/'.$creds['integration_id']),
                'connection_ok' => $check['ok'],
                'connection_message' => $check['message'],
            ],
        ];
    }

    /**
     * Extend an existing tool after renewal payment (same row).
     */
    public function renew(UserTool $tool, int $durationMonths): UserTool
    {
        if ($durationMonths < 1) {
            throw new InvalidArgumentException('Invalid renewal duration.');
        }

        $tool = DB::transaction(function () use ($tool, $durationMonths) {
            $locked = UserTool::query()->whereKey($tool->id)->lockForUpdate()->firstOrFail();

            $base = $locked->expires_at && $locked->expires_at->isFuture()
                ? $locked->expires_at
                : now();

            $locked->expires_at = $base->copy()->addMonths($durationMonths);
            $locked->duration_months = $durationMonths;
            if ($locked->status === UserToolStatus::Expired) {
                $locked->status = UserToolStatus::Active;
            }
            $locked->save();

            return $locked;
        });

        $this->subscriptionSync->push($tool->fresh(['integration']));

        return $tool->fresh(['integration']);
    }

    /**
     * @param  array{site_url: string, admin_login_url: string}  $data
     */
    private function assertHttpsUrls(array $data): void
    {
        $this->urlGuard->assertSafe($data['site_url'], httpsOnly: true);
        $this->urlGuard->assertSafe($data['admin_login_url'], httpsOnly: true);
    }
}
