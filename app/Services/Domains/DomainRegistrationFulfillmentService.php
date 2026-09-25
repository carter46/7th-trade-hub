<?php

namespace App\Services\Domains;

use App\Enums\PlatformProductType;
use App\Models\DomainConnection;
use App\Models\DomainQuote;
use App\Models\DomainRegistration;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Models\UserTool;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationMessage;
use App\Support\Domains\DomainFqdn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DomainRegistrationFulfillmentService
{
    public function __construct(
        private DomainProviderManager $providers,
        private PlatformDomainPricingPolicy $pricing,
        private DomainAuditLogger $audit,
        private DomainNameserverService $nameservers,
        private NotificationDispatcher $notifications,
    ) {}

    public function fulfillOrder(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $this->fulfillOrderItem($order, $item);
        }
    }

    private function fulfillOrderItem(Order $order, OrderItem $item): void
    {
        if (! $this->isDomainPurchaseLine($item)) {
            return;
        }

        if (DomainRegistration::query()->where('order_item_id', $item->id)->exists()) {
            return;
        }

        $options = $item->options ?? [];
        $fqdn = strtolower((string) ($options['domain_fqdn'] ?? $options['domain_name'] ?? ''));
        $quoteId = (int) ($options['domain_quote_id'] ?? 0);

        if ($fqdn === '' || $quoteId <= 0) {
            $this->recordFulfillmentDataMissing($order, $item, $fqdn !== '' ? $fqdn : 'unknown', 'Order line is missing domain FQDN or quote reference.');

            return;
        }

        $quote = DomainQuote::query()->find($quoteId);
        if (! $quote) {
            $this->recordFulfillmentDataMissing($order, $item, $fqdn, 'Domain quote #'.$quoteId.' was not found for fulfillment.');

            return;
        }

        $isManual = $quote->isManualFulfillment()
            || ($options['domain_fulfillment'] ?? null) === 'manual';

        if ($isManual) {
            $this->fulfillManualOrderItem($order, $item, $quote, $fqdn, $options);

            return;
        }

        if (! config('domains.auto_register_on_purchase', true)) {
            return;
        }

        $registrantContact = $options['registrant_contact'] ?? null;
        if (! is_array($registrantContact)) {
            $registration = DomainRegistration::query()->create([
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'domain_quote_id' => $quote->id,
                'fqdn' => $fqdn,
                'provider_key' => $quote->provider_key,
                'provider_cost_at_checkout' => $quote->provider_cost,
                'provider_currency_at_checkout' => $quote->provider_currency,
                'status' => DomainRegistration::STATUS_FAILED,
                'error_message' => 'Registrant contact details are missing.',
            ]);
            $this->audit->log('domains.fulfillment.failed', $registration, ['reason' => 'missing_registrant']);

            return;
        }

        $registration = DomainRegistration::query()->create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'domain_quote_id' => $quote->id,
            'fqdn' => $fqdn,
            'provider_key' => $quote->provider_key,
            'provider_cost_at_checkout' => $quote->provider_cost,
            'provider_currency_at_checkout' => $quote->provider_currency,
            'registrant_contact' => $registrantContact,
            'status' => DomainRegistration::STATUS_PROCESSING,
        ]);

        $this->audit->log('domains.fulfillment.started', $registration, [
            'fqdn' => $fqdn,
            'order_id' => $order->id,
        ]);

        try {
            $provider = $this->providers->providerRecord($quote->provider_key);
            $adapter = $this->providers->adapterFor($provider);

            $availability = $adapter->checkAvailability($provider, $fqdn);
            if (! $availability->available || ! $availability->isRegistration()) {
                $this->markFailed($registration, 'Domain is no longer available for registration.');

                return;
            }

            $freshQuote = $adapter->getRegistrationQuote($provider, $fqdn, $availability);
            $tolerance = max(0, (float) config('domains.price_drift_tolerance_percent', 2));
            $checkoutCost = (string) $quote->provider_cost;
            $freshCost = number_format($freshQuote->providerCost, 4, '.', '');

            if (! $this->pricing->driftWithinTolerance($checkoutCost, $freshCost, $tolerance)) {
                $this->markReconciliation($registration, 'Provider registration cost increased beyond tolerance.');

                return;
            }

            $result = $adapter->registerDomain($provider, $fqdn, [
                'provider_cost' => $freshQuote->providerCost,
                'premium' => $quote->premium,
                'purchase_type' => $quote->purchase_type,
                'quote_id' => $quote->id,
                'idempotency_key' => 'domain-'.$order->id.'-'.$item->id,
                'registrant_contact' => $registrantContact,
            ]);

            if ($result->success) {
                $confirmedNs = $this->nameservers->resolveAfterRegistration(
                    $quote->provider_key,
                    $fqdn,
                    $result->providerMeta,
                );

                $registration->update([
                    'status' => DomainRegistration::STATUS_REGISTERED,
                    'provider_reference' => $result->providerReference,
                    'provider_meta' => $result->providerMeta,
                    'registered_at' => now(),
                    'error_message' => null,
                    'nameservers' => $confirmedNs !== [] ? $confirmedNs : null,
                    'nameservers_updated_at' => $confirmedNs !== [] ? now() : null,
                    'nameservers_synced_at' => $confirmedNs !== [] ? now() : null,
                ]);
                $this->audit->log('domains.fulfillment.registered', $registration->fresh());

                return;
            }

            $this->markFailed($registration, $result->errorMessage ?? 'Registration failed.');
        } catch (\Throwable $e) {
            Log::error('Domain registration fulfillment failed', [
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'fqdn' => $fqdn,
                'message' => $e->getMessage(),
            ]);

            $this->markReconciliation($registration, $e->getMessage());
        }
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function fulfillManualOrderItem(
        Order $order,
        OrderItem $item,
        DomainQuote $quote,
        string $fqdn,
        array $options,
    ): void {
        $registrantContact = $options['registrant_contact'] ?? null;
        if (! is_array($registrantContact)) {
            $registration = DomainRegistration::query()->create([
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'domain_quote_id' => $quote->id,
                'fqdn' => $fqdn,
                'provider_key' => DomainQuote::PROVIDER_KEY_MANUAL,
                'provider_cost_at_checkout' => $quote->provider_cost,
                'provider_currency_at_checkout' => $quote->provider_currency,
                'status' => DomainRegistration::STATUS_FAILED,
                'error_message' => 'Registrant contact details are missing.',
                'provider_meta' => [
                    'fulfillment' => 'manual',
                    'domain_fulfillment' => 'manual',
                ],
            ]);
            $this->audit->log('domains.fulfillment.failed', $registration, ['reason' => 'missing_registrant']);

            return;
        }

        $registration = DomainRegistration::query()->create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'domain_quote_id' => $quote->id,
            'fqdn' => $fqdn,
            'provider_key' => DomainQuote::PROVIDER_KEY_MANUAL,
            'provider_cost_at_checkout' => $quote->provider_cost,
            'provider_currency_at_checkout' => $quote->provider_currency,
            'registrant_contact' => $registrantContact,
            'status' => DomainRegistration::STATUS_PENDING_MANUAL,
            'provider_meta' => [
                'fulfillment' => 'manual',
                'domain_fulfillment' => 'manual',
                'locked_retail_price' => (string) $quote->retail_price,
            ],
            'error_message' => 'Awaiting manual domain registration by admin.',
        ]);

        $this->audit->log('domains.fulfillment.pending_manual', $registration, [
            'fqdn' => $fqdn,
            'order_id' => $order->id,
        ]);

        $this->dispatchAfterCommit(function () use ($registration) {
            $fresh = DomainRegistration::query()->with(['order.user'])->find($registration->id);
            if ($fresh) {
                $this->notifyAdminsPendingManual($fresh);
            }
        });
    }

    private function markFailed(DomainRegistration $registration, string $message): void
    {
        $registration->update([
            'status' => DomainRegistration::STATUS_FAILED,
            'error_message' => Str::limit($message, 500),
            'last_attempt_at' => now(),
        ]);
        $this->audit->log('domains.fulfillment.failed', $registration->fresh(), [
            'message' => Str::limit($message, 200),
        ]);
    }

    private function markReconciliation(DomainRegistration $registration, string $message): void
    {
        $registration->update([
            'status' => DomainRegistration::STATUS_RECONCILIATION_REQUIRED,
            'error_message' => Str::limit($message, 500),
            'last_attempt_at' => now(),
        ]);
        $this->audit->log('domains.fulfillment.reconciliation_required', $registration->fresh(), [
            'message' => Str::limit($message, 200),
        ]);
    }

    private function recordFulfillmentDataMissing(Order $order, OrderItem $item, string $fqdn, string $message): void
    {
        $registration = DomainRegistration::query()->create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'domain_quote_id' => null,
            'fqdn' => Str::limit($fqdn, 191, ''),
            'provider_key' => DomainQuote::PROVIDER_KEY_MANUAL,
            'provider_cost_at_checkout' => 0,
            'provider_currency_at_checkout' => 'NGN',
            'status' => DomainRegistration::STATUS_FAILED,
            'error_message' => Str::limit($message, 500),
        ]);
        $this->audit->log('domains.fulfillment.failed', $registration, [
            'reason' => 'missing_fulfillment_data',
            'message' => Str::limit($message, 200),
        ]);
    }

    /**
     * Admin completes offline registration for a pending_manual / pending_replacement row.
     *
     * @param  list<string>|null  $nameservers
     * @return array{0: DomainRegistration, 1: bool}  [registration, alreadyRegistered]
     */
    public function markManualRegistered(
        DomainRegistration $registration,
        ?string $providerReference = null,
        ?array $nameservers = null,
        ?int $adminId = null,
        ?string $closelyRelatedFqdn = null,
    ): array {
        $normalizedCloselyRelated = null;
        if (filled($closelyRelatedFqdn)) {
            try {
                $normalizedCloselyRelated = DomainFqdn::normalizeFqdn((string) $closelyRelatedFqdn, true);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException('Closely related domain is invalid: '.$e->getMessage());
            }
        }

        $result = DB::transaction(function () use ($registration, $providerReference, $nameservers, $adminId, $normalizedCloselyRelated) {
            /** @var DomainRegistration $locked */
            $locked = DomainRegistration::query()->whereKey($registration->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === DomainRegistration::STATUS_REGISTERED) {
                return [$locked->fresh(['order.user']), true, false];
            }

            if (! $locked->isManualFulfillment()
                || ! in_array($locked->status, [
                    DomainRegistration::STATUS_PENDING_MANUAL,
                    DomainRegistration::STATUS_PENDING_REPLACEMENT,
                ], true)) {
                throw new InvalidArgumentException('Only pending manual domain registrations can be marked registered this way.');
            }

            $ns = is_array($nameservers)
                ? array_values(array_filter(array_map(fn ($v) => strtolower(trim((string) $v)), $nameservers)))
                : [];

            $wasReplacement = $locked->isPendingReplacement();
            $previousFqdn = strtolower((string) $locked->fqdn);
            $finalFqdn = $previousFqdn;
            $meta = $locked->provider_meta ?? [];
            $appliedCloselyRelated = false;

            if ($normalizedCloselyRelated !== null
                && strcasecmp($normalizedCloselyRelated, $previousFqdn) !== 0) {
                $this->assertFqdnAvailableForReplacement($normalizedCloselyRelated, (int) $locked->id);
                $finalFqdn = $normalizedCloselyRelated;
                $appliedCloselyRelated = true;
                $meta = array_merge($meta, [
                    'unavailable_fqdn' => $previousFqdn,
                    'original_requested_fqdn' => $previousFqdn,
                    'closely_related_fqdn' => $finalFqdn,
                    'closely_related_applied_at' => now()->toIso8601String(),
                    'closely_related_by_admin_id' => $adminId,
                ]);
            }

            $locked->update([
                'fqdn' => $finalFqdn,
                'status' => DomainRegistration::STATUS_REGISTERED,
                'provider_reference' => $providerReference !== null && $providerReference !== ''
                    ? Str::limit($providerReference, 191, '')
                    : $locked->provider_reference,
                'nameservers' => $ns !== [] ? $ns : $locked->nameservers,
                'nameservers_updated_at' => $ns !== [] ? now() : $locked->nameservers_updated_at,
                'registered_at' => now(),
                'error_message' => null,
                'provider_meta' => array_merge($meta, [
                    'fulfillment' => 'manual',
                    'domain_fulfillment' => 'manual',
                    'marked_registered_at' => now()->toIso8601String(),
                    'approved_by_admin_id' => $adminId,
                ]),
            ]);

            $fresh = $locked->fresh(['order.user', 'orderItem']);
            $this->syncOrderFqdnReferences(
                $fresh,
                $previousFqdn,
                forceAllOrderTools: $appliedCloselyRelated || strcasecmp($finalFqdn, $previousFqdn) !== 0,
            );

            $this->audit->log('domains.fulfillment.registered', $fresh, [
                'manual' => true,
                'replacement' => $wasReplacement,
                'closely_related' => $appliedCloselyRelated,
                'unavailable_fqdn' => $appliedCloselyRelated ? $previousFqdn : null,
                'fqdn' => $finalFqdn,
                'provider_reference' => $fresh->provider_reference,
                'admin_id' => $adminId,
            ]);

            return [$fresh->fresh(['order.user']), false, $wasReplacement];
        });

        [$fresh, $alreadyRegistered, $wasReplacement] = $result;

        if (! $alreadyRegistered) {
            $this->dispatchAfterCommit(function () use ($fresh, $wasReplacement) {
                $registration = DomainRegistration::query()->with(['order.user'])->find($fresh->id);
                if ($registration) {
                    $this->notifyUserDomainApproved($registration, $wasReplacement);
                }
            });
        }

        return [$fresh, $alreadyRegistered];
    }

    public function rejectManualRegistration(DomainRegistration $registration, string $reason, ?int $adminId = null): DomainRegistration
    {
        $reason = trim(strip_tags($reason));
        if ($reason === '') {
            throw new InvalidArgumentException('A rejection reason is required.');
        }

        $fresh = DB::transaction(function () use ($registration, $reason, $adminId) {
            /** @var DomainRegistration $locked */
            $locked = DomainRegistration::query()->whereKey($registration->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isManualFulfillment()) {
                throw new InvalidArgumentException('Only manual domain registrations can be rejected this way.');
            }

            if (! in_array($locked->status, [
                DomainRegistration::STATUS_PENDING_MANUAL,
                DomainRegistration::STATUS_PENDING_REPLACEMENT,
            ], true)) {
                throw new InvalidArgumentException('Only pending manual domains can be rejected.');
            }

            $rejectedFqdn = strtolower((string) $locked->fqdn);
            $meta = $locked->provider_meta ?? [];
            $history = is_array($meta['replacement_history'] ?? null) ? $meta['replacement_history'] : [];
            $history[] = [
                'fqdn' => $rejectedFqdn,
                'rejected_at' => now()->toIso8601String(),
                'reason' => Str::limit($reason, 500),
                'admin_id' => $adminId,
            ];
            if (count($history) > 20) {
                $history = array_slice($history, -20);
            }

            $locked->update([
                'status' => DomainRegistration::STATUS_REJECTED,
                'error_message' => Str::limit($reason, 500),
                'provider_meta' => array_merge($meta, [
                    'fulfillment' => 'manual',
                    'domain_fulfillment' => 'manual',
                    'rejected_fqdn' => $rejectedFqdn,
                    'rejection_reason' => Str::limit($reason, 500),
                    'rejected_at' => now()->toIso8601String(),
                    'rejected_by_admin_id' => $adminId,
                    'replacement_history' => $history,
                ]),
            ]);

            $updated = $locked->fresh(['order.user']);
            $this->audit->log('domains.fulfillment.rejected', $updated, [
                'fqdn' => $rejectedFqdn,
                'reason' => Str::limit($reason, 200),
                'admin_id' => $adminId,
            ]);

            return $updated;
        });

        // Status row is already committed by the transaction above. Send mail now so we can
        // report success/failure to the admin (afterCommit would run too late for the flash).
        $mailSent = false;
        $mailError = null;
        $sendMail = function () use ($fresh, &$mailSent, &$mailError): void {
            $registration = DomainRegistration::query()->with(['order.user'])->find($fresh->id);
            if ($registration) {
                [$mailSent, $mailError] = $this->notifyUserDomainRejected($registration);
            }
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($sendMail);
        } else {
            $sendMail();
        }

        $fresh->setAttribute('_reject_mail_sent', $mailSent);
        $fresh->setAttribute('_reject_mail_error', $mailError);

        return $fresh;
    }

    /**
     * Free FQDN replacement after rejection (no new quote/charge).
     */
    public function requestManualReplacement(DomainRegistration $registration, string $newFqdn, ?User $actor = null): DomainRegistration
    {
        try {
            $normalized = DomainFqdn::normalizeFqdn($newFqdn, true);
        } catch (InvalidArgumentException $e) {
            throw $e;
        }

        [$fresh, $notifyAdmins] = DB::transaction(function () use ($registration, $normalized, $actor) {
            /** @var DomainRegistration $locked */
            $locked = DomainRegistration::query()->whereKey($registration->id)->lockForUpdate()->firstOrFail();

            if (! $locked->canRequestReplacement()) {
                throw new InvalidArgumentException('A replacement can only be requested after a manual domain rejection.');
            }

            if (strcasecmp($normalized, (string) $locked->fqdn) === 0
                && $locked->isPendingReplacement()) {
                throw new InvalidArgumentException('Enter a different domain name for the replacement.');
            }

            $this->assertFqdnAvailableForReplacement($normalized, (int) $locked->id);

            $wasAlreadyPendingReplacement = $locked->isPendingReplacement();
            $previousFqdn = strtolower((string) $locked->fqdn);
            $meta = $locked->provider_meta ?? [];
            $rejectedFqdn = $locked->rejectedFqdn() ?: $previousFqdn;

            $locked->update([
                'fqdn' => $normalized,
                'status' => DomainRegistration::STATUS_PENDING_REPLACEMENT,
                'error_message' => $locked->rejectionReason() ?: $locked->error_message,
                'provider_meta' => array_merge($meta, [
                    'fulfillment' => 'manual',
                    'domain_fulfillment' => 'manual',
                    'rejected_fqdn' => $rejectedFqdn,
                    'proposed_fqdn' => $normalized,
                    'replacement_requested_at' => now()->toIso8601String(),
                    'replacement_requested_by_user_id' => $actor?->id,
                ]),
            ]);

            $updated = $locked->fresh(['order.user', 'orderItem']);
            $this->syncOrderFqdnReferences($updated, $previousFqdn);

            $this->audit->log('domains.fulfillment.replacement_requested', $updated, [
                'rejected_fqdn' => $rejectedFqdn,
                'proposed_fqdn' => $normalized,
                'user_id' => $actor?->id,
            ]);

            return [$updated, ! $wasAlreadyPendingReplacement];
        });

        if ($notifyAdmins) {
            $this->dispatchAfterCommit(function () use ($fresh) {
                $registration = DomainRegistration::query()->with(['order.user'])->find($fresh->id);
                if ($registration) {
                    $this->notifyAdminsReplacementRequested($registration);
                }
            });
        }

        return $fresh;
    }

    /**
     * Admin replaces the FQDN on any registration (registered or not).
     * Updates order lines, tool Website URLs, and domain connections. Status is preserved.
     */
    public function adminReplaceFqdn(
        DomainRegistration $registration,
        string $newFqdn,
        ?int $adminId = null,
        ?string $note = null,
    ): DomainRegistration {
        try {
            $normalized = DomainFqdn::normalizeFqdn($newFqdn, true);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('New domain is invalid: '.$e->getMessage(), 0, $e);
        }

        $fresh = DB::transaction(function () use ($registration, $normalized, $adminId, $note) {
            /** @var DomainRegistration $locked */
            $locked = DomainRegistration::query()->whereKey($registration->id)->lockForUpdate()->firstOrFail();

            $previousFqdn = strtolower((string) $locked->fqdn);
            if (strcasecmp($normalized, $previousFqdn) === 0) {
                throw new InvalidArgumentException('Enter a different domain name.');
            }

            $this->assertFqdnAvailableForReplacement($normalized, (int) $locked->id);
            $this->assertFqdnNotActivelyConnectedElsewhere($normalized, $locked->order_id ? (int) $locked->order_id : null);

            $meta = $locked->provider_meta ?? [];
            $history = is_array($meta['admin_replace_history'] ?? null) ? $meta['admin_replace_history'] : [];
            $history[] = [
                'from_fqdn' => $previousFqdn,
                'to_fqdn' => $normalized,
                'replaced_at' => now()->toIso8601String(),
                'admin_id' => $adminId,
                'note' => $note !== null && $note !== '' ? Str::limit(strip_tags($note), 500) : null,
                'status_at_replace' => $locked->status,
            ];
            if (count($history) > 30) {
                $history = array_slice($history, -30);
            }

            $metaPatch = [
                'admin_replace_history' => $history,
                'admin_replaced_from_fqdn' => $previousFqdn,
                'admin_replaced_at' => now()->toIso8601String(),
                'admin_replaced_by_admin_id' => $adminId,
                // Keep proposed_fqdn in sync when replacing a pending customer proposal.
                'proposed_fqdn' => $normalized,
            ];

            $locked->update([
                'fqdn' => $normalized,
                'provider_meta' => array_merge($meta, $metaPatch),
            ]);

            $updated = $locked->fresh(['order.user', 'orderItem']);
            // Do not force-overwrite unrelated tool site URLs on the same order.
            $this->syncOrderFqdnReferences($updated, $previousFqdn, forceAllOrderTools: false);
            $this->markOrderToolsConnectionUnchecked($updated, $previousFqdn);

            $this->audit->log('domains.fulfillment.admin_replaced', $updated, [
                'from_fqdn' => $previousFqdn,
                'to_fqdn' => $normalized,
                'admin_id' => $adminId,
                'status' => $updated->status,
            ]);

            return $updated->fresh(['order.user']);
        });

        $previousFqdn = (string) (($fresh->provider_meta ?? [])['admin_replaced_from_fqdn'] ?? '');
        $this->dispatchAfterCommit(function () use ($fresh, $previousFqdn) {
            $registration = DomainRegistration::query()->with(['order.user'])->find($fresh->id);
            if ($registration) {
                $this->notifyUserDomainAdminReplaced($registration, $previousFqdn);
            }
        });

        return $fresh;
    }

    /**
     * Run side-effects after the surrounding transaction commits (or immediately if none).
     */
    private function dispatchAfterCommit(callable $callback): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit($callback);

            return;
        }

        $callback();
    }

    private function assertFqdnAvailableForReplacement(string $fqdn, int $exceptRegistrationId): void
    {
        $conflict = DomainRegistration::query()
            ->whereRaw('LOWER(fqdn) = ?', [strtolower($fqdn)])
            ->where('id', '!=', $exceptRegistrationId)
            ->whereIn('status', [
                DomainRegistration::STATUS_PENDING,
                DomainRegistration::STATUS_PENDING_MANUAL,
                DomainRegistration::STATUS_PENDING_REPLACEMENT,
                DomainRegistration::STATUS_PROCESSING,
                DomainRegistration::STATUS_REGISTERED,
                DomainRegistration::STATUS_RECONCILIATION_REQUIRED,
            ])
            ->exists();

        if ($conflict) {
            throw new InvalidArgumentException('That domain is already attached to another active registration. Choose a different name.');
        }
    }

    /**
     * claim_key is UNIQUE on domain_connections — refuse replaces that would collide.
     */
    private function assertFqdnNotActivelyConnectedElsewhere(string $fqdn, ?int $exceptOrderId): void
    {
        $fqdn = strtolower($fqdn);

        $query = DomainConnection::query()
            ->activeClaim()
            ->whereRaw('LOWER(fqdn) = ?', [$fqdn]);

        if ($exceptOrderId !== null) {
            $query->where('order_id', '!=', $exceptOrderId);
        }

        if ($query->exists()) {
            throw new InvalidArgumentException('That domain is already connected on another order/account. Choose a different name.');
        }

        // Stale claim_key rows (failed/released) with the same key still block UNIQUE updates.
        $staleClaim = DomainConnection::query()
            ->whereRaw('LOWER(claim_key) = ?', [$fqdn])
            ->when($exceptOrderId !== null, fn ($q) => $q->where('order_id', '!=', $exceptOrderId))
            ->exists();

        if ($staleClaim) {
            // Clear inactive claims so this order can take the key safely.
            DomainConnection::query()
                ->whereRaw('LOWER(claim_key) = ?', [$fqdn])
                ->whereNotIn('verification_status', [
                    DomainConnection::STATUS_PENDING,
                    DomainConnection::STATUS_VERIFIED,
                ])
                ->when($exceptOrderId !== null, fn ($q) => $q->where('order_id', '!=', $exceptOrderId))
                ->update(['claim_key' => null]);

            if (DomainConnection::query()
                ->whereRaw('LOWER(claim_key) = ?', [$fqdn])
                ->when($exceptOrderId !== null, fn ($q) => $q->where('order_id', '!=', $exceptOrderId))
                ->exists()) {
                throw new InvalidArgumentException('That domain claim is still held by another connection. Choose a different name.');
            }
        }
    }

    /**
     * Keep website bundle lines + related tools in sync when the registration FQDN changes.
     */
    private function syncOrderFqdnReferences(
        DomainRegistration $registration,
        ?string $previousFqdn = null,
        bool $forceAllOrderTools = false,
    ): void {
        $registration->loadMissing(['order.items', 'orderItem']);
        $order = $registration->order;
        if (! $order) {
            return;
        }

        $newFqdn = strtolower((string) $registration->fqdn);
        $previousFqdn = $previousFqdn !== null ? strtolower($previousFqdn) : null;
        $unavailableFqdn = $registration->unavailableFqdn();
        $legacyHosts = array_values(array_unique(array_filter([$previousFqdn, $unavailableFqdn])));
        $tld = null;
        try {
            $tld = DomainFqdn::fromFqdn($newFqdn, true)['tld'];
        } catch (\Throwable) {
            // Keep previous tld if parse fails.
        }

        foreach ($order->items as $item) {
            $options = $item->options ?? [];
            $itemFqdn = strtolower((string) ($options['domain_fqdn'] ?? $options['domain_name'] ?? ''));
            $isRegistrationLine = (int) $item->id === (int) $registration->order_item_id;
            $matchesLegacy = $itemFqdn !== '' && in_array($itemFqdn, $legacyHosts, true);
            $isBuyDomainLine = ($options['domain_mode'] ?? null) === 'buy' && ($matchesLegacy || $isRegistrationLine);

            if (! $isRegistrationLine && ! $matchesLegacy && ! $isBuyDomainLine) {
                continue;
            }

            $options['domain_fqdn'] = $newFqdn;
            if ($tld) {
                $options['tld'] = $tld;
            }
            if ($unavailableFqdn) {
                $options['unavailable_domain_fqdn'] = $unavailableFqdn;
                $options['closely_related_domain_fqdn'] = $newFqdn;
            }
            $item->update(['options' => $options]);
        }

        $tools = UserTool::query()->where('order_id', $order->id)->get();
        foreach ($tools as $tool) {
            $siteHost = null;
            if (filled($tool->site_url)) {
                $siteHost = strtolower((string) (parse_url((string) $tool->site_url, PHP_URL_HOST) ?: ''));
            }

            // Never overwrite a tool that already points at an unrelated custom host.
            $shouldUpdateSiteUrl = ! filled($tool->site_url)
                || ($siteHost !== null && $siteHost !== '' && in_array($siteHost, $legacyHosts, true));

            if ($shouldUpdateSiteUrl) {
                $tool->update(['site_url' => 'https://'.$newFqdn]);
            }
        }

        if ($legacyHosts !== []) {
            $connections = DomainConnection::query()
                ->where('order_id', $order->id)
                ->where(function ($q) use ($legacyHosts) {
                    $q->whereIn('fqdn', $legacyHosts)
                        ->orWhereIn('claim_key', $legacyHosts);
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $claimAssigned = DomainConnection::query()
                ->whereRaw('LOWER(claim_key) = ?', [$newFqdn])
                ->whereNotIn('id', $connections->pluck('id'))
                ->exists();

            foreach ($connections as $connection) {
                $isActive = in_array($connection->verification_status, [
                    DomainConnection::STATUS_PENDING,
                    DomainConnection::STATUS_VERIFIED,
                ], true);

                // UNIQUE(claim_key): only one row may hold the new key.
                $nextClaim = null;
                if ($isActive && ! $claimAssigned) {
                    $nextClaim = $newFqdn;
                    $claimAssigned = true;
                }

                $connection->update([
                    'fqdn' => $newFqdn,
                    'claim_key' => $nextClaim,
                ]);
            }
        }
    }

    /**
     * After an FQDN change, force hub↔site health to be re-verified on linked tools
     * whose site URL still tracks this domain.
     */
    private function markOrderToolsConnectionUnchecked(DomainRegistration $registration, ?string $previousFqdn = null): void
    {
        $orderId = $registration->order_id;
        if (! $orderId) {
            return;
        }

        $hosts = array_values(array_unique(array_filter([
            strtolower((string) $registration->fqdn),
            $previousFqdn !== null ? strtolower($previousFqdn) : null,
            $registration->unavailableFqdn(),
        ])));

        $tools = UserTool::query()->where('order_id', $orderId)->get();
        $toolIds = [];
        foreach ($tools as $tool) {
            if (! filled($tool->site_url)) {
                $toolIds[] = $tool->id;
                continue;
            }
            $host = strtolower((string) (parse_url((string) $tool->site_url, PHP_URL_HOST) ?: ''));
            if ($host !== '' && in_array($host, $hosts, true)) {
                $toolIds[] = $tool->id;
            }
        }

        if ($toolIds === []) {
            return;
        }

        \App\Models\UserToolIntegration::query()
            ->whereIn('user_tool_id', $toolIds)
            ->update([
                'connection_status' => 'unchecked',
                'last_error' => 'Domain was replaced. Reconfigure the site on the new domain, install Hub credentials, then run Check connection.',
            ]);

        // Rewrite admin login URLs that still pointed at the old host so they track the new domain.
        $newFqdn = strtolower((string) $registration->fqdn);
        $legacyHosts = array_values(array_filter([
            $previousFqdn !== null ? strtolower($previousFqdn) : null,
            $registration->unavailableFqdn(),
        ]));

        foreach ($tools as $tool) {
            if (! in_array($tool->id, $toolIds, true) || ! filled($tool->admin_login_url)) {
                continue;
            }

            $loginHost = strtolower((string) (parse_url((string) $tool->admin_login_url, PHP_URL_HOST) ?: ''));
            if ($loginHost === '' || ! in_array($loginHost, $legacyHosts, true)) {
                continue;
            }

            $parts = parse_url((string) $tool->admin_login_url);
            $path = $parts['path'] ?? '/';
            $query = isset($parts['query']) ? '?'.$parts['query'] : '';
            $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';
            $tool->update([
                'admin_login_url' => 'https://'.$newFqdn.$path.$query.$fragment,
            ]);
        }
    }

    private function notifyUserDomainAdminReplaced(DomainRegistration $registration, string $previousFqdn): void
    {
        $user = $this->resolveOrderUser($registration);
        if (! $user?->email) {
            Log::warning('domains.fulfillment.admin_replace_notify_skipped', [
                'registration_id' => $registration->id,
                'reason' => 'missing_user_or_email',
            ]);

            return;
        }

        $url = Route::has('dashboard.my-domains.show')
            ? route('dashboard.my-domains.show', $registration)
            : null;

        $from = $previousFqdn !== '' ? $previousFqdn : '—';
        $message = new NotificationMessage(
            type: 'order.domain_replaced',
            title: __('Your domain was updated'),
            body: __('An administrator changed your domain from :from to :to. Order and website details now use the new domain.', [
                'from' => $from,
                'to' => $registration->fqdn,
            ]),
            actionUrl: $url,
            meta: [
                'domain_registration_id' => $registration->id,
                'action_label' => __('View domain'),
                'from_fqdn' => $from,
                'to_fqdn' => $registration->fqdn,
            ],
            emailSubject: __('Domain updated — :fqdn', ['fqdn' => $registration->fqdn]),
            dedupeKey: 'domain.admin_replace.'.$registration->id.'.'.md5($from.'|'.$registration->fqdn.'|'.now()->timestamp),
        );

        $this->deliverUserNotification($user, $message, 'admin_replace', $registration->id);
    }

    /**
     * @return array{0: bool, 1: ?string} [mailSent, error]
     */
    private function notifyUserDomainRejected(DomainRegistration $registration): array
    {
        $user = $this->resolveOrderUser($registration);
        if (! $user?->email) {
            Log::warning('domains.fulfillment.reject_notify_skipped', [
                'registration_id' => $registration->id,
                'order_id' => $registration->order_id,
                'reason' => 'missing_user_or_email',
            ]);

            return [false, 'Customer has no email on the order account.'];
        }

        $url = Route::has('dashboard.my-domains.show')
            ? route('dashboard.my-domains.show', $registration)
            : null;

        $rejectedAt = (string) (($registration->provider_meta ?? [])['rejected_at'] ?? now()->toIso8601String());

        $message = new NotificationMessage(
            type: 'order.domain_rejected',
            title: __('Domain registration rejected'),
            body: __('We could not register :fqdn. Reason: :reason. You can submit a free replacement domain.', [
                'fqdn' => $registration->fqdn,
                'reason' => $registration->error_message ?? '—',
            ]),
            actionUrl: $url,
            meta: [
                'domain_registration_id' => $registration->id,
                'action_label' => __('Replace domain'),
            ],
            emailSubject: __('Domain rejected — replace :fqdn', ['fqdn' => $registration->fqdn]),
            // Unique per reject event so retries after a failed send are not swallowed.
            dedupeKey: 'domain.reject.'.$registration->id.'.'.md5($rejectedAt.(string) $registration->error_message),
        );

        return $this->deliverUserNotification($user, $message, 'reject', $registration->id);
    }

    private function notifyUserDomainApproved(DomainRegistration $registration, bool $wasReplacement): void
    {
        $user = $this->resolveOrderUser($registration);
        if (! $user?->email) {
            Log::warning('domains.fulfillment.approve_notify_skipped', [
                'registration_id' => $registration->id,
                'order_id' => $registration->order_id,
                'reason' => 'missing_user_or_email',
            ]);

            return;
        }

        $url = Route::has('dashboard.my-domains.show')
            ? route('dashboard.my-domains.show', $registration)
            : null;

        $unavailable = $registration->unavailableFqdn();
        $reference = filled($registration->provider_reference)
            ? (string) $registration->provider_reference
            : null;
        $nameservers = $registration->nameserverList();

        if ($unavailable) {
            $title = __('Closely related domain registered');
            $body = __('Your requested domain :unavailable was not available. We registered the closely related domain :fqdn instead.', [
                'unavailable' => $unavailable,
                'fqdn' => $registration->fqdn,
            ]);
        } elseif ($wasReplacement) {
            $title = __('Domain replacement approved');
            $body = __('Your replacement domain :fqdn has been approved and registered.', ['fqdn' => $registration->fqdn]);
        } else {
            $title = __('Domain registered');
            $body = __('Your domain :fqdn has been registered successfully.', ['fqdn' => $registration->fqdn]);
        }

        if ($reference) {
            $body .= ' '.__('Registrar reference: :ref.', ['ref' => $reference]);
        }
        if ($nameservers !== []) {
            $body .= ' '.__('Nameservers: :ns.', ['ns' => implode(', ', $nameservers)]);
        }

        $registeredAt = optional($registration->registered_at)?->toIso8601String() ?: now()->toIso8601String();

        $message = new NotificationMessage(
            type: 'order.domain_approved',
            title: $title,
            body: $body,
            actionUrl: $url,
            meta: [
                'domain_registration_id' => $registration->id,
                'action_label' => __('View domain'),
                'provider_reference' => $reference,
                'unavailable_fqdn' => $unavailable,
                'fqdn' => $registration->fqdn,
            ],
            emailSubject: $title.' — '.$registration->fqdn,
            dedupeKey: 'domain.approve.'.$registration->id.'.'.md5($registeredAt.(string) $reference.(string) $unavailable),
        );

        $this->deliverUserNotification($user, $message, 'approve', $registration->id);
    }

    /**
     * Canonical path: NotificationDispatcher → MailChannel → OutboundMail → EmailService.
     *
     * @return array{0: bool, 1: ?string} [mailSent, error]
     */
    private function deliverUserNotification(User $user, NotificationMessage $message, string $action, int $registrationId): array
    {
        try {
            $mailResult = $this->notifications->notifyUser($user, $message, ['database', 'mail']);
        } catch (\Throwable $e) {
            Log::error('domains.fulfillment.'.$action.'_notify_exception', [
                'registration_id' => $registrationId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [false, $e->getMessage()];
        }

        if ($mailResult?->success) {
            Log::info('domains.fulfillment.'.$action.'_mail_sent', [
                'registration_id' => $registrationId,
                'user_id' => $user->id,
                'email' => $user->email,
                'provider' => $mailResult->provider,
                'message_id' => $mailResult->messageId,
            ]);

            return [true, null];
        }

        $error = trim((string) ($mailResult?->error ?: 'Mail channel did not report success'));
        Log::error('domains.fulfillment.'.$action.'_mail_failed', [
            'registration_id' => $registrationId,
            'user_id' => $user->id,
            'email' => $user->email,
            'provider' => $mailResult?->provider,
            'error' => $error,
        ]);

        return [false, $error];
    }

    private function resolveOrderUser(DomainRegistration $registration): ?User
    {
        $registration->loadMissing('order.user');

        if ($registration->order?->user instanceof User) {
            return $registration->order->user;
        }

        $userId = $registration->order?->user_id
            ?? ($registration->order_id
                ? Order::query()->whereKey($registration->order_id)->value('user_id')
                : null);

        if (! $userId) {
            return null;
        }

        return User::query()->find($userId);
    }

    private function domainAdminManageUrl(DomainRegistration $registration): ?string
    {
        $user = $registration->order?->user;
        if ($user && Route::has('admin.users.domains.registrations.show')) {
            return route('admin.users.domains.registrations.show', [$user, $registration]);
        }
        if ($registration->order_id && Route::has('admin.orders.show')) {
            return route('admin.orders.show', $registration->order_id);
        }

        return null;
    }

    private function notifyAdminsPendingManual(DomainRegistration $registration): void
    {
        $user = $registration->order?->user;

        $this->notifications->notifyAdmins(
            new NotificationMessage(
                type: 'domain.pending_manual',
                title: __('Manual domain registration needed'),
                body: __(':name ordered :fqdn (order :ref). Register it offline, then approve in admin.', [
                    'name' => $user?->name ?? 'Customer',
                    'fqdn' => $registration->fqdn,
                    'ref' => $registration->order?->reference ?? '#'.$registration->order_id,
                ]),
                actionUrl: $this->domainAdminManageUrl($registration),
                meta: [
                    'domain_registration_id' => $registration->id,
                    'order_id' => $registration->order_id,
                ],
                emailSubject: __('Register domain — :fqdn', ['fqdn' => $registration->fqdn]),
                permission: 'users.manage',
                dedupeKey: 'domain.pending_manual.'.$registration->id,
            ),
            ['database', 'mail']
        );
    }

    private function notifyAdminsReplacementRequested(DomainRegistration $registration): void
    {
        $user = $registration->order?->user;
        $requestedAt = (string) (($registration->provider_meta ?? [])['replacement_requested_at'] ?? now()->toIso8601String());

        $this->notifications->notifyAdmins(
            new NotificationMessage(
                // domain.* → General (info) profile notify inbox — not Sales.
                type: 'domain.replacement_requested',
                title: __('Domain replacement requested'),
                body: __(':name requested :fqdn to replace :rejected (order :ref).', [
                    'name' => $user?->name ?? 'Customer',
                    'fqdn' => $registration->fqdn,
                    'rejected' => $registration->rejectedFqdn() ?? '—',
                    'ref' => $registration->order?->reference ?? '#'.$registration->order_id,
                ]),
                actionUrl: $this->domainAdminManageUrl($registration),
                meta: [
                    'domain_registration_id' => $registration->id,
                    'order_id' => $registration->order_id,
                ],
                emailSubject: __('Domain replacement — :fqdn', ['fqdn' => $registration->fqdn]),
                permission: 'users.manage',
                // One notify per replacement submission (not sticky across reject→replace cycles).
                dedupeKey: 'domain.replacement.'.$registration->id.'.'.md5($requestedAt),
            ),
            ['database', 'mail']
        );
    }

    private function isDomainPurchaseLine(OrderItem $item): bool
    {
        $options = $item->options ?? [];

        if (($options['domain_mode'] ?? '') === 'connect') {
            return false;
        }

        if (filled($options['domain_fqdn'] ?? null) && filled($options['domain_quote_id'] ?? null)) {
            return true;
        }

        if ($item->item_type !== 'platform_product') {
            return false;
        }

        $product = PlatformProduct::query()->find($item->item_id);

        return $product?->product_type === PlatformProductType::Domain;
    }
}
