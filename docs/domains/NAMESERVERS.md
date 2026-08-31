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

## Schema

`domain_registrations`:

- `nameservers` — JSON array of hostnames
- `nameservers_updated_at` — last confirmed change
- `nameservers_synced_at` — last successful registrar fetch
