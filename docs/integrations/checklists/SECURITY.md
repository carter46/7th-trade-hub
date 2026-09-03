# Merchant security checklist

- [ ] Verify HMAC with `hash_equals` / constant-time compare
- [ ] Reject expired `expires_at` on assertions
- [ ] Track nonces / request ids against replay where practical
- [ ] Never log `client_secret`, webhook secret, or admin passwords
- [ ] If posting `owned.admin_credentials.updated`, send over TLS, short-lived signed events, unique `event_id`; never log the password
- [ ] Fail closed on subscription unknown/expired (users + regular admins; login page excepted; only super admin may enter)
- [ ] Do not expose Hub secrets in HTML or client JS
- [ ] Prefer Hub validate for SSO (token is one-time)
- [ ] Confirm validate `integration_id` matches env on consume
- [ ] Hub-bound emails exist locally before SSO testing
- [ ] SSO does not route through password / MFA / onboarding
- [ ] TLS everywhere (Hub outbound requires HTTPS)
