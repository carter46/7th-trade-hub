<?php

namespace App\Services\Domains;

use App\Enums\PlatformProductType;
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
    public function markManualRegistered(DomainRegistration $registration, ?string $providerReference = null, ?array $nameservers = null, ?int $adminId = null): array
    {
        $result = DB::transaction(function () use ($registration, $providerReference, $nameservers, $adminId) {
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

            $locked->update([
                'status' => DomainRegistration::STATUS_REGISTERED,
                'provider_reference' => $providerReference !== null && $providerReference !== ''
                    ? Str::limit($providerReference, 191, '')
                    : $locked->provider_reference,
                'nameservers' => $ns !== [] ? $ns : $locked->nameservers,
                'nameservers_updated_at' => $ns !== [] ? now() : $locked->nameservers_updated_at,
                'registered_at' => now(),
                'error_message' => null,
                'provider_meta' => array_merge($locked->provider_meta ?? [], [
                    'fulfillment' => 'manual',
                    'domain_fulfillment' => 'manual',
                    'marked_registered_at' => now()->toIso8601String(),
                    'approved_by_admin_id' => $adminId,
                ]),
            ]);

            $fresh = $locked->fresh(['order.user', 'orderItem']);
            $this->syncOrderFqdnReferences($fresh, $previousFqdn);

            $this->audit->log('domains.fulfillment.registered', $fresh, [
                'manual' => true,
                'replacement' => $wasReplacement,
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
     * Keep website bundle lines + related tools in sync when the registration FQDN changes.
     */
    private function syncOrderFqdnReferences(DomainRegistration $registration, ?string $previousFqdn = null): void
    {
        $registration->loadMissing(['order.items', 'orderItem']);
        $order = $registration->order;
        if (! $order) {
            return;
        }

        $newFqdn = strtolower((string) $registration->fqdn);
        $previousFqdn = $previousFqdn !== null ? strtolower($previousFqdn) : null;
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
            $matchesPrevious = $previousFqdn !== null && $itemFqdn !== '' && $itemFqdn === $previousFqdn;
            $isBuyDomainContext = ($options['domain_mode'] ?? null) === 'buy' && $matchesPrevious;

            if (! $isRegistrationLine && ! $matchesPrevious && ! $isBuyDomainContext) {
                continue;
            }

            $options['domain_fqdn'] = $newFqdn;
            if ($tld) {
                $options['tld'] = $tld;
            }
            $item->update(['options' => $options]);
        }

        $tools = UserTool::query()->where('order_id', $order->id)->get();
        foreach ($tools as $tool) {
            $siteHost = null;
            if (filled($tool->site_url)) {
                $siteHost = strtolower((string) (parse_url((string) $tool->site_url, PHP_URL_HOST) ?: ''));
            }

            $shouldUpdateSiteUrl = ! filled($tool->site_url)
                || ($previousFqdn !== null && $siteHost === $previousFqdn);

            if ($shouldUpdateSiteUrl) {
                $tool->update(['site_url' => 'https://'.$newFqdn]);
            }
        }
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

        $title = $wasReplacement
            ? __('Domain replacement approved')
            : __('Domain registered');

        $body = $wasReplacement
            ? __('Your replacement domain :fqdn has been approved and registered.', ['fqdn' => $registration->fqdn])
            : __('Your domain :fqdn has been registered successfully.', ['fqdn' => $registration->fqdn]);

        $registeredAt = optional($registration->registered_at)?->toIso8601String() ?: now()->toIso8601String();

        $message = new NotificationMessage(
            type: 'order.domain_approved',
            title: $title,
            body: $body,
            actionUrl: $url,
            meta: [
                'domain_registration_id' => $registration->id,
                'action_label' => __('View domain'),
            ],
            emailSubject: $title.' — '.$registration->fqdn,
            dedupeKey: 'domain.approve.'.$registration->id.'.'.md5($registeredAt),
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

    private function notifyAdminsReplacementRequested(DomainRegistration $registration): void
    {
        $user = $registration->order?->user;
        $manageUrl = null;
        if ($user && Route::has('admin.users.domains.registrations.show')) {
            $manageUrl = route('admin.users.domains.registrations.show', [$user, $registration]);
        } elseif ($registration->order_id && Route::has('admin.orders.show')) {
            $manageUrl = route('admin.orders.show', $registration->order_id);
        }

        $this->notifications->notifyAdmins(
            new NotificationMessage(
                type: 'order.domain_replacement_requested',
                title: __('Domain replacement requested'),
                body: __(':name requested :fqdn to replace :rejected (order :ref).', [
                    'name' => $user?->name ?? 'Customer',
                    'fqdn' => $registration->fqdn,
                    'rejected' => $registration->rejectedFqdn() ?? '—',
                    'ref' => $registration->order?->reference ?? '#'.$registration->order_id,
                ]),
                actionUrl: $manageUrl,
                meta: [
                    'domain_registration_id' => $registration->id,
                    'order_id' => $registration->order_id,
                ],
                emailSubject: __('Domain replacement — :fqdn', ['fqdn' => $registration->fqdn]),
                permission: 'users.manage',
                // One notify per registration rejection cycle (not per FQDN edit).
                dedupeKey: 'domain.replacement.'.$registration->id,
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
