# Screen Spec: UJ-4 Monthly Client Report Compiler & Preview (`Reporting/ReportCompiler.tsx`)

Status: ready for dev
Owner: UX designer agent (Sally)
Related story or epic: UJ-4 (FR-17, FR-18, FR-19, FR-21, FR-22, AD-14, AD-15, AD-21, AD-22)
Design context version or date: 2026-11-03 (`docs/design/design-context.md`)
Interactive HTML Prototype: [client-report-compiler.html](../mockups/client-report-compiler.html)

## 1. Purpose
- User goal: Select a single Client Account and reporting period (`start_date` to `end_date`), preview the compiled monthly operational dossier on screen (properties covered, completed inspections, work orders by status, outstanding issues, asset condition summary, and timestamped photo evidence, or an explicit zero-activity certification), trigger asynchronous PDF and CSV exports, and download the finished files via 10-minute signed URLs.
- Context of use (who, where, device, connectivity): Operations Manager (`Chidi Okafor`) or Organisation Owner on an office desktop browser (`1024px` to `1440px+`) in Lagos at month-end preparing client billing proof-of-work packages.
- Business goal and success metric: Compile an accurate single-client HTML report preview in under 2 seconds and complete background Chromium PDF + CSV generation within 30 seconds (`concurrency = 1` queue worker), with 0 cross-client record leakage.
- Entry points:
  1. Top navigation `Client reports` (`/reports`).
  2. Clicking `Compile monthly report` from a Client Account row on `Registry/ClientAccountsIndex.tsx`.
- Exit points and next screens:
  1. Clicking `Download PDF` or `Download CSV` in `async-export-banner` or the `Recent exports` table downloads the signed file from Cloudflare R2 for manual sharing with the client.
  2. Clicking any inspection ID (`INSP-2026-089`) or work order ID (`WO-2026-0142`) inside the preview opens the corresponding immutable dossier.

## 2. Layout and hierarchy
- Primary action: `Export PDF & CSV` (`.cc-button--teal`, one primary output action on the compiled report).
- Secondary actions: `Compile report preview` (`.cc-button--secondary` in the report parameters bar), `Download PDF` / `Download CSV` in the ready banner and recent exports table.
- Reading order and focal point:
  1. `.cc-skip-link` and top `app-shell-header` scoped to `Lagoon View Estates Ltd`.
  2. Report parameters bar (`Client account` Tier-2 `dropdown-editorial`, `Period start` and `Period end` date inputs, `Property scope` Tier-1 `.cc-select`, and `Compile report preview` button).
  3. `async-export-banner` status strip (`Queued / Compiling` polling state -> `Ready` state with 10-minute signed download buttons -> or `Failed` / `429 Rate limit` state).
  4. Compiled Editorial Report Dossier (`page-header` with `42px` hero title, 4-card executive metric strip, single `.cc-callout` executive takeaway, Completed Inspections & Work Orders `data-table`, Asset Condition Summary `data-table`, and `evidence-gallery`).
  5. `Recent exports` `data-table` (showing prior PDF/CSV export jobs for the active client account).

| Breakpoint | Layout notes |
|---|---|
| 320 to 767 | `.cc-page` uses `32px 22px` padding. Parameters bar stacks into 1 vertical column (`16px` input font size, `40px` control height). Executive cards stack to 1 column; tables scroll horizontally inside `.cc-table-wrap` with sticky first column; evidence gallery collapses to 1 column. |
| 768 to 1023 | Parameters bar wraps into a 2-column grid; executive summary cards render 2x2; evidence gallery renders 2 columns. |
| 1024 and up | Centered `1120px` `.cc-page` with `56px 64px` padding over `#F2F4F6` backdrop, matching the exact visual geometry of the exported Chromium PDF report (`spatie/laravel-pdf`). |

## 3. Components
| Region | Component | Variant and size | Tokens or notes |
|---|---|---|---|
| Skip navigation | `.cc-skip-link` | First focusable element | `--cc-navy-2` bg, `z-index: 100` |
| App shell | `app-shell-header` | Desktop 64px bar | `{components.app-shell-header.*}` |
| Parameter controls | `dropdown-editorial` + `form-field` + `select-native` | `40px` height, `#6E7E91` border | Single Client Account scope enforced (`FR-17`) |
| Export queue status | `async-export-banner` | `Queued / Compiling`, `Ready`, `Failed` | `{components.async-export-banner.*}`, Inertia `usePoll(3000)` |
| Report cover header | `page-header` | Display Hero `42px` | `{typography.display-hero}`, `.cc-rule` (`#4F81BD`) |
| Executive KPIs | `editorial-card` | 4-column grid, 1 `--emphasis` | `{components.editorial-card.*}`, `tabular-nums` |
| Executive summary | `recommendation-callout` | Max 1 per report | `{components.recommendation-callout.*}` (`#EAF3F4`) |
| Operational tables | `data-table` | Inspections, Work Orders, Assets, Recent Exports | `{components.data-table.*}`, `tabular-nums` |
| Photo proof | `evidence-gallery` | 3-column grid (`4:3` aspect ratio) | `{components.evidence-gallery.*}`, 10-min signed R2 URLs |
| Evidence preview | `modal-dialog` | High-res preview + provenance | Focus-trapped modal with `Escape` close |

New components or tokens needed (with reason): None beyond canonical 21 components and `--cc-border-control: #6E7E91`.

## 4. State matrix
| State | Trigger | What the user sees | Copy IDs |
|---|---|---|---|
| Default (Active Report + Ready Export) | Compiled report for `Lagoon View Estates Ltd` (`01 Nov 2026` to `30 Nov 2026`) | Full editorial dossier preview + `async-export-banner` in `Ready` state with 10-min signed `Download PDF` and `Download CSV` buttons + `Recent exports` table | `uj4.header.title`, `uj4.banner.ready`, `uj4.callout.body` |
| Loading / Compiling Preview | Clicking `Compile report preview` | Parameters bar remains active; report canvas renders pulsing `#F4F7FA` skeleton cards and table rows matching exact report geometry (zero CLS) | `uj4.loading.preview` |
| Async Queued / Compiling (`usePoll(3000)`) | Clicking `Export PDF & CSV` | `async-export-banner` transitions to `.cc-status--info` (`Queued / Compiling` state, `role="status" aria-live="polite"`), polling every 3s while user continues reading the preview | `uj4.banner.compiling` |
| Empty report period (Zero activity) | Selected Client Account (`Victoria Crest Holdings`) had 0 inspections and 0 work orders in the date range | Never renders a blank page; renders a formal `.cc-callout` and certification table explicitly stating zero completed inspections and zero work orders while confirming active registered properties (2) and assets (14) | `uj4.zero.title`, `uj4.zero.callout` |
| Error recoverable (422 date range) | `Period end` date is earlier than `Period start` date | `Period end` input border turns `2px solid #A34545` (`aria-invalid="true"`) with inline `.cc-error`: `End date must be on or after the start date (01 Nov 2026).` | `uj4.err.422.dates` |
| Error export failed / Rate-limited (429) | Worker PDF timeout or `>5/min` export rate limit (`AD-22`) | `async-export-banner` renders `.cc-status--danger` (`role="alert"`) with retry button, or `.cc-status--warning` countdown (`Rate limit reached: 5 exports per minute. Please wait 24 seconds.`) | `uj4.err.export_failed`, `uj4.err.429` |
| Partial (Expired signed URL 403) | User leaves tab open `>10 minutes` and clicks an evidence thumbnail or download link | Automatic Inertia partial reload (`only: ['attachments', 'activeExport', 'recentExports']`) refreshes the 10-minute signed R2 URLs transparently with a polite status note | `uj4.partial.url_refreshed` |
| Offline | Browser loses connection while viewing compiled preview | Polite warning banner (`Offline: viewing compiled preview. Reconnect to trigger PDF or CSV export.`) | `uj4.offline.banner` |
| Permission denied | Authenticated as `Technician` or `Contractor` (no `/reports` access) | Route blocked by role policy (`403`), navigation item hidden in app shell | `uj4.perm.denied` |
| Extreme data | Client portfolio with 200+ work orders in a month | On-screen preview paginates tables at 20 rows per section with full count headers, while exported PDF/CSV includes all 200+ records in full | `uj4.table.caption` |

## 5. Interaction and behavior
- Click, tap and keyboard behavior per control:
  - Client account picker (`dropdown-editorial`) enforces single-client selection (`FR-17`; a report never mixes two Client Accounts).
  - Clicking `Export PDF & CSV` dispatches `ReportExportJob` (`ShouldBeUnique` per org + client + period, `AD-21`) and starts `usePoll(3000)`.
  - Clicking any evidence card in `evidence-gallery` opens a `modal-dialog` showing full provenance metadata (`Uploaded by`, `WAT timestamp`, `Owner entity`, `GPS coordinates`).
- Validation rules and timing: `start_date` and `end_date` validate on blur and submit; `client_account_id` is required and verified against `client_scope`.
- Focus management: Export status transitions announce politely via `role="status" aria-live="polite"` without stealing keyboard focus from the report preview.
- Motion: `150ms ease`; disabled under `prefers-reduced-motion: reduce`.
- Data saved and when: `ReportService::compile()` builds the read-only `ReportData` DTO; `Export PDF & CSV` writes an atomic `audit_events` row (`report.exported`, `FR-22`, `AD-18`).

## 6. Copy deck
| ID | Location | State | Text | Notes |
|---|---|---|---|---|
| `uj4.header.kicker` | Report dossier kicker | Default | `MONTHLY CLIENT OPERATIONS & ASSET INTELLIGENCE REPORT · NOVEMBER 2026` | Uppercase Georgia `#126E6A` |
| `uj4.header.title` | Report dossier H1 | Default | `Lagoon View Estates Ltd: Operational Proof-of-Work Dossier` | Arial 42px bold `#17365D` |
| `uj4.cta.compile` | Parameters bar | Default | `Compile report preview` | `.cc-button--secondary` |
| `uj4.cta.export` | Parameters bar | Default | `Export PDF & CSV` | `.cc-button--teal` (sole primary CTA) |
| `uj4.banner.compiling` | `async-export-banner` | Compiling (`usePoll`) | `Compiling PDF & CSV report package for Lagoon View Estates Ltd (01 Nov 2026 to 30 Nov 2026). Polling worker status every 3 seconds; you may continue reviewing this page.` | `role="status" aria-live="polite"` |
| `uj4.banner.ready` | `async-export-banner` | Ready | `Report package ready (generated 03 Nov 2026, 17:10 WAT). Private Cloudflare R2 download links expire in 10 minutes.` | `role="status" aria-live="polite"` |
| `uj4.zero.title` | Report dossier H2 | Zero-activity period | `Zero-activity period certification (Victoria Crest Holdings)` | Arial 24px bold `#4F81BD` |
| `uj4.zero.callout` | `.cc-callout` | Zero-activity period | `No scheduled inspections or work orders were recorded for Victoria Crest Holdings between 01 Nov 2026 and 30 Nov 2026. All 2 registered properties and 14 assets remain on the active register.` | Explicit non-empty certification (`FR-17`) |
| `uj4.err.429` | `async-export-banner` | Rate-limited (429) | `Export rate limit reached (5 exports per minute per organisation). Please wait 24 seconds before requesting another PDF export.` | `.cc-status--warning` (`AD-22`) |

## 7. Accessibility notes
- Heading outline and landmarks: `.cc-skip-link` -> `<header>` -> `<main id="main-content">` -> `<h1>` (`Lagoon View Estates Ltd: Operational Proof-of-Work Dossier`) -> `<h2>` (`Executive summary`, `Completed inspections & maintenance work orders`, `Asset condition & custody register`, `Timestamped field evidence gallery`, `Recent report exports`).
- Images and alt text: Every `evidence-gallery` thumbnail includes descriptive `alt` text naming the asset/work order and finding (e.g., `alt="Installed OEM alternator drive belt on 500kVA Generator LVE-GEN-01 for WO-2026-0142"`).
- Tabular numerals: All KPI values, SLA percentages, counts, and timestamps use `font-variant-numeric: tabular-nums`.

## 8. Performance and offline
- Synchronous HTML preview compiles via single-pass read queries (`ReportService::compile()`).
- Heavy Chromium PDF rendering runs asynchronously on the queue worker with `concurrency = 1` (`AD-15`) so web PHP-FPM workers are never blocked.

## 9. Analytics
| Event | Trigger | Properties |
|---|---|---|
| `report.preview_compiled` | `Compile report preview` click | `period_days`, `is_zero_activity` |
| `report.export_requested` | `Export PDF & CSV` click | `format` (`pdf_csv`), `audit_logged: true` |
| `report.export_downloaded` | Signed URL download | `format` (`pdf` \| `csv`) |

## 10. Acceptance criteria
1. Built from this screen spec; all states (`Compiled Report + Ready Banner`, `Async Compiling Poll`, `Zero-Activity Certification`, `Loading Skeleton`, `422 Date Error / 429 Rate Limit`, and `Evidence Provenance Modal`) are implemented and reachable.
2. Every compiled report and exported PDF/CSV is strictly scoped to a single Client Account (`FR-17`, `AD-5`).
3. When a reporting period has zero completed inspections and zero work orders, both the on-screen preview and exported PDF render the explicit Zero-Activity Certification callout and active register counts (`FR-17`).
4. Triggering an export dispatches a unique `TenantAware` background job (`AD-14`, `AD-21`), records an `audit_events` entry (`FR-22`, `AD-18`), polls via Inertia until ready, and serves downloads through 10-minute signed R2 URLs (`FR-21`, `AD-13`).

## 11. Open questions and assumptions
| Item | Type (question, assumption) | Owner | Status |
|---|---|---|---|
| `OQ-UX-2`: Statutory retention period displayed in report footer & audit log | Question (PRD §10 #7) | PM / Legal | Open (configurable retention days token) |

