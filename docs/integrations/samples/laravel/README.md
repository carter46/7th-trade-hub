# Laravel sketch

Mount routes for:

- `POST /api/7th-tradehub/v1/health`
- `GET /auth/7th-tradehub/demo/consume`
- `POST /api/7th-tradehub/v1/subscription/sync`

Store credentials in `.env` using [../env.example](../env.example).

Call Hub validate from the consume controller using HTTP client + `X-7TH-Client-Id` / `X-7TH-Client-Secret`.

Schedule a job to poll `GET {HUB}/api/site-integrations/v1/subscription` and apply fail-closed shutdown.

Implement Protocol v1 HMAC verification to match Hub `ProtocolV1Signer` canonicalization (see PROTOCOL-v1.md).
