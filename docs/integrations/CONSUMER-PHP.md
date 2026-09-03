# Consumer PHP notes

Reference implementations (copy into your app — not production-complete):

| File | Purpose |
| ---- | ------- |
| [samples/php/protocol-v1-verify.php](samples/php/protocol-v1-verify.php) | HMAC verify (health + sync) and sign (credential POST) |
| [samples/php/consume-validate.php](samples/php/consume-validate.php) | Call Hub validate API |
| [samples/php/poll-subscription.php](samples/php/poll-subscription.php) | Poll owned subscription |
| [samples/php/sync-admin-credentials.php](samples/php/sync-admin-credentials.php) | Owned: POST admin email/password to Hub |
| [samples/env.example](samples/env.example) | Env variable names |

Main guides:

- [MERCHANT-GUIDE.md](MERCHANT-GUIDE.md)
- [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md)
- [PROTOCOL-v1.md](PROTOCOL-v1.md)

Use generic “independent merchant site” language — not any specific bank brand.
