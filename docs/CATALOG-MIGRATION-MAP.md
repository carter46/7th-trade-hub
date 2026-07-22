# Catalog hierarchy migration map

Maps legacy layers → Service Categories / Services (`product_types`) / Products.

## Service Categories ← `config('catalog.groups')`

| Legacy group slug | Service Category name | mode |
|-------------------|----------------------|------|
| `network-services` | Network Services | catalog |
| `communication` | Communication | catalog |
| `social-media` | Social Media | catalog |
| `website-services` | Website Services | catalog |
| `business-documents` | Documents & Receipts | catalog |
| `trust-escrow` | Trust & Escrow | marketplace_link |

## Services ← `PlatformProductType` enum (under groups)

| Enum value | Service name | Parent category slug |
|------------|--------------|----------------------|
| `vpn` | VPN | network-services |
| `vps` | VPS | network-services |
| `smtp` | SMTP | network-services |
| `proxy` | Proxy | network-services |
| `email` | Email Services | communication |
| `virtual_phone` | Virtual Phone Numbers | communication |
| `social_service` | Social Media Services | social-media |
| `website_template` | Website Templates | website-services |
| `website_package` | Website Packages | website-services |
| `domain` | Domains | website-services |
| `document_template` | Document Templates | business-documents |
| `escrow_service` | *(skip — no products; Trust & Escrow is marketplace_link)* | — |

## Legacy `platform_categories` handling

| Legacy rows | Action |
|-------------|--------|
| VPN Residential/Business/Gaming/Dedicated | Stay as **product flavor context** only — products keep titles; do **not** create fourth-layer Services. Optional: ignore as Services. |
| Document Legal/Business/Personal/HR | Promote to **Services** under Documents & Receipts (replace single `document_template` mid-level for browse), **or** keep one Document Templates service and leave products as-is. **Chosen:** keep one Service per enum (Document Templates); category filters dropped. |
| Social Growth/Engagement | Drop as Services; products under Social Media Services. |
| All other type-scoped filters | Drop; products attach to enum-derived Service only. |

Backfill sets `platform_products.product_type_id` from legacy string `product_type` → matching `product_types.slug` (enum value used as slug).

## URL redirects (dual-read / cutover)

| Old | New |
|-----|-----|
| `/services` | unchanged (lists service_categories) |
| `/services/{groupSlug}` | `/services/{serviceCategory.slug}` |
| `/services/{enumType}` | `/services/{category}/{service}` or redirect via map |
| `/services/{enumType}/{productSlug}` | `/services/{category}/{service}/{productSlug}` or keep product show route with redirect |

## Provider defaults on backfill

All existing products: `provider = manual`, `fulfillment_mode = manual`, `auto_renew = false`.
