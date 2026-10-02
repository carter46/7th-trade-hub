<?php

namespace App\Services\Domains;

use App\Models\DomainConnection;
use App\Models\DomainRegistration;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Support\Domains\DomainFqdn;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DomainConnectionService
{
    public function __construct(
        private DomainDnsLookupService $dns,
    ) {}

    /**
     * Safe customer-facing scan for connect-existing checkout.
     *
     * @return array{
     *     fqdn: string,
     *     registered: bool,
     *     status: string,
     *     nameservers: list<string>,
     *     required_nameservers: list<string>,
     *     already_connected: bool,
     *     message: string|null
     * }
     */
    public function scanForUser(User $user, string $input): array
    {
        $this->dns->assertPlatformNameserversConfigured();

        try {
            $lookup = $this->dns->lookup($input);
        } catch (InvalidArgumentException $e) {
            return [
                'fqdn' => '',
                'registered' => false,
                'status' => 'invalid',
                'nameservers' => [],
                'required_nameservers' => $this->dns->platformNameservers(),
                'already_connected' => false,
                'message' => $e->getMessage(),
            ];
        }

        $alreadyOther = $this->isClaimedByAnotherUser($lookup['fqdn'], $user->id);
        $alreadyOwn = $this->isClaimedByUser($lookup['fqdn'], $user->id);

        if (! $lookup['registered']) {
            return [
                ...$lookup,
                'required_nameservers' => $this->dns->platformNameservers(),
                'already_connected' => $alreadyOther || $alreadyOwn,
                'message' => 'We could not find nameservers for this domain. Confirm it is registered and try again.',
            ];
        }

        if ($alreadyOther) {
            return [
                ...$lookup,
                'required_nameservers' => $this->dns->platformNameservers(),
                'already_connected' => true,
                'message' => 'This domain is already connected to another account on 7th Trade Hub.',
            ];
        }

        if ($alreadyOwn) {
            return [
                ...$lookup,
                'required_nameservers' => $this->dns->platformNameservers(),
                'already_connected' => true,
                'message' => 'This domain is already connected on your account.',
            ];
        }

        return [
            ...$lookup,
            'required_nameservers' => $this->dns->platformNameservers(),
            'already_connected' => false,
            'message' => null,
        ];
    }

    public function isClaimedByAnotherUser(string $fqdn, int $userId): bool
    {
        $fqdn = DomainFqdn::normalizeFqdn($fqdn, apexOnly: false);

        $connected = DomainConnection::query()
            ->activeClaim()
            ->where('fqdn', $fqdn)
            ->where('user_id', '!=', $userId)
            ->exists();

        if ($connected) {
            return true;
        }

        return DomainRegistration::query()
            ->where('fqdn', $fqdn)
            ->where('status', DomainRegistration::STATUS_REGISTERED)
            ->whereHas('order', fn ($q) => $q->where('user_id', '!=', $userId)->where('status', 'paid'))
            ->exists();
    }

    public function isClaimedByUser(string $fqdn, int $userId, ?int $exceptOrderItemId = null): bool
    {
        $fqdn = DomainFqdn::normalizeFqdn($fqdn, apexOnly: false);

        $query = DomainConnection::query()
            ->activeClaim()
            ->where('fqdn', $fqdn)
            ->where('user_id', $userId);

        if ($exceptOrderItemId !== null) {
            $query->where('order_item_id', '!=', $exceptOrderItemId);
        }

        return $query->exists();
    }

    public function isActivelyClaimed(string $fqdn, ?int $exceptOrderItemId = null): bool
    {
        $fqdn = DomainFqdn::normalizeFqdn($fqdn, apexOnly: false);

        $query = DomainConnection::query()
            ->activeClaim()
            ->where('fqdn', $fqdn);

        if ($exceptOrderItemId !== null) {
            $query->where('order_item_id', '!=', $exceptOrderItemId);
        }

        if ($query->exists()) {
            return true;
        }

        return DomainRegistration::query()
            ->where('fqdn', $fqdn)
            ->where('status', DomainRegistration::STATUS_REGISTERED)
            ->whereHas('order', fn ($q) => $q->where('status', 'paid'))
            ->exists();
    }

    /**
     * @param  list<string>  $nameserversAtScan
     */
    public function createFromOrderItem(
        User $user,
        Order $order,
        OrderItem $item,
        string $fqdn,
        array $nameserversAtScan,
        bool $acknowledged,
    ): DomainConnection {
        if ($order->status !== 'paid') {
            throw new InvalidArgumentException('Domain connections can only be created for paid orders.');
        }

        $fqdn = DomainFqdn::normalizeFqdn($fqdn, apexOnly: false);
        $required = $this->dns->platformNameservers();

        if (count($required) < 2) {
            throw new InvalidArgumentException('Platform nameservers are not configured.');
        }

        try {
            return DB::transaction(function () use ($user, $order, $item, $fqdn, $nameserversAtScan, $acknowledged, $required) {
                // Serialize claim checks for this FQDN across concurrent checkouts.
                DomainConnection::query()
                    ->activeClaim()
                    ->where('fqdn', $fqdn)
                    ->lockForUpdate()
                    ->get();

                if ($this->isActivelyClaimed($fqdn, $item->id)) {
                    if ($this->isClaimedByAnotherUser($fqdn, $user->id)) {
                        throw new InvalidArgumentException('This domain is already connected to another account.');
                    }

                    throw new InvalidArgumentException('This domain is already connected on your account.');
                }

                return DomainConnection::query()->updateOrCreate(
                    ['order_item_id' => $item->id],
                    [
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'fqdn' => $fqdn,
                        'claim_key' => $fqdn,
                        'nameservers_at_scan' => $this->dns->normalizeList($nameserversAtScan),
                        'nameservers_last_seen' => $this->dns->normalizeList($nameserversAtScan),
                        'required_nameservers' => $required,
                        'verification_status' => DomainConnection::STATUS_PENDING,
                        'acknowledged_at' => $acknowledged ? now() : null,
                        'verified_at' => null,
                        'last_checked_at' => now(),
                    ],
                );
            });
        } catch (UniqueConstraintViolationException) {
            throw new InvalidArgumentException('This domain is already connected on 7th Trade Hub.');
        }
    }

    /**
     * @return array{ok: bool, message: string, connection: DomainConnection}
     */
    public function checkStatus(DomainConnection $connection): array
    {
        $this->dns->assertPlatformNameserversConfigured();

        $lookup = $this->dns->lookup($connection->fqdn);
        $detected = $lookup['nameservers'];
        $required = $connection->requiredNameserverList() ?: $this->dns->platformNameservers();
        $matched = $lookup['registered'] && $this->dns->matchesPlatformDefaults($detected, $required);

        DB::transaction(function () use ($connection, $detected, $matched) {
            $connection->refresh();
            $connection->update([
                'nameservers_last_seen' => $detected,
                'last_checked_at' => now(),
                'verification_status' => $matched
                    ? DomainConnection::STATUS_VERIFIED
                    : DomainConnection::STATUS_PENDING,
                'claim_key' => $connection->fqdn,
                'verified_at' => $matched ? now() : null,
            ]);
        });

        $connection = $connection->fresh();

        if ($matched) {
            return [
                'ok' => true,
                'message' => 'Nameservers verified. This domain is correctly configured.',
                'connection' => $connection,
            ];
        }

        return [
            'ok' => false,
            'message' => 'We can still see different nameservers. DNS changes can take time to propagate. Please update the nameservers and try again.',
            'connection' => $connection,
        ];
    }

    /**
     * Admin override: mark a domain connection as verified without DNS check.
     */
    public function approveManually(DomainConnection $connection): DomainConnection
    {
        DB::transaction(function () use ($connection) {
            $connection->refresh();
            $connection->update([
                'verification_status' => DomainConnection::STATUS_VERIFIED,
                'verified_at' => now(),
                'last_checked_at' => now(),
            ]);
        });

        return $connection->fresh();
    }

    /**
     * Admin replaces the FQDN on a connection (pending, verified or failed).
     * The new domain starts pending until its nameservers are checked or an admin approves it.
     */
    public function adminReplace(DomainConnection $connection, string $newFqdn): DomainConnection
    {
        try {
            $normalized = DomainFqdn::normalizeFqdn($newFqdn, apexOnly: false);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('New domain is invalid: '.$e->getMessage(), 0, $e);
        }

        try {
            return DB::transaction(function () use ($connection, $normalized) {
                /** @var DomainConnection $locked */
                $locked = DomainConnection::query()->whereKey($connection->id)->lockForUpdate()->firstOrFail();
                $previous = strtolower((string) $locked->fqdn);

                if (strcasecmp($normalized, $previous) === 0) {
                    throw new InvalidArgumentException('Enter a different domain name.');
                }

                DomainConnection::query()
                    ->activeClaim()
                    ->where('fqdn', $normalized)
                    ->lockForUpdate()
                    ->get();

                if ($this->isActivelyClaimed($normalized, $locked->order_item_id ? (int) $locked->order_item_id : null)) {
                    throw new InvalidArgumentException('That domain is already connected or registered on 7th Trade Hub. Choose a different name.');
                }

                DomainConnection::query()
                    ->where('id', '!=', $locked->id)
                    ->whereRaw('LOWER(claim_key) = ?', [$normalized])
                    ->whereNotIn('verification_status', [DomainConnection::STATUS_PENDING, DomainConnection::STATUS_VERIFIED])
                    ->update(['claim_key' => null]);

                $required = $this->dns->platformNameservers();

                $locked->update([
                    'fqdn' => $normalized,
                    'claim_key' => $normalized,
                    'nameservers_at_scan' => [],
                    'nameservers_last_seen' => [],
                    'required_nameservers' => count($required) >= 2 ? $required : $locked->requiredNameserverList(),
                    'verification_status' => DomainConnection::STATUS_PENDING,
                    'verified_at' => null,
                    'last_checked_at' => null,
                ]);

                $this->syncReplacedFqdnReferences($locked, $previous, $normalized);

                return $locked->fresh(['order', 'orderItem', 'userTool']);
            });
        } catch (UniqueConstraintViolationException) {
            throw new InvalidArgumentException('That domain is already connected on 7th Trade Hub. Choose a different name.');
        }
    }

    private function syncReplacedFqdnReferences(DomainConnection $connection, string $previous, string $next): void
    {
        $item = $connection->order_item_id ? OrderItem::query()->find($connection->order_item_id) : null;
        if ($item) {
            $options = $item->options ?? [];
            foreach (['domain_fqdn', 'domain_name'] as $key) {
                if (isset($options[$key]) && strcasecmp((string) $options[$key], $previous) === 0) {
                    $options[$key] = $next;
                }
            }
            if (empty($options['domain_fqdn'])) {
                $options['domain_fqdn'] = $next;
            }
            if (str_contains($next, '.')) {
                $options['domain_tld'] = substr($next, strpos($next, '.') + 1);
            }
            $item->update(['options' => $options]);
        }

        $tools = \App\Models\UserTool::query()
            ->where(function ($q) use ($connection) {
                $q->where('id', $connection->user_tool_id ?? 0);
                if ($connection->order_item_id) {
                    $q->orWhere('order_item_id', $connection->order_item_id);
                }
            })
            ->get();

        foreach ($tools as $tool) {
            $changes = [];
            $siteHost = strtolower((string) (parse_url((string) $tool->site_url, PHP_URL_HOST) ?: ''));
            if (! filled($tool->site_url) || $siteHost === $previous) {
                $changes['site_url'] = 'https://'.$next;
            }

            if (filled($tool->admin_login_url)) {
                $parts = parse_url((string) $tool->admin_login_url);
                if (strtolower((string) ($parts['host'] ?? '')) === $previous) {
                    $changes['admin_login_url'] = 'https://'.$next
                        .($parts['path'] ?? '/')
                        .(isset($parts['query']) ? '?'.$parts['query'] : '')
                        .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
                }
            }

            if ($changes === []) {
                continue;
            }

            $tool->update($changes);

            \App\Models\UserToolIntegration::query()
                ->where('user_tool_id', $tool->id)
                ->update([
                    'connection_status' => 'unchecked',
                    'last_error' => 'Domain was replaced. Reconfigure the site on the new domain, install Hub credentials, then run Check connection.',
                ]);
        }
    }

    public function attachUserTool(OrderItem $item, int $userToolId): void
    {
        DomainConnection::query()
            ->where('order_item_id', $item->id)
            ->whereNull('user_tool_id')
            ->update(['user_tool_id' => $userToolId]);
    }
}
