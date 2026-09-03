# Merchant go-live checklist

- [ ] Credentials stored only in server env (not frontend)
- [ ] Demo vs owned credentials not mixed on the same deployment
- [ ] Site URL is HTTPS and publicly reachable (Hub blocks localhost/private IPs)
- [ ] `POST /api/7th-tradehub/v1/health` verifies HMAC on signed JSON body
- [ ] Health returns `{ "ok": true, "capabilities": [...] }` with HTTP 200
- [ ] Health checks `X-7TH-Client-Id` and `X-7TH-Integration-Id` headers match env
- [ ] Consume route calls Hub `POST /api/site-integrations/v1/demo/tokens/validate`
- [ ] Consume does not trust browser query `email` / `role`
- [ ] Validate response `integration_id` matches env
- [ ] Local session uses Hub `identity.email` from validate response only
- [ ] Post-login redirect uses validate `role` (`user` vs `admin`)
- [ ] Hub-bound emails pre-exist locally with correct roles
- [ ] SSO bypasses password / MFA / onboarding flows
- [ ] Health (and owned sync) respond during customer maintenance/shutdown UI
- [ ] Subscription sync verifies signature (owned tools)
- [ ] Poll cron hits `GET /api/site-integrations/v1/subscription` every 5–15 min (owned)
- [ ] Shutdown UI blocks users and **regular** admins when expired (same session-expired message site-wide)
- [ ] Login page and login form remain reachable during shutdown
- [ ] Only **super admin** (upgraded existing admin) may enter after password login; Hub SSO still refused while expired
- [ ] Stale `active` cannot overwrite newer `expired` on sync
- [ ] Hub Check connection succeeds
- [ ] Demo / owned SSO smoke-tested end-to-end
- [ ] Optional webhook to Hub tested with `X-7TH-Webhook-Secret`
- [ ] Optional (owned): admin email/password changes POST `owned.admin_credentials.updated` (not required to stay connected)
- [ ] Optional (owned): Admin Hub **Shutdown Site** / **Enable** treated like expiry / renew (same sync payload)

Reference: [ENDPOINTS-REFERENCE.md](../ENDPOINTS-REFERENCE.md)
