# Changelog — Site Integration Protocol

## v1 (current)

- Protocol name: `7th-tradehub`, version `1`
- Demo vs owned credential split
- HMAC-SHA256 canonical signing
- One-time launch tokens (120s TTL) + Hub validate API
- Subscription push + poll
- Optional site→Hub webhook ping
- Owned site→Hub `owned.admin_credentials.updated` (admin email/password; no reconnect / no key rotation). Sample: `samples/php/sync-admin-credentials.php`.
- Hub Admin **Shutdown Site** / **Enable** (same `subscription/sync` channel; reason sent as `status`: `suspended` / `cancelled` / `inactive` / `expired`; Hub push is primary; merchant poll is throttled fallback only; fail-closed when Hub unreachable past max trust age)
- Non-authenticated owned sites: Hub checkbox omits admin email; capabilities without `owned_admin_login`; sample gate [samples/php/non-auth-site-gate.php](samples/php/non-auth-site-gate.php) (monotonic sync apply, only `active` online, locked fallback throttle); guide [NON-AUTHENTICATED-SITE.md](NON-AUTHENTICATED-SITE.md). Subscription sync omits `identity` when no admin email; `has_admin_auth=true` requires complete admin config.
- Distinct Protocol v1 subscription statuses for merchants: `pending_setup`, `active`, `suspended`, `cancelled`, `inactive`, `expired`
- Hub live `expires_at` enforcement on launch/poll
- HTTPS-only outbound URLs with SSRF protections
- Merchant docs: ENDPOINTS-REFERENCE, expanded MERCHANT-GUIDE, PHP verify/poll samples
- Public docs at `/developers/integrations`; operator + merchant notes (pre-create users, exact paths, SSO vs password login)
- Index FAQ (`/demo/` paths), capabilities table, clock skew, rotation flow, smoke test, samples index, merchant error JSON
