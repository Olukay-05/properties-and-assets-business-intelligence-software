# Screen Spec: UJ-1 Property Dossier & Scoped Asset Register (`Registry/PropertyDetail.tsx`)

Status: ready for dev
Owner: UX designer agent (Sally)
Related story or epic: UJ-1 (FR-1, FR-2, FR-3, FR-4, FR-5, FR-6, FR-7, FR-8, FR-19, FR-20)
Design context version or date: 2026-11-03 (`docs/design/design-context.md`)
Interactive HTML Prototype: [property-asset-register.html](../mockups/property-asset-register.html)

## 1. Purpose
- User goal: View and verify a registered client property's canonical location and NIPOST postcode status, manage site zones, register site assets, and assign an accountable custodian to each asset with full append-only location and custody history.
- Context of use (who, where, device, connectivity): Operations Manager (`Chidi Okafor`) on an office desktop browser (`1024px` to `1440px+`) in Lagos over broadband/Wi-Fi, or Site Supervisor (`Amara Bello`) checking site assets and zones on a mobile phone browser (`320px` to `760px`) on-site over 3G/4G.
- Business goal and success metric: Onboard a new client property, zones, and first 10 assets with assigned accountable custodians in under 15 minutes with zero cross-tenant or cross-client data leakage and 100% distinction between Accountable custodian (`asset_custody`) and Assigned technician (`work_orders`).
- Entry points:
  1. Clicking a property row or map pin on `Registry/PropertiesIndex.tsx` (`/properties`).
  2. Redirect after submitting the `Register property` form (`/properties/[uuid]`).
  3. Deep link from an Inspection, Work Order, or Client Report property reference.
- Exit points and next screens:
  1. Clicking an asset tag (`LVE-GEN-01`) navigates to `Registry/AssetDetail.tsx` (`/assets/[uuid]`).
  2. Clicking `Schedule inspection` navigates to `Inspections/InspectionsIndex.tsx` pre-filtered to this property.
  3. Switching Client Account in `app-shell-header` reloads the workspace for the newly selected Client Account (`preserveState: false`).

## 2. Layout and hierarchy
- Primary action: `Register asset` (`.cc-button--primary`, one primary action on the surface).
- Secondary actions: `Update GPS location` (`.cc-button--secondary`), `Schedule inspection` (`.cc-button--secondary`).
- Tertiary actions: `Clear filters` (`.cc-button--text`), row-level `Handover` and `Edit` compact buttons (`.cc-button--small`).
- Reading order and focal point:
  1. Skip link (`.cc-skip-link` "Skip to main content") and top `app-shell-header` with active Client Account scope.
  2. `page-header` displaying the property kicker, H1 site title (`Victoria Island Retail Plaza`), 1px blue rule, italic address subtitle, NIPOST postcode status badge + mandatory non-title disclaimer, and primary action bar.
  3. 4-card executive summary strip (`editorial-card`, with `.cc-card--emphasis` on `Registered site assets`).
  4. Single `.cc-callout` (`recommendation-callout`) surfacing the operational portfolio note.
  5. Two-column Geospatial & Append-Only Location History section (`property-map` on left, `Location history` `data-table` on right).
  6. Horizontal `.cc-tabs` (`Assets register`, `Site zones`, `Inspections`, `Work orders`, `Location history`) with `filter-bar`, `data-table`, and `pagination`.

| Breakpoint | Layout notes |
|---|---|
| 320 to 767 | `.cc-page` uses `32px 22px` padding. Top nav condenses to compact Client Account badge + `40px x 40px` Menu button. Summary cards and the Map + Location History grid stack into 1 vertical column. `.cc-input`, `.cc-select`, and `.cc-dropdown__trigger` use `16px` font size. `.cc-table-wrap` scrolls horizontally with scroll-shadow gradients and sticky first column (`td:first-child`). Pagination cells expand to `40px x 40px`. |
| 768 to 1023 | Full `app-shell-header` visible. Summary cards render in a 2x2 grid. Map and Location History stack or split `1fr 1fr` at `>=920px`. |
| 1024 and up | Centered `1120px` `.cc-page` with `56px 64px` padding over `#F2F4F6` backdrop. 4-column summary card strip; `1.1fr 0.9fr` split for `<PropertyMap>` and Location History; full horizontal Asset Register table. |

## 3. Components
| Region | Component | Variant and size | Tokens or notes |
|---|---|---|---|
| Skip navigation | `.cc-skip-link` | Hidden until focused | `--cc-navy-2` bg, `--cc-white` text, `z-index: 100` |
| App shell | `app-shell-header` | Desktop 64px bar | `{components.app-shell-header.*}`, includes Tier-2 `dropdown-editorial` Client account scope switcher |
| Dossier header | `page-header` | H1 `32px` + rule + subtitle | `{components.page-header.*}`, includes `.cc-status--success` (`Postcode verified (NIPOST)`) + non-title disclaimer |
| Summary metrics | `editorial-card` | 4-column grid, 1 `--emphasis` | `{components.editorial-card.*}`, `tabular-nums` on values |
| Portfolio note | `recommendation-callout` | Single callout per page | `{components.recommendation-callout.*}` (`#EAF3F4`, `0px` radius) |
| Geospatial panel | `property-map` | `320px` height frame | `{components.property-map.*}` + keyboard/input coordinate alternative (SC 2.5.7) |
| Location history | `data-table` | Append-only descending | `{components.data-table.*}`, latest row marked `Current` |
| Section navigation | `tabs` | 5 tabs, `40px` min-height | `{components.tabs.*}`, roving `tabindex`, `aria-controls` |
| Asset filtering | `filter-bar` | Search + Tier-1 select + Tier-2 custodian combobox + `.cc-filter` chips | `{components.filter-bar.*}`, `--cc-border-control` (`#6E7E91`, `3.95:1`) |
| Scoped asset register | `data-table` | 7 columns, sortable | `{components.data-table.*}`, sticky first column on mobile |
| Table footer | `pagination` | `34px` desktop / `40px` mobile | `{components.pagination.*}`, `<nav aria-label="Pagination">` |
| Location /Asset modals | `modal-dialog` | Max 1 level deep | `{components.modal-dialog.*}`, focus trap + Escape close |

New components or tokens needed (with reason):
- `--cc-border-control: #6E7E91` (`{colors.border-control}`) added to guarantee `3.95:1` non-text contrast against `#FFFFFF` on all inputs, selects, comboboxes, filter chips, and pagination cells per WCAG 2.2 AA SC 1.4.11.

## 4. State matrix
| State | Trigger | What the user sees | Copy IDs |
|---|---|---|---|
| Default | Property loaded with zones, location history, and 18 assets | Full property dossier, active `<PropertyMap>` pin, 3 location readings, and paginated Asset Register table | `uj1.header.title`, `uj1.nipost.badge`, `uj1.nipost.disclaimer` |
| Loading first load | Initial route visit or Client Account scope switch | Page header structure preserved; 44px pulsing `#F4F7FA` skeleton bars inside summary cards, map box, and 6 table rows (zero CLS) | `uj1.loading.label` |
| Loading refresh | Tab switch (`only: ['assets']`) or filter change | Existing table remains visible at `0.6` opacity with a polite status indicator `Updating asset register…` | `uj1.loading.refresh` |
| Empty first use | New property registered with 0 assets and 0 zones | Quiet `.cc-card` inside `Assets register` tab explaining the register purpose and showing one `.cc-button--primary` (`Register first asset`) | `uj1.empty.first.title`, `uj1.empty.first.body`, `uj1.empty.first.cta` |
| Empty no results | Search or filter chip matches 0 assets | Quiet `.cc-card` stating the active filter criteria and offering `Clear filters` (`.cc-button--secondary`) | `uj1.empty.filter.title`, `uj1.empty.filter.body`, `uj1.empty.filter.cta` |
| Error recoverable | `HTTP 422` on `Register asset` or `Update GPS location` (e.g., duplicate asset tag `LVE-GEN-01`) | 3-part `.cc-status--danger` alert strip at top of form + `2px solid #A34545` border on invalid input (`aria-invalid="true"`) + inline `.cc-error` message; user input preserved | `uj1.err.422.summary`, `uj1.err.422.tag` |
| Error fatal | `HTTP 500` or unexpected service failure | Editorial error card with 3-part message, reference code, and `Retry loading property` button | `uj1.err.500.body` |
| Partial | MapLibre vector tiles fail to load on slow connection | `<PropertyMap>` frame renders a structured fallback coordinate table (`6.4284° N, 3.4222° E · ±5 m`) with `Retry map tiles` button; rest of dossier works normally | `uj1.partial.map.fallback` |
| Offline | Browser loses network while viewing or editing | Top polite warning banner (`Offline: viewing cached property dossier. Changes cannot be saved until connection returns.`) | `uj1.offline.banner` |
| Success | New asset registered or GPS location updated | Polite status banner (`Location updated to 6.4284° N, 3.4222° E. Previous reading preserved in Location history.`) | `uj1.success.location` |
| Disabled or permission denied | Authenticated as `Technician` (read-only on registry) or out-of-scope UUID | For `Technician`: mutation buttons (`Register asset`, `Update GPS location`, `Handover`) are hidden, replaced by read-only badge `Read-only access (Technician role)`. Out-of-scope UUID returns `404`. | `uj1.perm.readonly` |
| Extreme data (0, 1, 1000s, long text) | 1,000+ assets or 120-character Nigerian landmark address | Table paginates at 20 rows per page (`tabular-nums`); long addresses wrap cleanly within `max-width: 75ch` without breaking grid alignment | `uj1.pagination.summary` |

## 5. Interaction and behavior
- Click, tap and keyboard behavior per control:
  - Client account scope switcher (`dropdown-editorial`) and Custodian filter open on click, `Enter`, `Space`, or `ArrowDown`, navigate options with `ArrowDown`/`ArrowUp`/`Home`/`End`, select with `Enter`, and close with `Escape` (returning focus to trigger).
  - `.cc-tabs` switch active panel on click and move focus across tabs with `ArrowLeft`/`ArrowRight`.
  - `.cc-filter` chips toggle `aria-pressed="true|false"` and filter table rows immediately.
- Hover alternatives for touch: All table row actions (`Handover`, `Edit`) are permanently visible in the right-hand `Row actions` column, never hidden behind hover.
- Gestures and their visible alternatives (WCAG 2.2 SC 2.5.7): `<PropertyMap>` pin placement provides explicit `Latitude`, `Longitude`, and `Use current device GPS` controls inside the `Update GPS location` modal so no user is forced to drag a map pin.
- Validation rules and timing: Validate format on blur (latitude `-90` to `90`, longitude `-180` to `180`), completeness and uniqueness on submit. Never clear form inputs on error.
- Focus management: Opening `Update GPS location` or `Register asset` modal moves focus to the modal title/first input, traps Tab inside, and restores focus to the trigger button on `Escape` or close.
- Motion: `150ms ease` on button/input borders and dropdown chevron rotation; disabled completely under `@media (prefers-reduced-motion: reduce)`.
- Data saved and when: Explicit server commit via `LocationService::setLocation()` and `AssetService::register()` with atomic `AuditService::record()` logging (`AD-1`, `AD-7`, `AD-8`, `AD-18`).

## 6. Copy deck
| ID | Location | State | Text | Notes |
|---|---|---|---|---|
| `uj1.header.kicker` | `page-header` kicker | Default | `PORTFOLIO & PROPERTIES · SITE DOSSIER · LVE-VI-01` | Uppercase Georgia 14px `#126E6A` |
| `uj1.header.title` | `page-header` H1 | Default | `Victoria Island Retail Plaza` | Arial 32px bold `#17365D` |
| `uj1.header.subtitle` | `page-header` subtitle | Default | `14 Adeola Odeku Street, Victoria Island, Lagos · Postcode 101241` | Arial 20px italic `#365F91` |
| `uj1.nipost.badge` | `page-header` meta | Default | `Postcode verified (NIPOST)` | `.cc-status--success` |
| `uj1.nipost.disclaimer` | `page-header` meta | Default | `Identifies postal routing reference only. Does not verify property title, ownership, or legal occupancy.` | Mandatory legal guardrail (PRD §6.2) |
| `uj1.cta.primary` | `page-header` actions | Default | `Register asset` | `.cc-button--primary` (sole primary button) |
| `uj1.cta.gps` | `page-header` actions | Default | `Update GPS location` | `.cc-button--secondary` |
| `uj1.cta.inspect` | `page-header` actions | Default | `Schedule inspection` | `.cc-button--secondary` |
| `uj1.empty.first.title` | Asset register table | Empty first use | `No assets registered for Victoria Island Retail Plaza yet` | H3 `#17365D` |
| `uj1.empty.first.body` | Asset register table | Empty first use | `Register the first site asset (such as a standby generator, HVAC chiller, or lift) to assign an accountable custodian and include it in scheduled inspections.` | Georgia 16px |
| `uj1.empty.first.cta` | Asset register table | Empty first use | `Register first asset` | `.cc-button--primary` |
| `uj1.empty.filter.title` | Asset register table | Empty no results | `No assets match your active filters` | H3 `#17365D` |
| `uj1.empty.filter.body` | Asset register table | Empty no results | `No registered assets matched the current search or filter selection. Try another keyword or reset the filters.` | Georgia 16px |
| `uj1.empty.filter.cta` | Asset register table | Empty no results | `Clear filters` | `.cc-button--secondary` |
| `uj1.err.422.summary` | Register asset form | Error 422 | `Could not save asset (validation_failed). Asset tag LVE-GEN-01 is already registered on this property. Enter a unique asset tag such as LVE-GEN-02 and save again.` | 3-part error formula (`role="alert"`) |
| `uj1.err.422.tag` | Asset tag field | Error 422 | `Asset tag LVE-GEN-01 already exists at Victoria Island Retail Plaza. Enter a unique tag.` | `.cc-error` linked via `aria-describedby` |
| `uj1.partial.map.fallback` | `<PropertyMap>` frame | Partial (tiles offline) | `Map tiles unavailable on current connection. Canonical coordinates remain active: 6.4284° N, 3.4222° E (±5 m).` | Graceful fallback table |
| `uj1.perm.readonly` | Header notice | Permission restricted | `Viewing in read-only mode (Technician role). Only Operations Managers and Organisation Owners may register assets or update property coordinates.` | `.cc-status--neutral` strip |

## 7. Accessibility notes
- Heading outline and landmarks: `<a class="cc-skip-link">` -> `<header role="banner" class="app-shell-header">` -> `<main id="main-content" class="cc-page">` -> single `<h1>` (`Victoria Island Retail Plaza`) -> `<h2>` (`Geospatial coordinates & append-only location history`, `Site zones & scoped asset register`) -> `<h3>` inside modals/empty states.
- Accessible names for non-text controls: Map zoom buttons have `aria-label="Zoom in map"` and `aria-label="Zoom out map"`; table sort headers expose `aria-sort="ascending|none"`.
- Keyboard map for custom widgets: `.cc-dropdown` combobox supports `ArrowDown`, `ArrowUp`, `Home`, `End`, `Enter`, `Space`, `Escape`. `.cc-tabs` supports `ArrowLeft`, `ArrowRight`, `Home`, `End`.
- Announcements: Filter count changes and state switcher updates announce via `role="status" aria-live="polite"`; validation/domain errors announce via `role="alert"`.
- Contrast pairs verified: `#14243A` on `#FFFFFF` (`15.8:1`), `#17365D` on `#FFFFFF` (`12.1:1`), `#365F91` on `#FFFFFF` (`6.68:1`), `#126E6A` on `#FFFFFF` (`5.86:1`), `#6E7E91` input border on `#FFFFFF` (`3.95:1`).

## 8. Performance and offline
- Loading strategy: Server-rendered Inertia props for initial property + first 20 assets; MapLibre GL JS bundle loaded asynchronously so H1 and Asset Register table paint in `< 1.5s`.
- Budget concerns: Total initial JS `< 170 KB` gzipped; zero webfont requests.
- Offline and retry behavior: If map tiles fail, coordinate table remains readable; if mutation fails due to network drop, modal retains entered form fields for immediate retry.

## 9. Analytics
| Event | Trigger | Properties |
|---|---|---|
| `property.location_updated` | `LocationService::setLocation()` commit | `property_id`, `source` (`browser_gps` \| `manual_pin`), `accuracy_m` (no PII) |
| `asset.registered` | `AssetService::register()` commit | `property_id`, `category`, `condition` |
| `asset.custody_assigned` | `AssetService::assignCustody()` commit | `asset_id`, `property_id` |

## 10. Acceptance criteria
1. Built from this screen spec; all 7 interactive states in the prototype (`Default`, `Loading`, `Empty`, `Error 422`, `Offline / Map Fallback`, `Read-Only Role`, and `Update Location Modal`) are implemented and reachable.
2. Uses Cloud Crafters Editorial Intelligence tokens exclusively (`--cc-border-control: #6E7E91` on all inputs/selects/chips for `>=3:1` WCAG 2.2 SC 1.4.11 contrast; zero raw hex or pixel literals in React components).
3. Displays the mandatory NIPOST non-title disclaimer whenever `Postcode verified (NIPOST)` is shown.
4. Updating property coordinates appends a new row to `locations` (`Location history` table) and never overwrites prior coordinate readings.
5. Accountable custodian (`asset_custody`) is displayed and edited distinctly from maintenance technician (`work_orders`).
6. Entire flow is operable by keyboard alone with visible `:focus-visible` outlines, `.cc-skip-link`, `scroll-padding-top: 80px`, and clean axe/Lighthouse accessibility scans at `320px` and `1440px`.

## 11. Open questions and assumptions
| Item | Type (question, assumption) | Owner | Status |
|---|---|---|---|
| `OQ-UX-1`: OpenFreeMap tile commercial volume terms | Question (PRD §10 #2) | PM / Architect | Open (mitigated by `<PropertyMap>` encapsulation and coordinate-table fallback) |

