# Operator guide — Site Integrations

## Demo Site Integrate

Path: **Admin → System → Demo Site Integrate**

1. Click **Add integration**.
2. **Select product** from existing Website Package products only.
3. Enter the independent demo site **HTTPS base URL**, demo user email, demo admin email.
4. **Generate API keys** — copy Integration ID, Client ID, Client Secret, Webhook URL/Secret once.
5. Give credentials to the external site developer (their `.env`).
6. Click **Check connection**. Fix errors using the connection log.
7. Set status to **active** when healthy.

Never reuse these credentials for a customer purchase. Base URLs must be HTTPS and publicly reachable (private/localhost URLs are rejected).

**Before Check connection:** confirm the merchant site has **local user accounts** for the demo user and demo admin emails you configure — Hub SSO binds to those addresses but does not create users on the merchant site.

## My Tools (user)

Path: **Dashboard → Services → My Tools**

- After buying an internal catalog service (website packages require quantity **1**), a tool appears as **Pending setup**.
- Orders remain under **Service orders**; My Tools is ownership/access.
- When configured: site URL, admin URL, admin email, **Copy password** (POST only, active+live subscriptions), **Login as admin** (auto session).
- **Expiring soon** is shown when active and ≤ 7 days remain — not a stored status.
- **Renew** extends the same tool instance (quantity 1).

## Admin user Tools tab

Path: `/admin/users/{id}/tools`

1. For **pending** tools, click **Setup** (starts subscription clock once).
2. Enter HTTPS site URL, admin login URL, admin email, admin password.
3. Save generates **unique provisioning credentials** and runs Check Connection + subscription push outside the DB write.
4. For already-configured tools use **Reconfigure** (URLs/email/password — does **not** reset `expires_at`) or **Rotate credentials** (new keys — does **not** extend subscription).

Ensure the Setup **admin email** exists as an admin user on the merchant site before testing Login as admin.

## Expiry job

Scheduled: `site-integrations:expire-user-tools` every five minutes.

Marks expired tools (with `lockForUpdate`) and pushes `status=expired`. Hub also refuses launch/poll when `expires_at` is past even before the job runs. Sites must still poll Hub.

Hostinger / shared hosting must run `php artisan schedule:run` via cron.

## Docs for merchants

Share the public URL with external developers:

```text
{HUB}/developers/integrations
```

Repository copies (same content):

- [MERCHANT-GUIDE.md](./MERCHANT-GUIDE.md)
- [ENDPOINTS-REFERENCE.md](./ENDPOINTS-REFERENCE.md)
- [PROTOCOL-v1.md](./PROTOCOL-v1.md)
- [openapi.yaml](./openapi.yaml)
- [samples/](./samples/)
