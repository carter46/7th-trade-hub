# Changelog — Site Integration Protocol

## v1 (current)

- Protocol name: `7th-tradehub`, version `1`
- Demo vs owned credential split
- HMAC-SHA256 canonical signing
- One-time launch tokens (120s TTL) + Hub validate API
- Subscription push + poll
- Optional site→Hub webhook
- Hub live `expires_at` enforcement on launch/poll
- HTTPS-only outbound URLs with SSRF protections
- Merchant docs: ENDPOINTS-REFERENCE, expanded MERCHANT-GUIDE, PHP verify/poll samples
- Public docs at `/developers/integrations`; operator + merchant notes (pre-create users, exact paths, SSO vs password login)
