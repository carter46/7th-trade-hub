# Domain security

## Customer exposure

- API responses exclude provider identity and wholesale cost.
- Blade/JS must not render reseller names in purchase flows.

## Quote tokens

- 64-char random plain token; stored as hash only.
- Bound to `user_id`, `platform_product_id`, `fqdn`, `provider_key`.
- Single consumption; expiry enforced.

## Purchase integrity

- Price taken from server quote re-validation, not browser-submitted amounts.
- Drift tolerance configurable (`domains.price_drift_tolerance_percent`).
- Provider switching forbidden after quote issuance.

## Admin credentials

- `domain_providers.credentials` encrypted at rest.
- Test connection updates `health_status` only; no credential echo in UI.

## Fail closed

If no enabled provider succeeds → unavailable message, no fallback to catalog fixed prices.
