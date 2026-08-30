# Merchant go-live checklist

- [ ] Credentials stored only in server env (not frontend)
- [ ] Demo vs owned credentials not mixed
- [ ] Health endpoint verifies HMAC and returns capabilities
- [ ] Consume calls Hub `tokens/validate` (does not trust browser email)
- [ ] Local session uses Hub-bound identity email only
- [ ] Subscription sync verifies signature
- [ ] Poll cron configured
- [ ] Shutdown UI blocks login when expired
- [ ] Stale `active` cannot overwrite newer `expired`
- [ ] Hub Check Connection succeeds
- [ ] Demo / owned SSO smoke-tested
- [ ] HTTPS site URL only
