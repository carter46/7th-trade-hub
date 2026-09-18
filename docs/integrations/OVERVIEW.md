# Overview — Site Integration Platform

```text
┌─────────────────────┐         Protocol v1          ┌──────────────────────┐
│  7th Trade Hub      │  signed health / sync / SSO  │  Merchant site A     │
│  (APIs, My Tools,   │◄────────────────────────────►│  (own DB & stack)    │
│   admin, docs)      │                               └──────────────────────┘
└─────────────────────┘                               ┌──────────────────────┐
                              same docs + credentials │  Merchant site B     │
                                                      └──────────────────────┘
```

## What Hub provides

- Demo Site Integrate (admin) for View Demo on Website Package products
- Checkout → My Tools (`pending_setup` → configured)
- One-time launch tokens + `POST /api/site-integrations/v1/demo/tokens/validate`
- Subscription push + poll (including Admin **Shutdown Site** / **Enable**, same sync as expiry)
- Optional merchant→Hub webhook (ping; owned admin credential sync)

## What merchants implement

On **their** site (not in this repo):

1. `POST /api/7th-tradehub/v1/health` — verify signed Hub POST  
2. `GET /auth/7th-tradehub/demo/consume` → call Hub validate → local session  
3. `POST /api/7th-tradehub/v1/subscription/sync` (Hub push primary) + throttled page-load reconciliation fallback + fail-closed when Hub unreachable; authenticated sites: public session-expired + admin post-login Hub CTAs; non-authenticated: public shutdown overlay with Hub CTAs ([NON-AUTHENTICATED-SITE.md](NON-AUTHENTICATED-SITE.md))
4. Env config with credentials from Hub operator  
5. Optional (owned): after local admin email/password change, POST signed `owned.admin_credentials.updated` to Hub (see [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md#5b-owned-admin-credential-sync-additive))  

Full endpoint spec: [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md)

## Capabilities (extensible)

Demo: `health`, `demo_user_login`, `demo_admin_login`  
Owned: `health`, `subscription_sync`, `shutdown_on_expiry`, and `owned_admin_login` when the site has admin authentication (`admin_credential_sync` only after site→Hub credential POST). Non-authenticated sites omit `owned_admin_login` — see [NON-AUTHENTICATED-SITE.md](NON-AUTHENTICATED-SITE.md).  

Future (documented, not required for Phase 1): logistics/shipment APIs, etc.
