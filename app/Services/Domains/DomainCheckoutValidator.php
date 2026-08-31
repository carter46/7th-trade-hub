<?php

namespace App\Services\Domains;

use App\Enums\PlatformProductType;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Support\Domains\DomainFqdn;
use App\Support\Domains\DomainProductTldPolicy;
use InvalidArgumentException;

class DomainCheckoutValidator
{
    public function __construct(
        private DomainQuoteService $quotes,
    ) {}

    /**
     * @return array{mode: string, fqdn: string, tld: string, sld: string, quote?: array<string, mixed>, domain_product?: PlatformProduct, domain_quote_token?: string}|null
     */
    public function validateWebsitePackageDomain(User $user, PlatformProduct $product, array $data, bool $deferConsumption = false, ?int $orderId = null): array
    {
        if ($product->product_type !== PlatformProductType::WebsitePackage) {
            return $this->legacyOptionalDomain($data);
        }

        $mode = (string) ($data['domain_mode'] ?? '');
        if (! in_array($mode, ['buy', 'connect'], true)) {
            throw new InvalidArgumentException('Choose whether to buy a new domain or connect an existing one.');
        }

        $sld = trim((string) ($data['domain_label'] ?? ''));
        $tld = trim((string) ($data['domain_tld'] ?? ''));

        if ($sld === '' || $tld === '') {
            throw new InvalidArgumentException('Enter a domain name and extension.');
        }

        $parsed = DomainFqdn::parse($sld, $tld);

        if ($mode === 'buy') {
            $this->assertTldSupported($parsed['tld'], $this->quotes->registrationProduct());
        }

        if ($mode === 'connect') {
            return [
                'mode' => 'connect',
                'fqdn' => $parsed['fqdn'],
                'tld' => $parsed['tld'],
                'sld' => $parsed['sld'],
            ];
        }

        $token = (string) ($data['domain_quote_token'] ?? '');
        if ($token === '') {
            throw new InvalidArgumentException('Check domain availability before checkout.');
        }

        $domainProduct = $this->quotes->registrationProduct();
        $quoteResult = $this->resolveDomainQuote($user, $token, $parsed['fqdn'], $domainProduct->id, $deferConsumption, $orderId);

        return [
            'mode' => 'buy',
            'fqdn' => $parsed['fqdn'],
            'tld' => $parsed['tld'],
            'sld' => $parsed['sld'],
            'quote' => $quoteResult,
            'domain_product' => $domainProduct,
            'domain_quote_token' => $token,
            'registrant_contact' => $this->resolveRegistrantContact($data),
        ];
    }

    /**
     * @return array{mode: string, fqdn: string, tld: string, sld: string, quote: array<string, mixed>, domain_product: PlatformProduct, domain_quote_token: string}
     */
    public function validateStandaloneDomainPurchase(User $user, PlatformProduct $product, array $data, bool $deferConsumption = false, ?int $orderId = null): array
    {
        if ($product->product_type !== PlatformProductType::Domain) {
            throw new InvalidArgumentException('Invalid domain checkout.');
        }

        $token = (string) ($data['domain_quote_token'] ?? '');
        $fqdnInput = (string) ($data['domain_fqdn'] ?? $data['domain_name'] ?? '');

        if ($token === '' || $fqdnInput === '') {
            throw new InvalidArgumentException('Check domain availability before checkout.');
        }

        $parsed = DomainFqdn::fromFqdn($fqdnInput);
        $this->assertTldSupported($parsed['tld'], $product);

        $quoteResult = $this->resolveDomainQuote($user, $token, $parsed['fqdn'], $product->id, $deferConsumption, $orderId);

        return [
            'mode' => 'buy',
            'fqdn' => $parsed['fqdn'],
            'tld' => $parsed['tld'],
            'sld' => $parsed['sld'],
            'quote' => $quoteResult,
            'domain_product' => $product,
            'domain_quote_token' => $token,
            'registrant_contact' => $this->resolveRegistrantContact($data),
        ];
    }

    /**
     * @return array{quote: \App\Models\DomainQuote, validated_retail: string, registration: mixed}
     */
    private function resolveDomainQuote(User $user, string $token, string $fqdn, int $productId, bool $deferConsumption, ?int $orderId): array
    {
        if ($deferConsumption) {
            if ($orderId === null) {
                return $this->quotes->previewForCheckout($user, $token, $fqdn, $productId);
            }

            return $this->quotes->reserveForGateway($user, $token, $fqdn, $orderId, $productId);
        }

        return $this->quotes->consumeForPurchase($user, $token, $fqdn, $productId);
    }

    private function assertTldSupported(string $tld, PlatformProduct $product): void
    {
        if (! DomainProductTldPolicy::isAllowed($product, $tld)) {
            throw new InvalidArgumentException('Selected extension is not available for this product.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string|null>
     */
    private function resolveRegistrantContact(array $data): array
    {
        $raw = $data['registrant'] ?? null;
        if (! is_array($raw)) {
            throw new InvalidArgumentException('Enter registrant contact details for domain registration.');
        }

        return DomainRegistrantContact::fromArray($raw)->toStorageArray();
    }

    /**
     * @return array{mode: string, fqdn: ?string, tld: ?string}|null
     */
    private function legacyOptionalDomain(array $data): ?array
    {
        $mode = (string) ($data['domain_mode'] ?? 'none');
        if ($mode === 'none') {
            return null;
        }

        return [
            'mode' => $mode,
            'fqdn' => $data['domain_name'] ?? null,
            'tld' => null,
        ];
    }
}
