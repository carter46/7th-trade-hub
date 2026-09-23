<?php

namespace App\Services\Domains;

use App\Enums\PlatformProductType;
use App\Models\DomainQuote;
use App\Models\DomainRegistration;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DomainRegistrationFulfillmentService
{
    public function __construct(
        private DomainProviderManager $providers,
        private PlatformDomainPricingPolicy $pricing,
        private DomainAuditLogger $audit,
        private DomainNameserverService $nameservers,
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
     * Admin completes offline registration for a pending_manual row.
     *
     * @param  list<string>|null  $nameservers
     */
    public function markManualRegistered(DomainRegistration $registration, ?string $providerReference = null, ?array $nameservers = null): DomainRegistration
    {
        if ($registration->status === DomainRegistration::STATUS_REGISTERED) {
            return $registration;
        }

        if ($registration->status !== DomainRegistration::STATUS_PENDING_MANUAL
            || $registration->provider_key !== DomainQuote::PROVIDER_KEY_MANUAL) {
            throw new \InvalidArgumentException('Only pending manual domain registrations can be marked registered this way.');
        }

        $ns = is_array($nameservers)
            ? array_values(array_filter(array_map(fn ($v) => strtolower(trim((string) $v)), $nameservers)))
            : [];

        $registration->update([
            'status' => DomainRegistration::STATUS_REGISTERED,
            'provider_reference' => $providerReference !== null && $providerReference !== ''
                ? Str::limit($providerReference, 191, '')
                : $registration->provider_reference,
            'nameservers' => $ns !== [] ? $ns : $registration->nameservers,
            'nameservers_updated_at' => $ns !== [] ? now() : $registration->nameservers_updated_at,
            'registered_at' => now(),
            'error_message' => null,
            'provider_meta' => array_merge($registration->provider_meta ?? [], [
                'fulfillment' => 'manual',
                'domain_fulfillment' => 'manual',
                'marked_registered_at' => now()->toIso8601String(),
            ]),
        ]);

        $this->audit->log('domains.fulfillment.registered', $registration->fresh(), [
            'manual' => true,
        ]);

        return $registration->fresh();
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
