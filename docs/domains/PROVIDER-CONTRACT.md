# Domain provider contract

Implement `App\Contracts\Domains\DomainProviderInterface`:

- `key(): string` — internal key (e.g. `namecom`)
- `listTlds(DomainProvider $provider): DomainTld[]`
- `checkAvailability(DomainProvider $provider, string $fqdn): DomainAvailabilityResult`
- `getRegistrationQuote(...): DomainRegistrationQuote`
- `registerDomain(DomainProvider, fqdn, context): DomainRegistrationResult` — post-payment registration (Phase 2)

## Normalized results

- Availability must expose `purchaseType`, `premium`, `purchasable`, optional `purchasePrice`.
- Only `purchaseType === registration` is supported in Phase 1.
- Premium domains require exact `getRegistrationQuote` cost (Name.com: `getPricing` with `years=1`).

## Registry

Credentials and sandbox flags live on `domain_providers` (encrypted JSON). Controllers and customer UI never reference adapter class names or reseller branding.

## Adding a provider

1. Add adapter under `app/Services/Domains/Providers/{Name}/`.
2. Seed a row in `domain_providers` with `adapter_class`.
3. Admin enables, sets default/fallback, stores credentials.

See [providers/NAMECOM.md](providers/NAMECOM.md) for the first adapter.
