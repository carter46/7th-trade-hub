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
        private DomainConnectionService $connections,
        private DomainDnsLookupService $dns,
    ) {}

    /**
     * @return array{
     *     mode: string,
     *     fqdn: string,
     *     tld: string,
     *     sld: string,
     *     nameservers_at_scan?: list<string>,
     *     acknowledged?: bool,
     *     quote?: array<string, mixed>,
     *     domain_product?: PlatformProduct,
     *     domain_quote_token?: string
     * }|null
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

        if ($mode === 'connect') {
            if (! empty($data['admin_skip_domain_validation'])) {
                return $this->validateConnectExistingAdmin($user, $data);
            }

            return $this->validateConnectExisting($user, $data);
        }

        $sld = trim((string) ($data['domain_label'] ?? ''));
        $tld = trim((string) ($data['domain_tld'] ?? ''));

        if ($sld === '' || $tld === '') {
            throw new InvalidArgumentException('Enter a domain name and extension.');
        }

        $parsed = DomainFqdn::parse($sld, $tld);
        $this->assertTldSupported($parsed['tld'], $this->quotes->registrationProduct());

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
     * @param  array<string, mixed>  $data
     * @return array{mode: string, fqdn: string, tld: string, sld: string, nameservers_at_scan: list<string>, acknowledged: bool}
     */
    private function validateConnectExisting(User $user, array $data): array
    {
        $fqdnInput = trim((string) ($data['domain_fqdn'] ?? $data['domain_name'] ?? ''));
        if ($fqdnInput === '' && filled($data['domain_label'] ?? null) && filled($data['domain_tld'] ?? null)) {
            // Legacy form fields — prefer full FQDN when present.
            $fqdnInput = trim((string) $data['domain_label']).'.'.ltrim(trim((string) $data['domain_tld']), '.');
        }

        if ($fqdnInput === '') {
            throw new InvalidArgumentException('Enter your existing domain (e.g. example.com).');
        }

        $acknowledged = filter_var($data['domain_connect_acknowledged'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || $data['domain_connect_acknowledged'] === '1'
            || $data['domain_connect_acknowledged'] === 1;

        if (! $acknowledged) {
            throw new InvalidArgumentException('Confirm that you will point this domain to our nameservers before continuing.');
        }

        $this->dns->assertPlatformNameserversConfigured();

        try {
            $lookup = $this->dns->lookup($fqdnInput);
        } catch (InvalidArgumentException $e) {
            throw $e;
        }

        if (! $lookup['registered']) {
            throw new InvalidArgumentException('We could not find nameservers for this domain. Confirm it is registered and try again.');
        }

        if ($this->connections->isClaimedByAnotherUser($lookup['fqdn'], $user->id)) {
            throw new InvalidArgumentException('This domain is already connected to another account on 7th Trade Hub.');
        }

        if ($this->connections->isClaimedByUser($lookup['fqdn'], $user->id)) {
            throw new InvalidArgumentException('This domain is already connected on your account.');
        }

        $parsed = DomainFqdn::fromFqdn($lookup['fqdn'], apexOnly: false);

        return [
            'mode' => 'connect',
            'fqdn' => $parsed['fqdn'],
            'tld' => $parsed['tld'],
            'sld' => $parsed['sld'],
            'nameservers_at_scan' => $lookup['nameservers'],
            'acknowledged' => true,
        ];
    }

    /**
     * Admin manual purchase — record existing domain without live DNS lookup.
     *
     * @param  array<string, mixed>  $data
     * @return array{mode: string, fqdn: string, tld: string, sld: string, nameservers_at_scan: list<string>, acknowledged: bool}
     */
    private function validateConnectExistingAdmin(User $user, array $data): array
    {
        $fqdnInput = trim((string) ($data['domain_fqdn'] ?? $data['domain_name'] ?? ''));
        if ($fqdnInput === '') {
            throw new InvalidArgumentException('Enter the existing domain (e.g. example.com or shop.example.com).');
        }

        $fqdnInput = preg_replace('#^https?://#i', '', $fqdnInput) ?? $fqdnInput;
        $fqdnInput = rtrim(explode('/', $fqdnInput, 2)[0], '/.');

        $parsed = DomainFqdn::fromFqdn($fqdnInput, apexOnly: false);

        if ($this->connections->isClaimedByAnotherUser($parsed['fqdn'], $user->id)) {
            throw new InvalidArgumentException('This domain is already connected to another account on 7th Trade Hub.');
        }

        return [
            'mode' => 'connect',
            'fqdn' => $parsed['fqdn'],
            'tld' => $parsed['tld'],
            'sld' => $parsed['sld'],
            'nameservers_at_scan' => [],
            'acknowledged' => true,
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
