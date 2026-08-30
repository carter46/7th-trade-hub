# Merchant security checklist

- [ ] Verify HMAC with `hash_equals` / constant-time compare
- [ ] Reject expired `expires_at` on assertions
- [ ] Track nonces / request ids against replay where practical
- [ ] Never log `client_secret`, webhook secret, or admin passwords
- [ ] Fail closed on subscription unknown/expired
- [ ] Do not expose Hub secrets in HTML or client JS
- [ ] Prefer Hub validate for SSO (token is one-time)
- [ ] TLS everywhere (Hub outbound requires HTTPS)
