# 7th Trade Hub — Design System

## Governance (read first)

**No raw Tailwind for shared UI.** Buttons, cards, alerts, tables, badges, inputs, page headers, empty states, pagination, nav items, stat cards, modals, skeletons, and breadcrumbs must use `<x-ui.*>` components.

Raw Tailwind is allowed only for:
- Page-specific layout grids unique to one screen
- Marketing hero sections (decorative blurs/gradients)
- One-off illustration placement

**Marketing typography exception:** marketing layouts/pages may use `font-display` (Poppins) for brand display. Dashboard and admin stay **Inter only** (`font-sans`). Do not use Public Sans.

### PR checklist
- [ ] No new inline button/card/table classes — use `<x-ui.*>`
- [ ] No per-page flash paragraphs — session flashes via `<x-ui.toast>`
- [ ] Page width via `<x-layout.page width="...">` only (marketing uses `max-w-marketing`)
- [ ] Icons via `<x-ui.icon name="...">` only — no Material Symbols / icon fonts
- [ ] New shared components added to `/dev/ui` before page use
- [ ] Confirmations use `<x-ui.modal>` — never `confirm()`

### Banned patterns
- `material-symbols-outlined`
- `onsubmit="return confirm(...)"`
- Bare "No items found" — use `<x-ui.empty>`
- `hidden` on nav without mobile drawer alternative
- `font-display` / Public Sans on **admin/dashboard** (Inter only; Poppins OK on marketing)
- Ad-hoc `max-w-5xl` / `max-w-7xl` / `max-w-3xl` — use width tokens (`max-w-marketing`, `max-w-content-*`, `max-w-form`, `max-w-auth`)

---

## Semantic theme tokens

| Token | Use |
|-------|-----|
| `bg-surface` | Page background |
| `bg-card` / `glass-card` | Glass cards |
| `bg-card-solid` | Solid admin cards |
| `bg-muted` | Zebra rows, subtle fills |
| `bg-elevated` | Dropdowns, modals, sidebars |
| `text-text-primary` | Headings, body |
| `text-text-secondary` | Labels |
| `text-text-muted` | Placeholders |
| `border-border-default` | Borders |
| `border-border-subtle` | Glass borders |

Brand: `primary`, `accent`, `success`, `warning`, `danger`.

---

## Layout widths

| `width` prop | Token | Use |
|--------------|-------|-----|
| `content-sm` | `max-w-content-sm` | Narrow prose |
| `content-md` | `max-w-content-md` | Ticket threads |
| `content` | `max-w-content` | Default dashboard |
| `content-lg` | `max-w-content-lg` | Analytics |
| `full` | `max-w-content-full` | Data tables |
| `form` | `max-w-form` | Create/edit forms |
| `auth` | `max-w-auth` | Login, OTP |

---

## Page grid (mandatory)

```
PageHeader → Toast (layout) → Breadcrumb → Content → Pagination
```

```blade
<x-layout.page title="Wallet" subtitle="..." width="content" :breadcrumb="[...]">
    <x-slot:actions>...</x-slot:actions>
    {{-- content --}}
    <x-slot:pagination>
        <x-ui.pagination :paginator="$items" />
    </x-slot:pagination>
</x-layout.page>
```

Section spacing: `space-y-section` (1.5rem).

---

## Components (`resources/views/components/ui/`)

| Component | Notes |
|-----------|-------|
| `button` | `variant`, `size`, `loading`, `icon`, `href` |
| `input` / `select` / `textarea` | Label + error |
| `card` | `glass` \| `solid` |
| `alert` | Inline form errors only |
| `toast` | Layout-level; 5s dismiss; top-right |
| `modal` | `open-modal` Alpine event; optional `form-action` |
| `table` / `th` / `td` | Sticky header, skeleton, empty |
| `badge` | Status pills |
| `empty` | Icon + title + description + CTAs |
| `pagination` | Paginator wrapper |
| `page-header` | Used by layout.page |
| `stat-card` / `stat-grid` | min-h 120px; 1→2→4 cols |
| `breadcrumb` | |
| `icon` | SVG from `resources/icons/` |
| `nav-link` | Sidebar items |
| `skeleton.*` | page-header, stat-grid, table, card |

Layouts: `x-layout.page`, `x-layout.app`.

### Button loading

```blade
<form x-data="{ submitting: false }" @submit="submitting = true">
    <x-ui.button type="submit" x-bind:disabled="submitting" icon="check">Save</x-ui.button>
</form>
```

Label stays the same; show spinner via `loading` prop or Alpine disabled + icon swap.

### Modal

```blade
<x-ui.button type="button" @click="$dispatch('open-modal', 'approve-funding')">Approve</x-ui.button>
<x-ui.modal name="approve-funding" title="Approve deposit?" confirm-label="Approve"
    :form-action="route('admin.fundings.approve', $f)">
    Approve ₦{{ number_format($f->amount, 2) }}?
</x-ui.modal>
```

---

## Icons

SVG files in `resources/icons/`. Use `currentColor`.

```blade
<x-ui.icon name="wallet" class="w-5 h-5" />
```

### Icon aliases

These filenames are reserved aliases (same visual family); prefer the primary name in new code:

| Alias | Prefer |
|-------|--------|
| `close` | `x` |
| `person` | `user` |
| `bookmark` | `watchlist` |
| `credit-card` | `wallet` |
| `grid` | `dashboard` |
| `tune` | `settings` |
| `info` / `warning` / `trash` | keep for alert/modal/danger actions |

---

## CLS policy

- Stat cards: `min-h-[120px]`
- Paginated tables: `min-h-[400px]`
- Page header: `min-h-[72px]`
- Buttons: fixed heights per size (`h-9`/`h-10`/`h-11`)
- Sidebar: fixed `w-64`; mobile drawer overlay (does not push content)
- Toast: fixed portal — no layout shift

---

## Responsive rules

- Sidebar → Alpine drawer on mobile (never hide without alternative)
- Tables → horizontal scroll inside component
- Stat grid → `grid-cols-1 sm:grid-cols-2 xl:grid-cols-4`
- Header actions stack on small screens

---

## Styleguide

Local only: `GET /dev/ui` (`App\Http\Controllers\Dev\DevUiController`) — **404 outside `local`**.

Any new `<x-ui.*>` component must appear on `/dev/ui` before production use.

`<x-layout.app>` exists as a thin optional wrapper; dashboards continue to `@extends` `layouts.dashboard-user` / `dashboard-admin`. Prefer those shells; do not duplicate another app chrome.

