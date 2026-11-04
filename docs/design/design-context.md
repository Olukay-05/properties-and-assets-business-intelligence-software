# Project Design Context

Owner: UX designer agent (updates), all agents (read). Location in project: `docs/design/design-context.md`.
This file overrides the generic defaults of the enterprise-ui-ux skill, except its non-negotiables.

## 1. Product snapshot
- Product name: Cloud Crafters Property & Asset Intelligence
- One sentence purpose: Multi-tenant operational platform for Nigerian facilities-management (FM) providers to register client properties and assets, run mobile inspections and maintenance work orders, capture timestamped photo and GPS evidence, and compile defensible client reports.
- Primary users and their context of use (device, location, connectivity, urgency):
  1. Operations Manager (`Chidi Okafor`): Desktop browser (`>760px`) in a Lagos office over Wi-Fi or broadband. Onboards client accounts, registers properties and assets, assigns accountable custodians, schedules inspections, oversees work orders, and compiles/exports monthly client PDF and CSV reports.
  2. Site Supervisor (`Amara Bello`): Mobile field browser (`<=760px`, mid-range Android or iOS) on-site in generator houses, plant rooms, and commercial compounds over fluctuating 3G/4G networks. Runs scheduled inspection checklists, captures photo evidence, updates asset condition, raises maintenance requests, and approves completed inspections.
  3. Technician and Contractor (`Tunde Adeyemi`): Mobile field browser (`<=760px`, mid-range Android or iOS) on-site over variable mobile signal and indoor dead zones. Reviews assigned work orders, logs honest multi-visit partial progress (`In progress` or `On hold` with delay reasons), and completes work orders with mandatory photo evidence.
- Secondary users (admins, reviewers, support):
  1. Organisation Owner: Desktop and mobile web. Manages organisation memberships, assigns multi-role permission sets, binds `client_scope` per user, and reviews append-only audit logs and NDPA 2023 compliance requests.
  2. Platform Admin: Desktop web. Supports tenant onboarding and compliance auditing strictly through an audited membership in the target organisation (never via runtime RLS bypass).
- Success metrics (task success, conversion, time on task, retention):
  - Onboarding and register completion (`UJ-1`): Operations Manager registers a client account, property coordinates, zones, and first 10 assets with assigned custodians in under 15 minutes with zero cross-tenant or cross-client leakage.
  - Field inspection completion (`UJ-2`): Site Supervisor completes a 25-item mobile checklist with photo attachments and inline maintenance request creation in under 8 minutes on a throttled 4G connection without losing draft answers.
  - Work order evidence integrity (`UJ-3`): 100% of completed work orders include at least one timestamped completion photo; zero work orders are falsely marked completed when parts or access are pending (`On hold` path used instead).
  - Client report compilation (`UJ-4`): Operations Manager compiles a single-client monthly report preview in under 2 seconds and exports PDF/CSV within 30 seconds via async queue polling, with explicit zero-activity certification when no visits occurred.
  - Counter-metrics (`SM-C1..SM-C3`): Never incentivize raw photo spam, unverified junk asset rows, or rushed 10-second checklist tap-throughs.

## 2. Platforms and tech
- Platforms: Responsive web application (`>760px` desktop editorial canvas and `<=760px` mobile field browser for Android and iOS). No native mobile app, desktop app, or client self-service portal in Phase 1.
- Framework and UI libraries: Laravel 12 + React 19 + TypeScript 5 + Inertia 3 (`@inertiajs/react`) + Tailwind CSS 4 + `shadcn/ui` primitives strictly reskinned to the Cloud Crafters Editorial Intelligence token set + MapLibre GL JS 5 (`<PropertyMap>` with OpenFreeMap vector tiles) + Spatie Laravel Data DTOs (`resources/js/types/generated`).
- Component library status: Mature specification (21 canonical `.cc-*` components mapped 1:1 between `DESIGN.md`, `EXPERIENCE.md`, and `cloud-crafters-design-system.css`).
- Theming: Light mode only (`#FFFFFF` editorial page canvas over `#F2F4F6` shell backdrop). Dark mode is intentionally out of scope to preserve the printed property-strategy report aesthetic and outdoor sunlight legibility.
- Languages and locales: `en-NG` (Nigerian English). Plain, direct operational English at grade 7 to 8 reading level. String templates must tolerate 30% to 40% length expansion for future localization.
- Currency, date and number formats:
  - Currency: Nigerian Naira (`NGN`, symbol `₦` before amount, comma thousands separators, e.g., `₦145,000` or `₦145,000.00`).
  - Dates and times: Stored in UTC, always displayed in `Africa/Lagos` (`WAT`, `UTC+1`) using unambiguous `DD MMM YYYY, HH:mm WAT` format (e.g., `03 Nov 2026, 14:20 WAT`).
  - Distances and accuracy: Computed via PostGIS geography casts and displayed in metres (`m`, e.g., `±8 m` or `142 m`).
  - Numerals: `font-variant-numeric: tabular-nums` required on all table numeric columns, coordinates, timestamps, and card metrics.

## 3. Brand and visual direction
- Personality in five adjectives: Authoritative, calm, legible, evidence-led, architectural.
- Anti-personality (what we must not feel like): Consumer SaaS dashboard, gamified app, neon/gradient marketing template, cluttered spreadsheet, or rounded pill-heavy social feed.
- Logo and brand assets location: `docs/cloud_crafters_design_system/` (`DESIGN_SYSTEM.md`, `cloud-crafters-design-system.css`, `design-system-preview.html`).
- Brand colors (hex, with roles):
  - `#FFFFFF` (`--cc-white` / `--cc-page` / `--cc-surface`): Default page canvas, card surface, and odd table rows.
  - `#F2F4F6` (`--cc-shell-backdrop`): Outer desktop viewport backdrop behind the 1120px `.cc-page` sheet.
  - `#F4F7FA` (`--cc-row`): Even table row fill, skeleton bar base, and dropzone well.
  - `#14243A` (`--cc-navy`): Ink navy for primary body copy, table cell copy, and solid table header (`<th>`) background (`15.8:1` on white).
  - `#17365D` (`--cc-navy-2`): Heading navy for page titles (`h1`), subsection headings (`h3`), form labels, card values, and primary buttons (`12.1:1` on white).
  - `#4F81BD` (`--cc-blue`): Editorial blue for section headings (`h2`, `>=24px` bold), `1px` signature horizontal rules (`.cc-rule`), active tab underlines, and `.cc-card--emphasis` top borders (`4.02:1` on white, large text and structural rules only).
  - `#365F91` (`--cc-blue-dark`): Interactive blue dark for italic page subtitles, inline links, text buttons, primary button hover, focus outline, and info badge foreground (`6.68:1` on white).
  - `#126E6A` (`--cc-teal`): WCAG-hardened accent teal for uppercase kickers, recommendation labels, card eyebrows, and positive confirmation buttons (`.cc-button--teal`, `5.86:1` on white, `5.19:1` on `#EAF3F4`; hover `#0E5855` `7.61:1`). Legacy print token `#168C88` (`--cc-teal-legacy`) is retained only for reference.
  - `#EAF3F4` (`--cc-teal-wash`): Pale teal wash reserved for at most one `.cc-callout` recommendation box or ready export banner per screen.
  - `#263244` (`--cc-line`): Strong `1px` table gridlines, shell header dividers, and modal borders.
  - `#C7D1DC` (`--cc-border`): Quiet `1px` structural border for cards, tab bars, and section dividers.
  - `#6E7E91` (`--cc-border-control`): Interactive form control border (`3.95:1` against `#FFFFFF`, satisfying WCAG 2.2 AA SC 1.4.11 `3:1` non-text contrast) for `.cc-input`, `.cc-select`, `.cc-textarea`, `.cc-dropdown__trigger`, and `.cc-filter` chips; hover `#526071` (`--cc-border-hover`).
  - `#5F6F82` (`--cc-muted`): Secondary descriptions, captions, helper hints, and timestamps (`5.18:1` on white).
- Type families and fallbacks:
  - Display, headings, and UI controls: `Arial, Helvetica, sans-serif` (zero external webfont latency, crisp sans-serif hierarchy).
  - Editorial body, kickers, and data tables: `Georgia, 'Times New Roman', serif` (authoritative consulting-report prose and tabular legibility).
- Imagery and illustration style: Authentic operational evidence only (timestamped site/asset photos, MapLibre vector site maps, architectural diagrams). Zero decorative illustrations or stock cartoons in empty states.
- Reference products that feel right, and what specifically we borrow:
  - Management Consulting Property Strategy Dossiers (`DESIGN_SYSTEM.md`): Ink navy table headers with white Georgia text, `1px` blue rules under display titles, uppercase teal kickers, and a single pale teal executive callout.
  - GOV.UK Design System: High-contrast visible `<label for>` above every input, 3-part validation error summaries, native `<select>` preference on mobile, and never relying on color alone.
  - Linear / Stripe Table Discipline: URL-synced filters, deterministic keyboard navigation, tabular numerals, and zero decorative card chrome.

## 4. Design tokens
Source of truth files: `docs/cloud_crafters_design_system/cloud-crafters-design-system.css` and `_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/DESIGN.md`.

| Category | Decision |
|---|---|
| Color scales | Editorial report palette (`--cc-white` `#FFFFFF`, `--cc-navy` `#14243A`, `--cc-navy-2` `#17365D`, `--cc-blue` `#4F81BD`, `--cc-blue-dark` `#365F91`, `--cc-teal` `#126E6A`, `--cc-teal-hover` `#0E5855`, `--cc-teal-wash` `#EAF3F4`, `--cc-row` `#F4F7FA`, `--cc-shell-backdrop` `#F2F4F6`). |
| Semantic colors (bg, surface, text, border, accent, success, warning, danger, info) | Base semantic tokens (`--cc-success` `#126E6A`, `--cc-warning` `#94651D`, `--cc-danger` `#A34545`, `--cc-info` `#4F81BD`, `--cc-muted` `#5F6F82`) + WCAG 2.2 AA badge triples: Success (`#126E6A` on `#EAF3F1`, border `#C8E0DC`, `5.42:1`), Warning (`#795014` on `#F7F1E6`, border `#E8D9BA`, `6.15:1`), Danger (`#853838` on `#F8EEEE`, border `#EBCFCF`, `6.31:1`), Info (`#365F91` on `#EEF3F9`, border `#D4E0EF`, `5.89:1`), Neutral (`#5F6F82` on `#F1F3F5`, border `#DCE1E6`, `4.66:1`). Borders: `--cc-line` `#263244` (tables/modals), `--cc-border` `#C7D1DC` (quiet cards/dividers), `--cc-border-control` `#6E7E91` (`3.95:1` interactive inputs/selects/chips). |
| Type scale and line heights | Hero `42px` (`2.625rem`, `1.12`), Page H1 `32px` (`2rem`, `1.15`), Section H2 `24px` (`1.5rem`, `1.2`), Subsection H3 / Subtitle `20px` (`1.25rem`, `1.25`/`1.35`), Desktop body `18px` (`1.125rem`, `1.42`), Mobile body & mobile inputs `16px` (`1rem`, `1.42`), Table & UI controls `14px` (`0.875rem`, `1.35`), Compact badges/hints/eyebrows `12px` (`0.75rem`, `1.2` to `1.4`). Reading measure capped at `65ch` to `75ch`. |
| Spacing scale (base unit) | 4px base grid: `4px` (`--cc-space-1`), `8px` (`--cc-space-2`), `12px` (`--cc-space-3`), `16px` (`--cc-space-4`), `20px` (`--cc-space-5`), `24px` (`--cc-space-6`), `32px` (`--cc-space-8`), `40px` (`--cc-space-10`), `48px` (`--cc-space-12`), `64px` (`--cc-space-16`). Desktop `.cc-page` max-width `1120px` (`56px 64px` padding); mobile padding `32px 22px`. |
| Radius | Square `0px` (`rounded.none`) for tables, callouts, pagination cells, and export banners; sharp `2px` (`--cc-radius-sm`) for cards, buttons, inputs, selects, dropdowns, badges, and filter chips; `3px` (`--cc-radius`) max for modals and map frames. Pill radii (`9999px`) are banned. |
| Elevation | Level 0 (`box-shadow: none`) on all pages, cards, callouts, tables, and buttons. Level 1 (`--cc-shadow: 0 2px 8px rgba(20, 36, 58, 0.08)`) only on map overlays and sticky mobile quick-task bars. Level 2 (`--cc-shadow-popover: 0 8px 24px rgba(20, 36, 58, 0.14)`) only on dropdown menus, slide-over sheets, and modal dialogs. |
| Motion durations and easing | `--cc-transition: 150ms ease` for hover/focus/chevron feedback; `200ms ease-out` for drawers and modals. Full `@media (prefers-reduced-motion: reduce)` override disables transitions and pulsing animations. |
| Breakpoints | Mobile field browser: `320px` to `760px` (`@media (max-width: 760px)`). Tablet/compact desktop: `761px` to `1023px`. Full desktop editorial canvas: `1024px` to `1440px+` (`.cc-page` centered at `1120px`). |
| Z-index layers | Sticky table column: `z-index: 2`; Map overlay controls: `z-index: 10`; Sticky `app-shell-header` & `mobile-quick-task-bar`: `z-index: 30`; Dropdown menu (`.cc-dropdown__menu`): `z-index: 60`; Modal / slide-over backdrop & dialog (`.cc-modal`): `z-index: 90`; Skip link (`.cc-skip-link:focus`): `z-index: 100`. |

## 5. Components in use
All 21 canonical components are defined visually in `DESIGN.md` and behaviorally in `EXPERIENCE.md`:
1. `app-shell-header` (Desktop top bar with Client Account Scope Switcher — spec ready)
2. `mobile-quick-task-bar` (Mobile field bottom bar with 40px+ touch targets — spec ready)
3. `page-header` (Teal kicker, H1, 1px blue rule, italic subtitle, WAT metadata — spec ready)
4. `recommendation-callout` (Pale teal `#EAF3F4` square callout, max 1 per view — spec ready)
5. `data-table` (Ink navy header, 1px `#263244` grid, sticky first column on mobile, tabular numerals — spec ready)
6. `editorial-card` (Zero-shadow white card, optional 3px blue top emphasis on max 1 card per row — spec ready)
7. `button` (Primary, Secondary, Teal confirm, Danger, Text link; 40px height; `aria-busy` loading state — spec ready)
8. `form-field` (Visible `<label for>`, `#6E7E91` 3:1 control border, 16px mobile font size, inline `.cc-error` — spec ready)
9. `select-native` (Tier-1 native `<select>` with SVG chevron for standard single-choice fields — spec ready)
10. `dropdown-editorial` (Tier-2 `<button role="combobox">` with rich two-line options and status badges — spec ready)
11. `status-badge` (Rectangular `.cc-status--*` tag with text word + semantic color triple — spec ready)
12. `tabs` (`role="tablist"` with 40px `.cc-tab` buttons, roving `tabindex`, and Inertia partial reload — spec ready)
13. `filter-bar` (Debounced search, Tier-1 selects, 40px `.cc-filter` chips with `aria-pressed`, `Clear filters` — spec ready)
14. `pagination` (Square `.cc-page-link` cells, 34px desktop / 40px mobile, `Showing X to Y of Z` — spec ready)
15. `property-map` (`<PropertyMap>` MapLibre GL JS 5 wrapper with keyboard/coordinate pin alternative and offline table fallback — spec ready)
16. `evidence-uploader` (Client-side `<=1920px` WebP/JPEG compression, `role="progressbar"`, non-blocking GPS, interrupted blob retry — spec ready)
17. `evidence-gallery` (10-min signed R2 thumbnails, provenance modal, automatic 403 signed-URL refresh — spec ready)
18. `checklist-runner` (Pinned template version, `role="radiogroup"` 40px Pass/Fail/N/A buttons, sync badge, inline Maintenance Request drawer — spec ready)
19. `timeline-feed` (Descending append-only feed for locations, asset custody, work order events, and audit logs — spec ready)
20. `async-export-banner` (Inertia `usePoll(3000)` banner for Queued/Compiling, Ready with 10-min signed PDF/CSV links, and Failed states — spec ready)
21. `modal-dialog` (Focus-trapped dialog/drawer/sheet, max 1 level deep, Escape-close with focus return — spec ready)

## 6. Accessibility target
- Standard: **WCAG 2.2 Level AA** across all desktop and mobile web views.
- Extra commitments:
  - AAA contrast (`12.1:1` to `15.8:1`) on primary headings and body/table text.
  - Non-text contrast `>=3:1` (SC 1.4.11) on all interactive form controls via `--cc-border-control: #6E7E91` (`3.95:1` on white) and universal `:focus-visible` (`2px solid #365F91` + `3px` soft ring).
  - Skip link (`.cc-skip-link` "Skip to main content") as the first focusable element on every page.
  - Focus Not Obscured (WCAG 2.2 SC 2.4.11) enforced via `scroll-padding-top: 80px` and `scroll-padding-bottom: 72px` so sticky headers and mobile bottom bars never cover focused controls.
  - Dragging Movements (WCAG 2.2 SC 2.5.7) satisfied on `<PropertyMap>` by pairing map pin dragging with explicit latitude/longitude numeric inputs and a single-tap "Use current device GPS" button.
  - Target Size (WCAG 2.2 SC 2.5.8): `40px` minimum height on all primary controls, tabs, filter chips, radio buttons, and mobile pagination cells (`36px` min with `8px` gap on compact mobile table actions).
- Assistive tech test matrix:
  - Desktop: Keyboard-only navigation + NVDA (Chrome/Firefox on Windows) and VoiceOver (Safari on macOS).
  - Mobile: TalkBack (Chrome on Android) and VoiceOver (Safari on iOS) at `320px` width and `200%` text zoom.

## 7. Performance and network budgets
- Target devices: Mid-range Android phones (e.g., 3 GB RAM, 360x640 to 390x844 CSS px viewport, outdoor glare) and standard Windows/macOS office laptops.
- Network assumptions: Variable Nigerian 3G/4G mobile connections with ~110ms to 150ms RTT to EU Central (Frankfurt), intermittent packet loss, and indoor generator-room dead zones.
- Budgets:
  - Largest Contentful Paint (LCP): `< 2.5s` on throttled 4G.
  - Interaction to Next Paint (INP): `< 200ms`.
  - Cumulative Layout Shift (CLS): `< 0.1` (fixed aspect ratios on evidence thumbnails and map containers; 44px row-matched table skeletons).
  - Initial JavaScript: `< 170 KB` gzipped on core field entry routes (MapLibre lazy-loaded only on map views).
  - Fonts: `0 KB` external font weight (100% system `Arial` and `Georgia`).
  - Image uploads: Client-side canvas compression downscales 4 to 12 MB camera photos to `<= 1920px` WebP/JPEG (`~180 to 350 KB`) before upload.
- Offline needs:
  - Full offline-first sync is deferred from Phase 1, but resilient degraded-network handling is mandatory: `checklist-runner` autosaves draft responses locally and displays a live sync status badge (`All 5 responses saved` vs. `Offline: tap to retry sync`); `evidence-uploader` retains the compressed image blob in memory if upload drops and offers a one-tap `Retry upload` bar without losing form text.

## 8. Content and voice
- Voice and tone rules:
  - Calm, factual, operational, and defensible. Every screen and exported PDF should read like an engineering dossier suitable for a commercial property owner's billing review.
  - Sentence case for all headings, form labels, buttons, tabs, and menu items (capitalize only proper nouns, Nigerian place names, and acronyms like `NIPOST`, `GPS`, `WAT`, `NDPA`, `SLA`, `PDF`, `CSV`, `WO`).
  - Plain punctuation: never use em dashes or en dashes as punctuation in UI copy or specs. Use periods, colons, commas, or middots (`·`) for metadata separation.
  - Three-part error formula: every validation or domain error states (1) what happened, (2) operational impact, and (3) exact next action while preserving user input.
- Terminology list (preferred words, banned words):
  - Use **Accountable custodian** (stored in `asset_custody`), never confuse with **Assigned technician** (stored on `work_orders`).
  - Use **Client account**, **Property**, **Site zone**, **Asset**, **Inspection template**, **Maintenance request**, **Work order**, **Evidence attachment**.
  - Banned words in UI copy: "Oops", "Whoops", "Simply", "Just", "Submit", "OK", "Click here", or claiming that NIPOST postcode validation verifies property ownership/title.
- Legal copy that must appear:
  - NIPOST Postcode Guardrail (adjacent to any `Postcode verified (NIPOST)` badge): *"Identifies postal routing reference only. Does not verify property title, ownership, or legal occupancy."*
  - Immutable Inspection Notice (on approved inspections): *"Approved on [DD MMM YYYY, HH:mm WAT] by [Supervisor Name]. This record is immutable. To amend a finding, create a corrected version linked to this inspection."*
  - Zero-Activity Report Certification (when a client report period has 0 inspections and 0 work orders): *"No scheduled inspections or work orders were recorded for [Client Account] between [Start Date] and [End Date]. All [N] registered properties and [M] assets remain on the active register."*

## 9. Constraints and decisions log
Record each decision with date, decision, reason, and who made it. Newest first.

| Date | Decision | Reason | By |
|---|---|---|---|
| 2026-11-03 | Upgrade UX standard to WCAG 2.2 AA and `enterprise-ui-ux` SPEC mode (`--cc-border-control: #6E7E91` for `3.95:1` input contrast, `.cc-skip-link`, `scroll-padding`, `tabular-nums`, sentence case, zero em/en dashes, interactive multi-state HTML prototypes, and per-screen `screen-spec.md` files). | Aligns the Cloud Crafters Editorial Intelligence system with the project's newly installed `enterprise-ui-ux` skill and non-negotiables. | Olukayode + Sally (UX) |
| 2026-11-03 | Darken UI Accent Teal to `#126E6A` (`5.86:1` on white, `5.19:1` on `#EAF3F4`) and subtitle/link blue to `#365F91` (`6.68:1` on white). | Ensures 100% WCAG AA text contrast compliance across kickers, callout labels, subtitles, and `.cc-button--teal` buttons. | Sally (UX) |
| 2026-11-03 | Enforce `16px` (`1rem`) minimum font size on mobile prose and `.cc-input`/`.cc-select`/`.cc-textarea` (`<=760px`), while keeping `14px` for dense data table cells and secondary labels. | Prevents iOS Safari auto-zoom on field focus and satisfies `enterprise-ui-ux` mobile legibility rules without breaking dense tabular comparison. | Olukayode + Sally (UX) |
| 2026-11-03 | Use top editorial `app-shell-header` with Tier-2 Client Account Scope Switcher on desktop (`>760px`) and compact header + slide-over sheet + bottom `mobile-quick-task-bar` on mobile (`<=760px`). | Preserves the full 1120px `.cc-page` report width on desktop while giving field supervisors and technicians one-thumb navigation on mobile. | Olukayode + Sally (UX) |

## 10. Open questions
- **OQ-UX-1 (Map Provider Commercial Terms — PRD §10 #2)**: Confirm OpenFreeMap tile usage policy for commercial production volume before public launch (`<PropertyMap>` already encapsulates the tile URL and provides a coordinate-table fallback).
- **OQ-UX-2 (Evidence & Audit Retention Days — PRD §10 #7)**: Confirm exact numeric retention period (in days/years) with legal counsel so `Audit/AuditEventsIndex.tsx` displays the final statutory retention window under NDPA 2023.
