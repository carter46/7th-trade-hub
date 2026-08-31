# Domain nameservers

## Platform defaults vs per-domain state

| Concept | Source | Purpose |
|---------|--------|---------|
| **Platform defaults** | `DOMAIN_NS1`, `DOMAIN_NS2` in `.env` → `config('domains.default_nameservers')` | Applied **only when initially registering a new domain** |
| **Current nameservers** | `domain_registrations.nameservers` (JSON) | Authoritative per-domain snapshot for that domain only |

Platform defaults are **not** the current state after a customer changes nameservers in **My Domains**. `.env` defaults remain unchanged and apply to future registrations only.

The registrar (Name.com, DomainNameAPI) is the **registration pipe**. It does not choose the customer's DNS destination unless you configure registrar account defaults outside this app—which we avoid by always sending platform defaults at registration.

## Registration flow

1. Customer completes checkout (WHOIS only—no nameserver fields).
2. Fulfillment calls `registerDomain()` on the provider bound to the quote.
3. Registration payload includes `config('domains.default_nameservers')`.
4. On **successful** registration, `domain_registrations.nameservers` is set from provider response or a post-register fetch.
5. `nameservers_updated_at` and `nameservers_synced_at` are set when the snapshot is confirmed.

If registration fails, nameserver fields are not written.

## Customer management (My Domains)

**Services → My Domains → {domain} → Manage**

- View current nameservers (per-domain snapshot)
- **Change Nameservers** — customer enters NS1–NS4; update goes through the **same** `provider_key` that registered the domain
- **Use Platform Defaults** — applies current `DOMAIN_NS*` via the registrar API
- **Refresh from Registrar** — fetches live NS from the registrar and updates the snapshot

### Confirmed-snapshot rule (mandatory)

Database `nameservers` and `nameservers_updated_at` are updated **only after** the registrar API confirms success. If the API call fails, the dashboard keeps showing the previous values.

## Provider routing for updates

NS updates always use `domain_registrations.provider_key`. Never re-route to another provider or fallback registrar.

```
Customer → My Domains → DomainNameserverService → {provider_key} adapter → registrar API
```

## Configuration

```env
DOMAIN_NS1=ns1.your-dns.example.com
DOMAIN_NS2=ns2.your-dns.example.com
```

These can point at your hosting DNS, Cloudflare, or any DNS destination you choose as the platform default for **new** registrations.

They are also the **required nameservers** for **Connect existing domain** ownership verification (public DNS lookup vs `DOMAIN_NS*`). Connect checkout does not require a live match before payment; customers verify later under **My Domains → Check status**.

## Connect existing domain (website packages)

1. Checkout: customer enters a full FQDN → **Check Domain** runs `DomainDnsLookupService` (public NS records via `dns_get_record`).
2. UI shows current NS + required platform defaults from config.
3. Customer acknowledges they will update NS, then pays (no registration fee, no registrar call).
4. A `domain_connections` row is created with `verification_status=pending` **only after the order is paid** (wallet immediately; gateway after Monnify fulfillment). Unpaid gateway checkouts do not claim the FQDN.
5. **My Domains** lists paid connections; **Check status** re-queries public DNS and marks `verified` when all required platform NS are present.
6. Active claims use a unique `claim_key` (= FQDN) so concurrent checkouts cannot double-claim the same domain.

`DOMAIN_NS1` / `DOMAIN_NS2` must be set or connect scan/verify fails with a clear message.

## Schema

`domain_registrations`:

- `nameservers` — JSON array of hostnames
- `nameservers_updated_at` — last confirmed change
- `nameservers_synced_at` — last successful registrar fetch

`domain_connections` (external domains pointed at the platform):

- `fqdn`, `nameservers_at_scan`, `nameservers_last_seen`, `required_nameservers`
- `verification_status` — `pending` | `verified` | `failed`
- `acknowledged_at`, `verified_at`, `last_checked_at`
