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
use App\Services\Domains\DomainConnectionService;
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
        private DomainConnectionService $domainConnections,
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
            $this->domainConnections->attachUserTool($item, $existing->id);

            return $existing;
        }

        $sequence = (int) UserTool::query()
            ->where('user_id', $order->user_id)
            ->where('platform_product_id', $product->id)
            ->count() + 1;

        $variant = $item->variant;
        $duration = (int) ($variant?->duration_months ?? 0);

        $siteUrl = null;
        $fqdn = $options['domain_fqdn'] ?? $options['domain_name'] ?? null;
        if (is_string($fqdn) && trim($fqdn) !== '') {
            $siteUrl = 'https://'.strtolower(trim($fqdn));
        }

        $tool = UserTool::create([
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
            'site_url' => $siteUrl,
            'purchased_at' => ! empty($options['purchased_at'])
                ? \Carbon\Carbon::parse($options['purchased_at'])
                : now(),
            'duration_months' => $duration > 0 ? $duration : null,
        ]);

        $this->domainConnections->attachUserTool($item, $tool->id);

        return $tool;
    }

    /**
     * Initial setup for pending_setup tools only. Starts the paid clock once.
     *
     * @param  array{site_url: string, has_admin_auth?: bool, admin_login_url?: string|null, admin_email?: string|null, admin_password?: string|null}  $data
     * @return array{tool: UserTool, credentials: array<string, mixed>}
     */
    public function setup(UserTool $tool, array $data, ?User $admin = null, ?string $ip = null): array
    {
        if ($tool->status !== UserToolStatus::PendingSetup) {
            throw new InvalidArgumentException('Tool is already configured. Use reconfigure or rotate credentials instead.');
        }

        $hasAdminAuth = array_key_exists('has_admin_auth', $data)
            ? $this->coerceHasAdminAuth($data['has_admin_auth'])
            : true;

        $this->assertCompleteAdminAuthConfig($hasAdminAuth, $data, $tool, requirePassword: true);
        $this->assertHttpsUrls($data, $hasAdminAuth);

        $duration = (int) ($tool->duration_months
            ?: $tool->variant?->duration_months
            ?: 0);

        if ($duration < 1) {
            throw new InvalidArgumentException('Tool is missing a valid plan duration.');
        }

        $creds = $this->credentials->generate();
        $capabilities = UserToolIntegration::capabilitiesForHasAdminAuth($hasAdminAuth);

        $tool = DB::transaction(function () use ($tool, $data, $admin, $ip, $duration, $creds, $hasAdminAuth, $capabilities) {
            $locked = UserTool::query()->whereKey($tool->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== UserToolStatus::PendingSetup) {
                throw new InvalidArgumentException('Tool is already configured. Use reconfigure or rotate credentials instead.');
            }

            $fill = [
                'site_url' => rtrim($data['site_url'], '/'),
                'has_admin_auth' => $hasAdminAuth,
                'livechat_name' => isset($data['livechat_name']) ? (trim((string) $data['livechat_name']) ?: null) : $locked->livechat_name,
                'livechat_url' => isset($data['livechat_url']) ? (trim((string) $data['livechat_url']) ?: null) : $locked->livechat_url,
                'livechat_email' => isset($data['livechat_email']) ? (strtolower(trim((string) $data['livechat_email'])) ?: null) : $locked->livechat_email,
                'status' => UserToolStatus::Active,
                'configured_at' => now(),
                'expires_at' => ($locked->purchased_at ?? now())->copy()->addMonths($duration),
                'duration_months' => $duration,
            ];

            if ($hasAdminAuth) {
                $fill['admin_login_url'] = $data['admin_login_url'] ?? null;
                $fill['admin_email'] = strtolower(trim((string) ($data['admin_email'] ?? '')));
                $fill['admin_password'] = $data['admin_password'] ?? null;
            } else {
                $fill['admin_login_url'] = null;
                $fill['admin_email'] = null;
                $fill['admin_password'] = null;
            }

            $locked->fill($fill);
            if (array_key_exists('livechat_password', $data) && filled($data['livechat_password'])) {
                $locked->livechat_password = $data['livechat_password'];
            }
            $locked->save();

            $integration = $locked->integration;
            if ($integration) {
                $integration->fill([
                    'integration_id' => $creds['integration_id'],
                    'client_id' => $creds['client_id'],
                    'client_secret' => $creds['client_secret'],
                    'webhook_secret' => $creds['webhook_secret'],
                    'capabilities' => $capabilities,
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
                    'capabilities' => $capabilities,
                    'connection_status' => 'unchecked',
                ]);
            }

            $this->audit->log($admin?->id, 'user_tool.setup', $locked, null, [
                'site_url' => $locked->site_url,
                'has_admin_auth' => $hasAdminAuth,
                'admin_email' => $locked->admin_email,
                'expires_at' => $locked->expires_at?->toIso8601String(),
                'integration_id' => $creds['integration_id'],
            ], $ip, ['module' => 'site_integrations']);

            return $locked->fresh(['integration']);
        });

        // HTTP outside the DB transaction — defer health check and subscription sync until
        // merchant installs credentials (operator runs Check connection manually).
        return [
            'tool' => $tool->fresh(['integration', 'product', 'variant']),
            'credentials' => [
                ...$creds,
                'webhook_url' => url('/webhooks/site-integrations/'.$creds['integration_id']),
                'connection_status' => 'unchecked',
                'connection_message' => 'Credentials generated. Install on the merchant site, then run Check connection.',
            ],
        ];
    }

    /**
     * Update URLs / admin identity / password without changing expires_at or rotating keys.
     *
     * @param  array{site_url: string, has_admin_auth?: bool, admin_login_url?: string|null, admin_email?: string|null, admin_password?: string|null}  $data
     * @return array{tool: UserTool}
     */
    public function reconfigure(UserTool $tool, array $data, ?User $admin = null, ?string $ip = null): array
    {
        if ($tool->status === UserToolStatus::PendingSetup) {
            throw new InvalidArgumentException('Tool is still pending setup. Use setup first.');
        }

        $hasAdminAuth = array_key_exists('has_admin_auth', $data)
            ? $this->coerceHasAdminAuth($data['has_admin_auth'])
            : $tool->hasAdminAuth();

        $this->assertCompleteAdminAuthConfig($hasAdminAuth, $data, $tool, requirePassword: false);
        $this->assertHttpsUrls($data, $hasAdminAuth);

        $previousExpires = $tool->expires_at?->copy();
        $capabilities = UserToolIntegration::capabilitiesForHasAdminAuth($hasAdminAuth);

        $tool = DB::transaction(function () use ($tool, $data, $admin, $ip, $previousExpires, $hasAdminAuth, $capabilities) {
            $locked = UserTool::query()->whereKey($tool->id)->lockForUpdate()->with('integration')->firstOrFail();

            $fill = [
                'site_url' => rtrim($data['site_url'], '/'),
                'has_admin_auth' => $hasAdminAuth,
            ];

            if ($hasAdminAuth) {
                $fill['admin_login_url'] = $data['admin_login_url'] ?? null;
                $fill['admin_email'] = strtolower(trim((string) ($data['admin_email'] ?? '')));
                if (! empty($data['admin_password'])) {
                    $fill['admin_password'] = $data['admin_password'];
                } elseif (! filled($locked->admin_password)) {
                    throw new InvalidArgumentException(
                        'Admin password is required when enabling admin authentication.'
                    );
                }
            } else {
                $fill['admin_login_url'] = null;
                $fill['admin_email'] = null;
                $fill['admin_password'] = null;
            }

            $locked->fill($fill);
            // Never extend or reset subscription clock on reconfigure.
            if ($previousExpires) {
                $locked->expires_at = $previousExpires;
            }
            $locked->save();

            if ($locked->integration) {
                $locked->integration->capabilities = $capabilities;
                $locked->integration->save();
            }

            $this->audit->log($admin?->id, 'user_tool.reconfigure', $locked, null, [
                'site_url' => $locked->site_url,
                'has_admin_auth' => $hasAdminAuth,
                'admin_email' => $locked->admin_email,
                'expires_at' => $locked->expires_at?->toIso8601String(),
            ], $ip, ['module' => 'site_integrations']);

            return $locked->fresh(['integration']);
        });

        $this->connectionCheck->checkOwned($tool->fresh(['integration']));

        return ['tool' => $tool->fresh(['integration', 'product', 'variant'])];
    }

    /**
     * Update livechat login details without touching subscription or site credentials.
     *
     * @param  array{livechat_name?: string|null, livechat_url?: string|null, livechat_email?: string|null, livechat_password?: string|null}  $data
     * @return array{tool: UserTool}
     */
    public function updateLivechat(UserTool $tool, array $data, ?User $admin = null, ?string $ip = null): array
    {
        if ($tool->status === UserToolStatus::PendingSetup) {
            throw new InvalidArgumentException('Complete initial setup before saving livechat logins.');
        }

        $tool = DB::transaction(function () use ($tool, $data, $admin, $ip) {
            $locked = UserTool::query()->whereKey($tool->id)->lockForUpdate()->firstOrFail();

            $locked->livechat_name = array_key_exists('livechat_name', $data)
                ? (trim((string) ($data['livechat_name'] ?? '')) ?: null)
                : $locked->livechat_name;
            $locked->livechat_url = array_key_exists('livechat_url', $data)
                ? (trim((string) ($data['livechat_url'] ?? '')) ?: null)
                : $locked->livechat_url;
            $locked->livechat_email = array_key_exists('livechat_email', $data)
                ? (strtolower(trim((string) ($data['livechat_email'] ?? ''))) ?: null)
                : $locked->livechat_email;

            if (array_key_exists('livechat_password', $data) && filled($data['livechat_password'])) {
                $locked->livechat_password = $data['livechat_password'];
            }

            $locked->save();

            $this->audit->log($admin?->id, 'user_tool.livechat_updated', $locked, null, [
                'livechat_name' => $locked->livechat_name,
                'livechat_url' => $locked->livechat_url,
                'livechat_email' => $locked->livechat_email,
                'password_updated' => array_key_exists('livechat_password', $data) && filled($data['livechat_password']),
            ], $ip, ['module' => 'site_integrations']);

            return $locked;
        });

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
        $checkedTool = $tool->fresh(['integration']);

        return [
            'tool' => $checkedTool->load(['product', 'variant']),
            'credentials' => [
                ...$creds,
                'webhook_url' => url('/webhooks/site-integrations/'.$creds['integration_id']),
                'connection_ok' => $check['ok'],
                'connection_status' => $checkedTool->integration?->connection_status,
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
            $locked->clearSubscriptionEndReason();
            $locked->clearShutdownResumeExpiry();
            $locked->save();

            return $locked;
        });

        $this->subscriptionSync->push($tool->fresh(['integration']));

        return $tool->fresh(['integration']);
    }

    /**
     * @param  array{site_url: string, admin_login_url?: string|null}  $data
     */
    private function assertHttpsUrls(array $data, bool $hasAdminAuth = true): void
    {
        $this->urlGuard->assertSafe($data['site_url'], httpsOnly: true);
        if ($hasAdminAuth && filled($data['admin_login_url'] ?? null)) {
            $this->urlGuard->assertSafe((string) $data['admin_login_url'], httpsOnly: true);
        }
    }

    /**
     * Invariant: has_admin_auth=true requires complete admin authentication config.
     *
     * @param  array{admin_login_url?: string|null, admin_email?: string|null, admin_password?: string|null}  $data
     */
    private function assertCompleteAdminAuthConfig(
        bool $hasAdminAuth,
        array $data,
        UserTool $tool,
        bool $requirePassword,
    ): void {
        if (! $hasAdminAuth) {
            return;
        }

        if (! filled($data['admin_login_url'] ?? null)) {
            throw new InvalidArgumentException('Admin login URL is required when admin authentication is enabled.');
        }

        $email = strtolower(trim((string) ($data['admin_email'] ?? '')));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Admin email is required when admin authentication is enabled.');
        }

        $passwordProvided = filled($data['admin_password'] ?? null);
        $hasExistingPassword = filled($tool->admin_password);

        if ($requirePassword && ! $passwordProvided) {
            throw new InvalidArgumentException('Admin password is required when admin authentication is enabled.');
        }

        if (! $requirePassword && ! $passwordProvided && ! $hasExistingPassword) {
            throw new InvalidArgumentException(
                'Admin password is required when enabling admin authentication (no password is stored yet).'
            );
        }

        if ($passwordProvided && strlen((string) $data['admin_password']) < 6) {
            throw new InvalidArgumentException('Admin password must be at least 6 characters.');
        }
    }

    private function coerceHasAdminAuth(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $filtered ?? false;
    }
}
