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
- Subscription push + poll
- Optional merchant→Hub webhook

## What merchants implement

On **their** site (not in this repo):

1. `POST /api/7th-tradehub/v1/health` — verify signed Hub POST  
2. `GET /auth/7th-tradehub/demo/consume` → call Hub validate → local session  
3. `POST /api/7th-tradehub/v1/subscription/sync` + periodic poll + fail-closed shutdown  
4. Env config with credentials from Hub operator  

Full endpoint spec: [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md)

## Capabilities (extensible)

Demo: `health`, `demo_user_login`, `demo_admin_login`  
Owned: `health`, `subscription_sync`, `shutdown_on_expiry`, `owned_admin_login`  

Future (documented, not required for Phase 1): logistics/shipment APIs, etc.
