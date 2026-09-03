# Changelog — Site Integration Protocol

## v1 (current)

- Protocol name: `7th-tradehub`, version `1`
- Demo vs owned credential split
- HMAC-SHA256 canonical signing
- One-time launch tokens (120s TTL) + Hub validate API
- Subscription push + poll
- Optional site→Hub webhook ping
- Owned site→Hub `owned.admin_credentials.updated` (admin email/password; no reconnect / no key rotation). Sample: `samples/php/sync-admin-credentials.php`.
- Hub Admin **Shutdown Site** / **Enable** (same `subscription/sync` as expiry; merchant login page excepted; only super admin may enter while expired)
- Hub live `expires_at` enforcement on launch/poll
- HTTPS-only outbound URLs with SSRF protections
- Merchant docs: ENDPOINTS-REFERENCE, expanded MERCHANT-GUIDE, PHP verify/poll samples
- Public docs at `/developers/integrations`; operator + merchant notes (pre-create users, exact paths, SSO vs password login)
- Index FAQ (`/demo/` paths), capabilities table, clock skew, rotation flow, smoke test, samples index, merchant error JSON
