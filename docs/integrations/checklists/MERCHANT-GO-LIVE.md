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
- [ ] Subscription sync verifies signature (owned tools) and persists local state from Hub **push**
- [ ] Throttled fallback reconciliation only when local state is missing/stale (not required cron every 5–15 min)
- [ ] Fail-closed when `expires_at` past or `last_synced_at` older than max trust age and Hub unreachable
- [ ] Shutdown UI blocks public pages and end users with the generic session-expired message when offline (authenticated sites)
- [ ] After **regular admin** password login: status-specific copy (`expired` → Hub `/login` renew; `cancelled` / `suspended` / `inactive` → Hub `/help`)
- [ ] Non-authenticated sites: public shutdown overlay with Hub CTAs ([NON-AUTHENTICATED-SITE.md](../NON-AUTHENTICATED-SITE.md))
- [ ] Login page and login form remain reachable during shutdown (authenticated sites)
- [ ] Only **super admin** (upgraded existing admin) may enter after password login; Hub SSO still refused while offline
- [ ] Stale `active` cannot overwrite newer offline status on sync
- [ ] Hub Check connection succeeds
- [ ] Demo / owned SSO smoke-tested end-to-end
- [ ] Optional webhook to Hub tested with `X-7TH-Webhook-Secret`
- [ ] Optional (owned): admin email/password changes POST `owned.admin_credentials.updated` (not required to stay connected)
- [ ] Optional (owned): Admin Hub **Shutdown Site** / **Enable** — after regular-admin login, show status-specific Hub CTAs from pushed `status`

Reference: [ENDPOINTS-REFERENCE.md](../ENDPOINTS-REFERENCE.md)
