# Integration samples

Copy and adapt these into **your merchant site** (not into Hub).

| Sample | Purpose |
| ------ | ------- |
| [env.example](env.example) | Credential env variable names |
| [php/protocol-v1-verify.php](php/protocol-v1-verify.php) | HMAC verify (health + subscription sync) and sign (admin credential POST) |
| [php/consume-validate.php](php/consume-validate.php) | Hub token validate on SSO consume |
| [php/poll-subscription.php](php/poll-subscription.php) | Cron poll for owned tools |
| [php/sync-admin-credentials.php](php/sync-admin-credentials.php) | Owned: POST admin email/password changes to Hub |
| [laravel/README.md](laravel/README.md) | Laravel route/handler sketch |
| [SMOKE-TEST.md](SMOKE-TEST.md) | End-to-end smoke test checklist + curl |

**Online paths** (replace host with your Hub):

```text
/developers/integrations/samples/php/protocol-v1-verify.php
/developers/integrations/openapi.yaml
```

There is no directory listing at `/developers/integrations/samples/` — open files from the sidebar or links above.

Samples are **PHP-first** because Protocol v1 signing is the hardest part; port `protocol-v1-verify.php` canonicalization to your language using [PROTOCOL-v1.md](../PROTOCOL-v1.md).
