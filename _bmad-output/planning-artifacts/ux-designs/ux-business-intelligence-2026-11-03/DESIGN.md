---
name: Cloud Crafters Editorial Intelligence
status: final
updated: 2026-11-03
description: Editorial business-report design system for Cloud Crafters Property & Asset Intelligence (React 19 + Inertia 3 + Tailwind CSS 4 + reskinned shadcn/ui), hardened for WCAG 2.2 AA per docs/design/design-context.md.
colors:
  # Canvas & surfaces
  white: '#FFFFFF'
  page: '#FFFFFF'
  surface: '#FFFFFF'
  shell-backdrop: '#F2F4F6'
  row-alt: '#F4F7FA'
  # Brand ink & blues
  navy: '#14243A'
  navy-heading: '#17365D'
  blue: '#4F81BD'
  blue-dark: '#365F91'
  teal: '#126E6A'
  teal-hover: '#0E5855'
  teal-legacy: '#168C88'
  teal-wash: '#EAF3F4'
  # Lines & borders
  line-strong: '#263244'
  border: '#C7D1DC'
  border-control: '#6E7E91'
  border-hover: '#526071'
  rule-blue: '#4F81BD'
  # Text & neutral tones
  muted: '#5F6F82'
  subtle: '#7B8794'
  placeholder: '#6E7E91'
  # Base semantic tokens (--cc-success, --cc-warning, --cc-danger, --cc-info, --cc-muted)
  success: '#126E6A'
  warning: '#94651D'
  danger: '#A34545'
  info: '#4F81BD'
  neutral: '#5F6F82'
  # Badge semantic tokens (.cc-status WCAG 2.2 AA pairs)
  status-success-fg: '#126E6A'
  status-success-bg: '#EAF3F1'
  status-success-border: '#C8E0DC'
  status-warning-fg: '#795014'
  status-warning-bg: '#F7F1E6'
  status-warning-border: '#E8D9BA'
  status-danger-fg: '#853838'
  status-danger-bg: '#F8EEEE'
  status-danger-border: '#EBCFCF'
  status-info-fg: '#365F91'
  status-info-bg: '#EEF3F9'
  status-info-border: '#D4E0EF'
  status-neutral-fg: '#5F6F82'
  status-neutral-bg: '#F1F3F5'
  status-neutral-border: '#DCE1E6'
typography:
  display-hero:
    fontFamily: 'Arial, Helvetica, sans-serif'
    fontSize: '42px'
    fontWeight: '700'
    lineHeight: '1.12'
    letterSpacing: '-0.025em'
  display-page:
    fontFamily: 'Arial, Helvetica, sans-serif'
    fontSize: '32px'
    fontWeight: '700'
    lineHeight: '1.15'
    letterSpacing: '-0.02em'
  heading-section:
    fontFamily: 'Arial, Helvetica, sans-serif'
    fontSize: '24px'
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: '0em'
  heading-subsection:
    fontFamily: 'Arial, Helvetica, sans-serif'
    fontSize: '20px'
    fontWeight: '700'
    lineHeight: '1.25'
    letterSpacing: '0em'
  subtitle:
    fontFamily: 'Arial, Helvetica, sans-serif'
    fontSize: '20px'
    fontWeight: '400'
    lineHeight: '1.35'
    letterSpacing: '0em'
  body-lg:
    fontFamily: "Georgia, 'Times New Roman', serif"
    fontSize: '18px'
    fontWeight: '400'
    lineHeight: '1.42'
    letterSpacing: '0em'
  body-base:
    fontFamily: "Georgia, 'Times New Roman', serif"
    fontSize: '16px'
    fontWeight: '400'
    lineHeight: '1.42'
    letterSpacing: '0em'
  body-sm:
    fontFamily: "Georgia, 'Times New Roman', serif"
    fontSize: '14px'
    fontWeight: '400'
    lineHeight: '1.35'
    letterSpacing: '0em'
  ui-label:
    fontFamily: 'Arial, Helvetica, sans-serif'
    fontSize: '14px'
    fontWeight: '700'
    lineHeight: '1.3'
    letterSpacing: '0em'
  ui-button:
    fontFamily: 'Arial, Helvetica, sans-serif'
    fontSize: '14px'
    fontWeight: '700'
    lineHeight: '1.0'
    letterSpacing: '0em'
  kicker:
    fontFamily: "Georgia, 'Times New Roman', serif"
    fontSize: '14px'
    fontWeight: '700'
    lineHeight: '1.25'
    letterSpacing: '0.045em'
  table-header:
    fontFamily: "Georgia, 'Times New Roman', serif"
    fontSize: '14px'
    fontWeight: '700'
    lineHeight: '1.25'
    letterSpacing: '0em'
  table-cell:
    fontFamily: "Georgia, 'Times New Roman', serif"
    fontSize: '14px'
    fontWeight: '400'
    lineHeight: '1.35'
    letterSpacing: '0em'
  status-badge:
    fontFamily: 'Arial, Helvetica, sans-serif'
    fontSize: '12px'
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: '0em'
  card-eyebrow:
    fontFamily: "Georgia, 'Times New Roman', serif"
    fontSize: '12px'
    fontWeight: '700'
    lineHeight: '1.3'
    letterSpacing: '0.05em'
  card-value:
    fontFamily: 'Arial, Helvetica, sans-serif'
    fontSize: '32px'
    fontWeight: '700'
    lineHeight: '1.1'
    letterSpacing: '-0.02em'
  hint:
    fontFamily: "Georgia, 'Times New Roman', serif"
    fontSize: '12px'
    fontWeight: '400'
    lineHeight: '1.4'
    letterSpacing: '0em'
rounded:
  none: '0px'
  sm: '2px'
  DEFAULT: '3px'
spacing:
  '1': '4px'
  '2': '8px'
  '3': '12px'
  '4': '16px'
  '5': '20px'
  '6': '24px'
  '8': '32px'
  '10': '40px'
  '12': '48px'
  '16': '64px'
  page-max: '1120px'
  page-pad-desktop: '56px 64px'
  page-pad-mobile: '32px 22px'
  control-height: '40px'
  control-height-sm: '32px'
  pagination-cell: '34px'
components:
  app-shell-header:
    background: '{colors.white}'
    foreground: '{colors.navy}'
    border-bottom: '{colors.line-strong}'
    accent-rule: '{colors.rule-blue}'
    height: '{spacing.16}'
  mobile-quick-task-bar:
    background: '{colors.white}'
    foreground: '{colors.navy-heading}'
    active-foreground: '{colors.blue-dark}'
    active-background: '{colors.status-info-bg}'
    border-top: '{colors.line-strong}'
    min-touch-target: '{spacing.control-height}'
  page-header:
    kicker-color: '{colors.teal}'
    title-color: '{colors.navy-heading}'
    subtitle-color: '{colors.blue-dark}'
    rule-color: '{colors.rule-blue}'
    meta-color: '{colors.muted}'
  recommendation-callout:
    background: '{colors.teal-wash}'
    label-color: '{colors.teal}'
    body-color: '{colors.navy}'
    radius: '{rounded.none}'
    padding-y: '{spacing.4}'
    padding-x: '{spacing.5}'
  data-table:
    header-background: '{colors.navy}'
    header-foreground: '{colors.white}'
    row-background: '{colors.white}'
    row-alt-background: '{colors.row-alt}'
    grid-border: '{colors.line-strong}'
    radius: '{rounded.none}'
  editorial-card:
    background: '{colors.surface}'
    border: '{colors.border}'
    emphasis-top-border: '{colors.blue}'
    eyebrow-color: '{colors.teal}'
    value-color: '{colors.navy-heading}'
    note-color: '{colors.muted}'
    radius: '{rounded.sm}'
    padding: '{spacing.5}'
  button:
    primary-bg: '{colors.navy-heading}'
    primary-fg: '{colors.white}'
    primary-hover-bg: '{colors.blue-dark}'
    secondary-bg: '{colors.white}'
    secondary-fg: '{colors.navy-heading}'
    secondary-border: '{colors.navy-heading}'
    secondary-hover-bg: '{colors.status-info-bg}'
    teal-bg: '{colors.teal}'
    teal-fg: '{colors.white}'
    teal-hover-bg: '{colors.teal-hover}'
    danger-bg: '{colors.status-danger-bg}'
    danger-fg: '{colors.status-danger-fg}'
    danger-border: '{colors.status-danger-border}'
    text-fg: '{colors.blue-dark}'
    radius: '{rounded.sm}'
    height: '{spacing.control-height}'
    height-sm: '{spacing.control-height-sm}'
  form-field:
    background: '{colors.white}'
    foreground: '{colors.navy}'
    label-color: '{colors.navy-heading}'
    hint-color: '{colors.muted}'
    placeholder-color: '{colors.placeholder}'
    border: '{colors.border-control}'
    border-hover: '{colors.border-hover}'
    border-error: '{colors.danger}'
    error-text: '{colors.danger}'
    radius: '{rounded.sm}'
    height: '{spacing.control-height}'
  select-native:
    background: '{colors.white}'
    foreground: '{colors.navy}'
    border: '{colors.border-control}'
    border-hover: '{colors.border-hover}'
    chevron-color: '{colors.muted}'
    disabled-background: '{colors.row-alt}'
    radius: '{rounded.sm}'
    height: '{spacing.control-height}'
  dropdown-editorial:
    trigger-background: '{colors.white}'
    trigger-border: '{colors.border-control}'
    trigger-focus-border: '{colors.blue}'
    menu-background: '{colors.surface}'
    menu-border: '{colors.border-control}'
    item-hover-background: '{colors.status-info-bg}'
    item-selected-background: '{colors.row-alt}'
    item-title-color: '{colors.navy}'
    item-sub-color: '{colors.muted}'
    radius: '{rounded.sm}'
  status-badge:
    radius: '{rounded.sm}'
    font-size: '{typography.status-badge.fontSize}'
    success-fg: '{colors.status-success-fg}'
    success-bg: '{colors.status-success-bg}'
    success-border: '{colors.status-success-border}'
    warning-fg: '{colors.status-warning-fg}'
    warning-bg: '{colors.status-warning-bg}'
    warning-border: '{colors.status-warning-border}'
    danger-fg: '{colors.status-danger-fg}'
    danger-bg: '{colors.status-danger-bg}'
    danger-border: '{colors.status-danger-border}'
    info-fg: '{colors.status-info-fg}'
    info-bg: '{colors.status-info-bg}'
    info-border: '{colors.status-info-border}'
    neutral-fg: '{colors.status-neutral-fg}'
    neutral-bg: '{colors.status-neutral-bg}'
    neutral-border: '{colors.status-neutral-border}'
  tabs:
    border-bottom: '{colors.border}'
    tab-inactive-fg: '{colors.muted}'
    tab-active-fg: '{colors.navy-heading}'
    tab-active-underline: '{colors.blue}'
  filter-bar:
    chip-background: '{colors.white}'
    chip-border: '{colors.border-control}'
    chip-foreground: '{colors.navy-heading}'
    chip-active-background: '{colors.status-info-bg}'
    chip-active-border: '{colors.blue}'
    radius: '{rounded.sm}'
  pagination:
    cell-size: '{spacing.pagination-cell}'
    border: '{colors.border-control}'
    foreground: '{colors.navy-heading}'
    active-background: '{colors.navy-heading}'
    active-foreground: '{colors.white}'
    radius: '{rounded.none}'
  property-map:
    container-border: '{colors.line-strong}'
    pin-fill: '{colors.navy-heading}'
    pin-stroke: '{colors.white}'
    pin-selected-fill: '{colors.teal}'
    overlay-background: '{colors.white}'
    overlay-border: '{colors.border}'
    radius: '{rounded.sm}'
  evidence-uploader:
    dropzone-background: '{colors.row-alt}'
    dropzone-border: '{colors.border-control}'
    dropzone-active-border: '{colors.blue}'
    progress-bar-fill: '{colors.teal}'
    error-border: '{colors.danger}'
    radius: '{rounded.sm}'
  evidence-gallery:
    card-background: '{colors.white}'
    card-border: '{colors.border}'
    caption-color: '{colors.navy}'
    meta-color: '{colors.muted}'
    radius: '{rounded.sm}'
  checklist-runner:
    section-header-bg: '{colors.navy}'
    section-header-fg: '{colors.white}'
    row-background: '{colors.white}'
    row-alt-background: '{colors.row-alt}'
    row-border: '{colors.border}'
    pass-active-bg: '{colors.status-success-bg}'
    pass-active-fg: '{colors.status-success-fg}'
    fail-active-bg: '{colors.status-danger-bg}'
    fail-active-fg: '{colors.status-danger-fg}'
    na-active-bg: '{colors.status-neutral-bg}'
    na-active-fg: '{colors.status-neutral-fg}'
    radius: '{rounded.sm}'
  timeline-feed:
    spine-line: '{colors.border}'
    node-dot: '{colors.blue}'
    node-completed-dot: '{colors.teal}'
    card-background: '{colors.white}'
    card-border: '{colors.border}'
    timestamp-color: '{colors.muted}'
    radius: '{rounded.sm}'
  async-export-banner:
    compiling-background: '{colors.status-info-bg}'
    compiling-border: '{colors.status-info-border}'
    compiling-foreground: '{colors.status-info-fg}'
    ready-background: '{colors.teal-wash}'
    ready-border: '{colors.status-success-border}'
    ready-foreground: '{colors.navy}'
    failed-background: '{colors.status-danger-bg}'
    failed-border: '{colors.status-danger-border}'
    failed-foreground: '{colors.status-danger-fg}'
    radius: '{rounded.none}'
  modal-dialog:
    backdrop: 'rgba(20, 36, 58, 0.45)'
    surface: '{colors.white}'
    border: '{colors.line-strong}'
    header-rule: '{colors.rule-blue}'
    title-color: '{colors.navy-heading}'
    radius: '{rounded.DEFAULT}'
---

# Cloud Crafters Editorial Intelligence: Design Spine

## Brand & Style

**Cloud Crafters Property & Asset Intelligence** uses an **editorial business-report aesthetic**: authoritative, calm, highly legible, and evidence-led. Rather than looking like a SaaS dashboard cluttered with saturated KPI tiles, rounded pill badges, or floating shadows, every screen reads like a well-typeset management consulting report or property strategy dossier translated into a working software product.

For Nigerian facilities-management (FM) providers, whether an Operations Manager reviewing a client portfolio in Lagos or a Site Supervisor walking a generator room on a mobile browser, the product's core commercial promise is **operational control and undeniable proof of work**. The visual language reinforces that promise: clean white paper surfaces (`{colors.white}`), deep authoritative navy ink (`{colors.navy}` and `{colors.navy-heading}`), crisp 1px architectural rules (`{colors.line-strong}` and `{colors.rule-blue}`), formal serif reading and tabular copy (`Georgia`), and functional sans-serif controls (`Arial`).

The system is **light-mode only** (`{colors.page}` on a subtle `{colors.shell-backdrop}` outer viewport) and governed by `docs/design/design-context.md` and the `enterprise-ui-ux` standard. We build on React 19, Inertia 3, Tailwind CSS 4, and `shadcn/ui` primitives that are strictly reskinned to the Cloud Crafters Editorial Intelligence token set. Where `shadcn/ui` ships rounded-md corners, muted zinc borders, or drop-shadow cards by default, this design system overrides them with square-edged tables (`{rounded.none}`), tight 2px to 3px control radii (`{rounded.sm}` / `{rounded.DEFAULT}`), explicit 1px borders (`{colors.border}` for quiet containers and `{colors.border-control}` for `>=3:1` WCAG 2.2 AA interactive controls), and zero default card elevation.

Reference visual specimen, canonical stylesheet, screen specifications, and promoted interactive key-screen mockups:
- **Project Design Context**: `docs/design/design-context.md` (authoritative product, audience, token, and accessibility contract).
- **Imported Design System Specimen & Stylesheet**: [design-system-preview.html](imports/design-system-preview.html) (canonical typography, colour palette, table, card, button, and Tier-1/Tier-2 select specimens) and [cloud-crafters-design-system.css](imports/cloud-crafters-design-system.css) (canonical CSS variables and `.cc-*` utility rules).
- **Screen Specifications (`enterprise-ui-ux` SPEC mode)**:
  - [screen-spec-uj1-property-asset-register.md](specs/screen-spec-uj1-property-asset-register.md)
  - [screen-spec-uj2-mobile-inspection-runner.md](specs/screen-spec-uj2-mobile-inspection-runner.md)
  - [screen-spec-uj3-mobile-work-order-execution.md](specs/screen-spec-uj3-mobile-work-order-execution.md)
  - [screen-spec-uj4-client-report-compiler.md](specs/screen-spec-uj4-client-report-compiler.md)
- **Promoted Interactive Key-Screen Mockups**:
  - [property-asset-register.html](mockups/property-asset-register.html): Desktop Property Dossier & Scoped Asset Register (`UJ-1`, `1120px` `.cc-page` canvas, `app-shell-header`, `<PropertyMap>`, NIPOST `Verified` disclaimer, append-only Location History, and interactive UX State Switcher).
  - [mobile-inspection-runner.html](mockups/mobile-inspection-runner.html): Mobile Field Inspection Runner (`UJ-2`, `<=390px` view comparing active `checklist-runner` + inline Maintenance Request drawer vs. `Immutable / Locked Record` with `Create corrected version`, plus interactive UX State Switcher).
  - [mobile-work-order-execution.html](mockups/mobile-work-order-execution.html): Mobile Work Order Execution (`UJ-3`, `<=390px` view comparing multi-visit `In Progress / On Hold` logging + non-blocking `GPS Unavailable` notice vs. mandatory completion photo gate, plus interactive UX State Switcher).
  - [client-report-compiler.html](mockups/client-report-compiler.html): Desktop Monthly Client Report Compiler & Preview (`UJ-4`, `1120px` `.cc-page` canvas illustrating `async-export-banner` polling & ready states, `evidence-gallery`, Recent Exports table, Zero-Activity Period certification, and interactive UX State Switcher).
- *Contract Precedence*: Both UX spines (`DESIGN.md` and `EXPERIENCE.md`) win on conflict with any mock, wireframe, or imported file.

## Colors

Every colour in the palette carries an explicit editorial or operational meaning. Colour is never applied decoratively to fill entire cards or create visual noise.

### Canvas & Surfaces
- **White (`{colors.white}` / `{colors.page}` / `{colors.surface}`)**: The default canvas for all `.cc-page` sheets, cards, form inputs, and odd table rows. White space is the primary structural separator.
- **Shell Backdrop (`{colors.shell-backdrop}`)**: The quiet outer viewport background behind the centered 1120px `.cc-page` sheet on wide desktop screens, giving the workspace the tactile framing of a printed report page.
- **Alternating Row (`{colors.row-alt}`)**: Used for even table rows (`tbody tr:nth-child(even)`), skeleton loading placeholders, disabled select backgrounds, and subtle dropzone wells.

### Brand Ink & Blues
- **Ink Navy (`{colors.navy}`)**: Primary body copy, table cell text, and solid table header (`<th>`) fills. Contrast against `{colors.white}` is **15.8:1** (exceeds WCAG AAA).
- **Heading Navy (`{colors.navy-heading}`)**: Display titles (`h1`), subsection headings (`h3`), form labels, card values, and primary button fills. Contrast against `{colors.white}` is **12.1:1** (exceeds WCAG AAA).
- **Editorial Blue (`{colors.blue}` / `{colors.rule-blue}`)**: Section headings (`h2`), active tab underlines, card top-edge emphasis bars, and signature 1px horizontal divider rules (`.cc-rule`). Contrast against `{colors.white}` is **4.02:1** (used exclusively for large bold headings `>=18pt` / `24px bold` and structural rules).
- **Interactive Blue Dark (`{colors.blue-dark}`)**: Italic page subtitles (`.cc-subtitle`), inline link text, text-button labels, primary button hover state, `:focus-visible` outlines, and `.cc-status--info` foreground text. Contrast against `{colors.white}` is **6.68:1** (WCAG 2.2 AA compliant for all regular and small text sizes).
- **Accent Teal (`{colors.teal}` / `{colors.teal-hover}`)**: Reserved strictly for uppercase editorial kickers (`.cc-kicker`), recommendation labels (`.cc-callout__label`), card eyebrows (`.cc-card__eyebrow`), and positive confirmation buttons (`.cc-button--teal`, e.g., completing an inspection or work order). Legacy print-specimen teal (`{colors.teal-legacy}` `#168C88`, `4.08:1` on white) is darkened to `{colors.teal}` (`#126E6A`, **5.86:1** on `{colors.white}` and **5.19:1** on `{colors.teal-wash}`) and `{colors.teal-hover}` (`#0E5855`, **7.61:1** on `{colors.white}`) on all UI text and buttons to meet WCAG 2.2 AA `4.5:1` contrast.
- **Pale Teal Wash (`{colors.teal-wash}`)**: Background fill exclusively for the `.cc-callout` recommendation/decision box and the "Ready" state of the async report export banner. At most one pale teal callout may appear per surface.

### Lines, Borders & Neutral Text
- **Strong Line (`{colors.line-strong}`)**: 1px structural gridlines for `.cc-table-wrap`, `.cc-table th`, `.cc-table td`, modal borders, and shell header dividers (`12.6:1` on white).
- **Quiet Structural Border (`{colors.border}`)**: Default 1px border (`#C7D1DC`) for non-interactive `.cc-card` containers, tab bar bottom lines, and section dividers.
- **Interactive Control Border (`{colors.border-control}` / `{colors.border-hover}`)**: High-contrast 1px border (`#6E7E91`, **3.95:1** on `{colors.white}`) applied to all interactive form controls (`.cc-input`, `.cc-select`, `.cc-textarea`, `.cc-dropdown__trigger`, `.cc-filter` chips, `.cc-page-link` cells, and `.cc-uploader` dropzones) to satisfy WCAG 2.2 AA SC 1.4.11 (`>=3:1` non-text contrast); darkens to `{colors.border-hover}` (`#526071`, **6.32:1**) on pointer hover.
- **Muted Text (`{colors.muted}`)**: Secondary descriptions, table captions, helper hints, timestamps, and inactive tab labels. Contrast against `{colors.white}` is **5.18:1** (WCAG AA compliant).
- **Subtle Metadata (`{colors.subtle}`) & Input Placeholder (`{colors.placeholder}`)**: Low-emphasis metadata and input placeholder example prompts (`#6E7E91`, never used as the sole field label).

### Semantic & Status Badge Tokens (Reconciled CSS & WCAG 2.2 AA Pairs)
To maintain 100% parity with `cloud-crafters-design-system.css`, the token system distinguishes between **base semantic tokens** (used for input validation borders, inline error messages, and active indicators) and **status badge triples** (`.cc-status--*` foreground, background, and border tokens engineered for high-contrast legibility on tinted chips):

| Semantic Role | Base Token | Badge Foreground | Badge Background | Badge Border | Contrast Ratio (FG on BG) | Operational Meaning |
|---|---|---|---|---|---|---|
| **Success / Confirmed** | `{colors.success}` | `{colors.status-success-fg}` | `{colors.status-success-bg}` | `{colors.status-success-border}` | **5.42:1** (AA) | Completed work order, approved inspection, active client account, or NIPOST-verified postcode. Never implies legal property title. |
| **Warning / Attention** | `{colors.warning}` | `{colors.status-warning-fg}` | `{colors.status-warning-bg}` | `{colors.status-warning-border}` | **6.15:1** (AA) | Due-soon inspection, work order `on_hold`, pending supervisor review, or unverified/offline GPS warning. |
| **Danger / Overdue** | `{colors.danger}` | `{colors.status-danger-fg}` | `{colors.status-danger-bg}` | `{colors.status-danger-border}` | **6.31:1** (AA) | Overdue work order, failed checklist item (`Fail`), form validation error, or destructive action. |
| **Info / In Progress** | `{colors.info}` | `{colors.status-info-fg}` | `{colors.status-info-bg}` | `{colors.status-info-border}` | **5.89:1** (AA) | Work order `assigned` or `in_progress`, active filter chip, or async report job `queued` / `compiling`. |
| **Neutral / Draft** | `{colors.neutral}` | `{colors.status-neutral-fg}` | `{colors.status-neutral-bg}` | `{colors.status-neutral-border}` | **4.66:1** (AA) | Draft inspection, `N/A` checklist response, archived template version, or `cancelled` work order. |

## Typography

Typography is the primary engine of hierarchy in the Editorial Intelligence system. We use a zero-latency, universal system font stack that requires zero external webfont downloads on high-latency Nigerian mobile networks while delivering crisp report authority:

1. **Display, Headings & UI Controls (`Arial, Helvetica, sans-serif`)**: Provides clean, high-density structural clarity for page titles, section headings, form labels, buttons, tabs, and status badges.
2. **Editorial Body, Kickers & Tabular Data (`Georgia, 'Times New Roman', serif`)**: Provides an authoritative, publication-grade voice for narrative descriptions, inspection notes, recommendation callouts, uppercase kickers, and all data table headers and cells.

### Type Scale & Role Rules
- **Display Hero (`{typography.display-hero.fontSize}` / `clamp(2.1rem, 4vw, 2.625rem)`)**: Used on Client Report covers and primary portfolio overview headers. Set in `{colors.navy-heading}`, weight `{typography.display-hero.fontWeight}`, line-height `{typography.display-hero.lineHeight}`, tracking `{typography.display-hero.letterSpacing}`.
- **Page Heading (`{typography.display-page.fontSize}`)**: Standard top-level page title (`h1`) on operational surfaces. Set in `{colors.navy-heading}` using sentence case.
- **Section Heading (`{typography.heading-section.fontSize}`)**: Major section divider (`h2`), set in `{colors.blue}`, weight `{typography.heading-section.fontWeight}` using sentence case. May be preceded by an uppercase `{typography.kicker.fontSize}` Georgia kicker in `{colors.teal}`.
- **Subsection / Card Heading (`{typography.heading-subsection.fontSize}`)**: Set in `{colors.navy-heading}`, weight `{typography.heading-subsection.fontWeight}` using sentence case.
- **Subtitle (`{typography.subtitle.fontSize}`)**: Italic Arial deck below the `h1` and blue horizontal rule, set in `{colors.blue-dark}` (`#365F91`, **6.68:1** on `{colors.white}` to satisfy WCAG 2.2 AA for regular-weight text `<24px`). Scales to `18px` (`--cc-text-lg`) on viewports `<=760px`.
- **Editorial Body (`{typography.body-lg.fontSize}` desktop / `{typography.body-base.fontSize}` mobile)**: Long-form Georgia prose, callout copy, and inspection summaries. Constrained to a comfortable reading measure of **65 to 75 characters (`max-width: 75ch`)**, and never smaller than `16px` on mobile web.
- **Tabular Copy (`{typography.table-header.fontSize}` & `{typography.table-cell.fontSize}`)**: `14px` Georgia (`16px` allowed in low-density two-column summary tables) with `font-variant-numeric: tabular-nums`. First descriptor column (`td:first-child`) is always bold (`700`); numeric columns are right-aligned with explicit units (`m`, `kVA`, `₦`, `%`).
- **UI Controls & Labels (`{typography.ui-label.fontSize}` & `{typography.ui-button.fontSize}`)**: `14px` Arial (`700` for labels and buttons, `400` for desktop input text), set in sentence case. On mobile viewports (`@media (max-width: 760px)`), `.cc-input`, `.cc-select`, `.cc-textarea`, and `.cc-dropdown__trigger` expand from `14px` to `16px` (`1rem`, `{typography.body-base.fontSize}`) so iOS Safari never auto-zooms the viewport during field checklist or work-order entry (`NFR-5.2`). Core workflow text never drops below `14px`.
- **Compact Metadata & Badges (`{typography.status-badge.fontSize}` & `{typography.hint.fontSize}`)**: `12px` is the absolute minimum font size across the entire system (including mobile quick-task cells and mobile context header eyebrows), reserved strictly for `.cc-status` badges, `.cc-card__eyebrow` labels, `.cc-hint` field helper copy, and compact button variants. Never use all-caps on paragraphs or standard form labels.

## Layout & Spacing

All spacing, padding, and structural rhythm follow a strict **4px base grid**: `{spacing.1}` (`4px`), `{spacing.2}` (`8px`), `{spacing.3}` (`12px`), `{spacing.4}` (`16px`), `{spacing.5}` (`20px`), `{spacing.6}` (`24px`), `{spacing.8}` (`32px`), `{spacing.10}` (`40px`), `{spacing.12}` (`48px`), and `{spacing.16}` (`64px`).

### Editorial Canvas, Hierarchy & App Shell Geometry
- **One Primary Action Per View**: Every surface presents at most **one primary button** (`.cc-button--primary` or `.cc-button--teal` completion button) in the primary action zone. Secondary actions use `.cc-button--secondary`, and tertiary actions use `.cc-button--text`.
- **Skip Link & Scroll Padding (WCAG 2.2 SC 2.4.1 & SC 2.4.11)**: Every page begins with a `<a href="#main-content" class="cc-skip-link">Skip to main content</a>` link as the first focusable element, and `html` sets `scroll-padding-top: 80px; scroll-padding-bottom: 72px;` so sticky headers and mobile quick-task bars never obscure focused elements.
- **Desktop Viewport (`>760px`)**:
  - **Top Editorial Header Bar (`app-shell-header`)**: Full-width white bar (`{spacing.16}` / `64px` height) pinned at the top of the viewport with a `1px solid` `{colors.line-strong}` bottom border and a `2px solid` `{colors.rule-blue}` top accent rule. Houses the Organisation name, the Tier-2 **Client account scope switcher** (`.cc-dropdown`), horizontal text-link module navigation (`Properties`, `Assets`, `Inspections`, `Work orders`, `Reports`, `Organisation & audit`), and the user role badge. No persistent left sidebar is used, preserving 100% of horizontal width for wide data tables and split map/register views.
  - **Page Sheet (`.cc-page`)**: Centered `<main id="main-content" class="cc-page">` container with `max-width` `{spacing.page-max}` (`1120px`), `{colors.white}` background, and padding `{spacing.page-pad-desktop}` (`56px 64px`) resting on the `{colors.shell-backdrop}` canvas.
  - **Section Rhythm (`.cc-section`)**: Major content sections are separated by `{spacing.10}` (`40px`, range `32px` to `48px`) top margin with an internal vertical grid gap of `{spacing.4}` (`16px`). Space between groups is always at least `2x` the space within groups.
  - **Stacks & Rows**: Vertical `.cc-stack` groups use `{spacing.3}` (`12px`) gap; horizontal `.cc-row` control groups use `{spacing.3}` (`12px`, range `8px` to `16px`) with `flex-wrap: wrap`.
- **Mobile & Field Browser Viewport (`<=760px`)**:
  - **Page Sheet Padding**: `.cc-page` padding compresses to `{spacing.page-pad-mobile}` (`32px 22px`) to frame content cleanly like a notebook page without bleeding to screen edges. Reflow is verified down to `320px` CSS width without horizontal page overflow.
  - **Compact Header + Slide-Over Sheet**: The top bar condenses to display the active Client Account badge (`>=12px` eyebrow) and a `40px x 40px` Menu trigger that opens a full-height slide-over navigation sheet (`modal-dialog` contract).
  - **Persistent Bottom Quick-Task Bar (`mobile-quick-task-bar`)**: For field roles (`Site supervisor`, `Technician`, `Contractor`), a `56px`-tall bottom bar with `1px solid` `{colors.line-strong}` top border provides one-thumb access to `My inspections`, `My work orders`, `Asset lookup`, and `Quick evidence`, with every touch target enforcing a minimum height of `{spacing.control-height}` (`40px`) and `12px` (`{typography.status-badge.fontSize}`) bold Arial labels.
  - **Grid Collapse & Scroll Affordance**: Multi-column card grids (`repeat(auto-fit, minmax(220px, 1fr))`) and 2-column form grids stack into single vertical columns. Data tables never collapse into unreadable fragments; they remain inside `.cc-table-wrap` (`overflow-x: auto`, `min-width: 560px`) with horizontal scroll-shadow gradients and a sticky first identifier column (`th:first-child, td:first-child`).

## Elevation & Depth

Hierarchy in the Editorial Intelligence system is achieved through **typographic scale, crisp 1px borders, and surface tinting**, never through floating drop shadows. Shadow, focus-ring, and reduced-motion CSS variables map directly to `--cc-shadow`, `--cc-shadow-popover`, and `--cc-focus` in [cloud-crafters-design-system.css](imports/cloud-crafters-design-system.css).

- **Level 0 (Base Canvas & Cards, `box-shadow: none`)**: `.cc-page`, `.cc-card`, `.cc-callout`, `.cc-table-wrap`, `.cc-input`, and `.cc-button` carry **zero shadow** in their resting, hover, and active states.
- **Level 1 (Ambient Utility Shadow, `--cc-shadow: 0 2px 8px rgba(20, 36, 58, 0.08)`)**: Permitted only on floating map attribution overlays or sticky mobile quick-task bars when scrolling over dense content.
- **Level 2 (Popover & Modal Overlay, `--cc-shadow-popover: 0 8px 24px rgba(20, 36, 58, 0.14)`)**: Reserved strictly for transient layers that must separate from underlying document text: `.cc-dropdown__menu` popovers, date-range pickers, slide-over mobile navigation sheets, and `.cc-modal` dialogs (which pair with a `{components.modal-dialog.backdrop}` scrim).
- **Universal Focus Ring (`--cc-focus: 0 0 0 3px rgba(79, 129, 189, 0.22)` + `2px solid {colors.blue-dark}`)**: Mandatory on `:focus-visible` across every interactive element to satisfy WCAG 2.2 SC 1.4.11 (`3:1` non-text contrast), SC 2.4.7, and SC 2.4.11:
  ` .cc-theme :where(a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])):focus-visible { outline: 2px solid var(--cc-blue-dark); outline-offset: 1px; box-shadow: var(--cc-focus); } `
- **Reduced Motion (`@media (prefers-reduced-motion: reduce)`)**: Disables transition durations (`0.01ms`) and replaces skeleton pulse animations with static high-contrast placeholder bars.

## Shapes

The shape language is **architectural and rectangular**, echoing ruled financial tables and printed engineering schedules:

- **Square (`{rounded.none}` / `0px`)**: Mandatory for `.cc-callout` recommendation boxes, `.cc-table-wrap` and `.cc-table` cells, `.cc-page-link` pagination cells, and `.cc-export-banner` status strips.
- **Sharp Micro-Radius (`{rounded.sm}` / `2px`)**: Standard corner radius for `.cc-card`, `.cc-button`, `.cc-input`, `.cc-select`, `.cc-textarea`, `.cc-dropdown__trigger`, `.cc-dropdown__menu`, `.cc-status` badges, and `.cc-filter` chips.
- **Modal & Container Radius (`{rounded.DEFAULT}` / `3px`)**: Maximum radius used anywhere in the system, applied to modal dialogs and map containers.
- **Forbidden Shapes**: Pill shapes (`border-radius: 9999px` or `rounded-full`) and large rounded cards (`>=6px`) are strictly banned across buttons, status badges, filter chips, and inputs.

## Components

All 21 canonical components are implemented by reskinning `shadcn/ui` primitives or semantic HTML with the `.cc-*` class specifications and frontmatter tokens below. Behavioral rules for each of these exact 21 components are defined 1:1 in `EXPERIENCE.md`, and base CSS rules live in [cloud-crafters-design-system.css](imports/cloud-crafters-design-system.css).

### 1. `app-shell-header`
- **Anatomy**: Full-width header bar (`height: {components.app-shell-header.height}`, background `{components.app-shell-header.background}`) preceded by `.cc-skip-link`, with a `2px solid {components.app-shell-header.accent-rule}` top stripe and `1px solid {components.app-shell-header.border-bottom}` bottom border.
- **Zones**: Left: Organisation wordmark (`{typography.ui-label.fontSize}`, `{colors.navy-heading}`) + separator + **Client account scope switcher** (`dropdown-editorial`). Center: horizontal nav links (`{typography.ui-label.fontSize}`, `{colors.muted}`, active state `{colors.navy-heading}` with `2px solid {colors.blue}` bottom bar). Right: active role badge (`status-badge` neutral) and user menu trigger.
- **Illustrated in**: [property-asset-register.html](mockups/property-asset-register.html) and [client-report-compiler.html](mockups/client-report-compiler.html).

### 2. `mobile-quick-task-bar`
- **Anatomy**: Fixed bottom bar on viewports `<=760px` (`background: {components.mobile-quick-task-bar.background}`, `border-top: 1px solid {components.mobile-quick-task-bar.border-top}`).
- **Sizing & States**: 4 equal-width action cells, each `min-height: {components.mobile-quick-task-bar.min-touch-target}` (`40px`, container height `56px`), set in `{typography.status-badge.fontSize}` (`12px`) bold Arial (`{components.mobile-quick-task-bar.foreground}`). Active route cell uses `{components.mobile-quick-task-bar.active-background}` fill and `{components.mobile-quick-task-bar.active-foreground}` text with a `2px solid {colors.blue}` top edge.
- **Illustrated in**: [mobile-inspection-runner.html](mockups/mobile-inspection-runner.html) and [mobile-work-order-execution.html](mockups/mobile-work-order-execution.html).

### 3. `page-header`
- **Anatomy**: Vertical stack (`.cc-stack`, gap `{spacing.3}`) at the top of `.cc-page`.
- **Elements**: (1) Uppercase Georgia kicker (`.cc-kicker`, `{components.page-header.kicker-color}`, `{typography.kicker.fontSize}`), (2) Display `h1` (`{components.page-header.title-color}`, `{typography.display-hero.fontSize}` or `{typography.display-page.fontSize}` in sentence case), (3) `1px` horizontal rule (`.cc-rule`, `{components.page-header.rule-color}`), (4) Optional italic Arial subtitle (`.cc-subtitle`, `{components.page-header.subtitle-color}`, `{typography.subtitle.fontSize}`), and (5) Metadata row (`.cc-row .cc-spread`) with `.cc-meta` (`{components.page-header.meta-color}`, `{typography.body-sm.fontSize}`) on the left and at most one primary page action plus secondary actions on the right.

### 4. `recommendation-callout`
- **Anatomy**: `.cc-callout` container with `{components.recommendation-callout.background}` fill, `{components.recommendation-callout.padding-y}` `{components.recommendation-callout.padding-x}` padding, `border: 0`, and `border-radius: {components.recommendation-callout.radius}` (`0px`).
- **Typography**: Uppercase Georgia label (`.cc-callout__label`, `{components.recommendation-callout.label-color}`, `{typography.kicker.fontSize}`) above Georgia body copy (`.cc-callout__body`, `{components.recommendation-callout.body-color}`, `{typography.body-lg.fontSize}`, `max-width: 75ch`).

### 5. `data-table`
- **Anatomy**: Outer `.cc-table-wrap` (`overflow-x: auto`, `border: 1px solid {components.data-table.grid-border}`, `border-radius: {components.data-table.radius}`). Inner `.cc-table` (`width: 100%`, `min-width: 560px`, `border-collapse: collapse`, `font-variant-numeric: tabular-nums`).
- **Cells, Rows & Mobile Scroll Affordance**: `<caption>` top-aligned in `{colors.muted}` (`{typography.body-sm.fontSize}`). `<th>` uses solid `{components.data-table.header-background}` fill, `{components.data-table.header-foreground}` bold Georgia text (`{typography.table-header.fontSize}`), and `1px solid {components.data-table.grid-border}`. `<td>` uses `1px solid {components.data-table.grid-border}`, `{typography.table-cell.fontSize}` Georgia text in `{colors.navy}`, alternating `{components.data-table.row-background}` (odd) and `{components.data-table.row-alt-background}` (even). First column (`td:first-child`) is bold (`700`). At `@media (max-width: 760px)`, `.cc-table-wrap` applies horizontal scroll-shadow gradients and pins `th:first-child, td:first-child` (`position: sticky; left: 0; z-index: 2`) so row identifiers remain visible while scrolling horizontally.

### 6. `editorial-card`
- **Anatomy**: `.cc-card` with `{components.editorial-card.background}` fill, `1px solid {components.editorial-card.border}`, `border-radius: {components.editorial-card.radius}`, `padding: {components.editorial-card.padding}` (`{spacing.4}` on mobile), and `box-shadow: none`.
- **Variants & Content**: `.cc-card--emphasis` adds `border-top: 3px solid {components.editorial-card.emphasis-top-border}` (used on at most one primary summary card per row). Contains `.cc-card__eyebrow` (`{components.editorial-card.eyebrow-color}`, `{typography.card-eyebrow.fontSize}` uppercase Georgia), `.cc-card__value` (`{components.editorial-card.value-color}`, `{typography.card-value.fontSize}` bold Arial with `tabular-nums`), and `.cc-card__note` (`{components.editorial-card.note-color}`, `{typography.body-sm.fontSize}` Georgia).

### 7. `button`
- **Anatomy**: `.cc-button` inline-flex rectangular control (`min-height: {components.button.height}`, compact `.cc-button--small` `min-height: {components.button.height-sm}` on desktop and `min-height: 36px` with `{spacing.2}` row gap at `@media (max-width: 760px)`, `gap: {spacing.2}`, `border-radius: {components.button.radius}`, font `{typography.ui-button.fontSize}` bold Arial in sentence case).
- **Variants**:
  - **Primary (`.cc-button--primary`)**: `{components.button.primary-bg}` fill and border, `{components.button.primary-fg}` text; hover `{components.button.primary-hover-bg}`.
  - **Secondary (`.cc-button--secondary`)**: `{components.button.secondary-bg}` fill, `1px solid {components.button.secondary-border}`, `{components.button.secondary-fg}` text; hover `{components.button.secondary-hover-bg}`.
  - **Teal Confirm (`.cc-button--teal`)**: `{components.button.teal-bg}` fill and border, `{components.button.teal-fg}` text; hover `{components.button.teal-hover-bg}`. Reserved for positive completion/verification actions.
  - **Danger / Destructive (`.cc-button--danger`)**: `{components.button.danger-bg}` fill, `1px solid {components.button.danger-border}`, `{components.button.danger-fg}` text.
  - **Text Link (`.cc-button--text`)**: Transparent background, `{components.button.text-fg}` underlined text; hover `{colors.navy-heading}` on `{colors.status-info-bg}`.
- **States**: Disabled uses `opacity: 0.46; cursor: not-allowed` with an adjacent explanation of why the action is unavailable. Focus uses the universal `:focus-visible` ring.

### 8. `form-field`
- **Anatomy**: `.cc-field` vertical stack. Top `<label class="cc-label" for="...">` (`{components.form-field.label-color}`, `{typography.ui-label.fontSize}` sentence case) explicitly linked via `for`/`id` to `.cc-input` or `.cc-textarea` (`min-height: {components.form-field.height}` / `100px` for textarea, `background: {components.form-field.background}`, `color: {components.form-field.foreground}`, `border: 1px solid {components.form-field.border}` (`#6E7E91`, `3.95:1` contrast), `border-radius: {components.form-field.radius}`, font `{typography.body-sm.fontSize}` Arial on desktop expanding to `{typography.body-base.fontSize}` / `16px` at `@media (max-width: 760px)`). Bottom `.cc-hint` (`{components.form-field.hint-color}`, `{typography.hint.fontSize}` Georgia) or `.cc-error` (`{components.form-field.error-text}`, `{typography.hint.fontSize}` bold Arial).
- **States**: Hover border `{components.form-field.border-hover}`; `:focus-visible` applies universal focus outline + `--cc-focus`; `aria-invalid="true"` applies `border: 2px solid {components.form-field.border-error}`.

### 9. `select-native`
- **Anatomy**: `.cc-select` Tier-1 native `<select>` control (`appearance: none`, `min-height: {components.select-native.height}`, `border: 1px solid {components.select-native.border}` (`#6E7E91`, `3.95:1`), `border-radius: {components.select-native.radius}`, embedded SVG chevron in `{components.select-native.chevron-color}`, font `{typography.body-sm.fontSize}` on desktop expanding to `{typography.body-base.fontSize}` / `16px` at `@media (max-width: 760px)`). Disabled state uses `{components.select-native.disabled-background}` and `opacity: 0.46`.

### 10. `dropdown-editorial`
- **Anatomy**: `.cc-dropdown` Tier-2 rich custom combobox/listbox. Trigger renders as `<button type="button" class="cc-dropdown__trigger" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-controls="..." aria-labelledby="...">` (`min-height: {spacing.control-height}`, `border: 1px solid {components.dropdown-editorial.trigger-border}` (`#6E7E91`, `3.95:1`), `border-radius: {components.dropdown-editorial.radius}`). Open/focus state sets `border-color: {components.dropdown-editorial.trigger-focus-border}` + universal `:focus-visible` ring and rotates `.cc-dropdown__arrow` `180deg` in `{colors.navy-heading}`.
- **Popover Menu**: `.cc-dropdown__menu` (`role="listbox"`, `background: {components.dropdown-editorial.menu-background}`, `border: 1px solid {components.dropdown-editorial.menu-border}`, `border-radius: {components.dropdown-editorial.radius}`, `box-shadow: var(--cc-shadow-popover)`). Items (`.cc-dropdown__item`, `role="option"`) support `.cc-dropdown__item-title` (`{components.dropdown-editorial.item-title-color}`), `.cc-dropdown__item-sub` (`{components.dropdown-editorial.item-sub-color}`, `{typography.hint.fontSize}` Georgia), and right-aligned `.cc-status` badges. Hover/focus uses `{components.dropdown-editorial.item-hover-background}`; selected uses `{components.dropdown-editorial.item-selected-background}` and `font-weight: 700`.

### 11. `status-badge`
- **Anatomy**: `.cc-status` inline-flex rectangular tag (`border-radius: {components.status-badge.radius}`, font `{components.status-badge.font-size}` bold Arial, `1px solid` semantic border).
- **Variants**: `.cc-status--success` (`{components.status-badge.success-fg}` / `{components.status-badge.success-bg}` / `{components.status-badge.success-border}`), `.cc-status--warning` (`{components.status-badge.warning-fg}` / `{components.status-badge.warning-bg}` / `{components.status-badge.warning-border}`), `.cc-status--danger` (`{components.status-badge.danger-fg}` / `{components.status-badge.danger-bg}` / `{components.status-badge.danger-border}`), `.cc-status--info` (`{components.status-badge.info-fg}` / `{components.status-badge.info-bg}` / `{components.status-badge.info-border}`), and `.cc-status--neutral` (`{components.status-badge.neutral-fg}` / `{components.status-badge.neutral-bg}` / `{components.status-badge.neutral-border}`). Always displays an explicit text word (never colour alone).

### 12. `tabs`
- **Anatomy**: `.cc-tabs` flex row (`role="tablist"`, `border-bottom: 1px solid {components.tabs.border-bottom}`). Individual `.cc-tab` buttons (`role="tab"`, `min-height: {spacing.control-height}` / `40px`, `border-bottom: 2px solid transparent`, font `{typography.ui-label.fontSize}`, `{components.tabs.tab-inactive-fg}`) declare explicit `id`, `aria-controls`, and roving `tabindex` (`0` on `aria-selected="true"`, `-1` on inactive tabs), linked to a `<section role="tabpanel" aria-labelledby="...">`. Active tab uses `{components.tabs.tab-active-fg}` and `border-bottom-color: {components.tabs.tab-active-underline}`.

### 13. `filter-bar`
- **Anatomy**: Horizontal `.cc-row` of `.cc-filter` toggle chips (`min-height: {spacing.control-height}` / `40px`, `border: 1px solid {components.filter-bar.chip-border}` (`#6E7E91`, `3.95:1`), `background: {components.filter-bar.chip-background}`, `color: {components.filter-bar.chip-foreground}`, `border-radius: {components.filter-bar.radius}`, font `{typography.status-badge.fontSize}` bold Arial) paired with search inputs or Tier-1 `.cc-select` filters. Active filter (`aria-pressed="true"`) uses `{components.filter-bar.chip-active-background}` fill and `{components.filter-bar.chip-active-border}` border.

### 14. `pagination`
- **Anatomy**: `<nav aria-label="Pagination">` row of `.cc-page-link` square cells (`width: {components.pagination.cell-size}`, `height: {components.pagination.cell-size}` on desktop; expanded to `{spacing.control-height} x {spacing.control-height}` / `40px x 40px` at `@media (max-width: 760px)`, `border: 1px solid {components.pagination.border}` (`#6E7E91`), `border-radius: {components.pagination.radius}`, `color: {components.pagination.foreground}`, font `{typography.status-badge.fontSize}` bold Arial) plus summary caption (`Showing 1 to 20 of 142 assets`). Current page (`aria-current="page"`) uses `{components.pagination.active-background}` fill and `{components.pagination.active-foreground}` text.

### 15. `property-map`
- **Anatomy**: Unified `<PropertyMap>` wrapper around MapLibre GL JS 5 + OpenFreeMap vector tiles inside a `1px solid {components.property-map.container-border}` frame (`border-radius: {components.property-map.radius}`, default height `360px` desktop / `260px` mobile).
- **Markers, Overlay & Single-Pointer Alternative (WCAG 2.2 SC 2.5.7)**: Default site markers render as `16px` square-shouldered pins in `{components.property-map.pin-fill}` with a `2px` `{components.property-map.pin-stroke}` border; selected/active site marker uses `{components.property-map.pin-selected-fill}`. Top-right coordinate & accuracy readout overlay uses `{components.property-map.overlay-background}`, `1px solid {components.property-map.overlay-border}`, and tabular Georgia copy (`Lat / Lng · ±accuracy m · Captured at`). Map pin placement always pairs pointer dragging with explicit Latitude/Longitude numeric inputs and a `Use current device GPS` button so users never rely on dragging alone.

### 16. `evidence-uploader`
- **Anatomy**: Field-ready upload zone (`background: {components.evidence-uploader.dropzone-background}`, `border: 1px dashed {components.evidence-uploader.dropzone-border}` (`#6E7E91`), `border-radius: {components.evidence-uploader.radius}`, `padding: {spacing.4}`, `aria-live="polite"`) with a prominent `{spacing.control-height}` `.cc-button--secondary` camera/file trigger (`Capture or select photo`).
- **Sub-States, Progressbar & Provenance**:
  - **Compressing / Uploading**: Displays a `4px`-high `.progress-track` (`role="progressbar" aria-label="Evidence upload progress" aria-valuenow="0..100" aria-valuemin="0" aria-valuemax="100"`) with `.progress-fill` in `{components.evidence-uploader.progress-bar-fill}` during client-side downscaling (`<=1920px` WebP/JPEG) and upload, plus a live provenance line (`Timestamp · GPS status badge`).
  - **Non-Blocking GPS Fallback Notice**: When indoor plant-room GPS times out or is denied, renders a polite status strip (`role="status" aria-live="polite"`, `{colors.status-neutral-bg}`) confirming timestamp capture without coordinates.
  - **Interrupted Upload Retry (`interrupted-retry`)**: When mobile signal drops mid-upload, renders a non-destructive recovery strip (`role="status" aria-live="polite"`, `{colors.status-warning-bg}`, `1px solid {colors.status-warning-border}`, `{colors.status-warning-fg}`) retaining the compressed WebP blob in memory with an inline `Retry upload` button.
- **Illustrated in**: [mobile-inspection-runner.html](mockups/mobile-inspection-runner.html) and [mobile-work-order-execution.html](mockups/mobile-work-order-execution.html).

### 17. `evidence-gallery`
- **Anatomy**: Responsive grid (`repeat(auto-fill, minmax(180px, 1fr))`, gap `{spacing.3}`) of private attachment cards (`background: {components.evidence-gallery.card-background}`, `border: 1px solid {components.evidence-gallery.card-border}`, `border-radius: {components.evidence-gallery.radius}`).
- **Content**: `4:3` aspect-ratio thumbnail loaded via 10-minute signed R2 URL, followed by a metadata footer showing uploader name, `Africa/Lagos` timestamp, optional GPS accuracy badge, and a lock badge when the parent Inspection or Work Order is immutable.

### 18. `checklist-runner`
- **Anatomy**: Structured inspection execution list grouped by template section. Section header bar uses `{components.checklist-runner.section-header-bg}` fill and `{components.checklist-runner.section-header-fg}` bold Georgia text, accompanied by a visible draft **Sync status badge** (`role="status" aria-live="polite"`: `.cc-status--success` `All N responses saved` when synced, or `.cc-status--warning` `Offline: tap to retry sync` when network-degraded).
- **Question Rows**: Alternating `{components.checklist-runner.row-background}` and `{components.checklist-runner.row-alt-background}` rows (`border: 1px solid {components.checklist-runner.row-border}`, `padding: {spacing.4}`) containing the question prompt (`id="qN-text"`, `{typography.body-base.fontSize}` Georgia), a segmented response group (`role="radiogroup" aria-labelledby="qN-text"`) of `{spacing.control-height}` (`40px`) radio buttons (`role="radio" aria-checked="true|false"` for `Pass` -> `{components.checklist-runner.pass-active-bg}` / `{components.checklist-runner.pass-active-fg}`, `Fail` -> `{components.checklist-runner.fail-active-bg}` / `{components.checklist-runner.fail-active-fg}`, `N/A` -> `{components.checklist-runner.na-active-bg}` / `{components.checklist-runner.na-active-fg}`), inline notes `.cc-input`, photo attachment trigger, and an inline `Raise maintenance request` drawer when `Fail` is selected.
- **Illustrated in**: [mobile-inspection-runner.html](mockups/mobile-inspection-runner.html).

### 19. `timeline-feed`
- **Anatomy**: Vertical chronological audit and history feed (used for Asset Timeline, Custody Handover History, Location History, and Work Order Events). Left vertical `1px solid {components.timeline-feed.spine-line}` rule with `10px x 10px` square node markers (`{components.timeline-feed.node-dot}`, or `{components.timeline-feed.node-completed-dot}` for completed/approved milestones).
- **Event Cards**: Right-hand `.cc-card` blocks (`background: {components.timeline-feed.card-background}`, `border: 1px solid {components.timeline-feed.card-border}`, `border-radius: {components.timeline-feed.radius}`, `padding: {spacing.3} {spacing.4}`) showing actor, event type badge, `Africa/Lagos` timestamp (`{components.timeline-feed.timestamp-color}`), narrative delta, and linked evidence thumbnails.

### 20. `async-export-banner`
- **Anatomy**: Full-width square-cornered (`border-radius: {components.async-export-banner.radius}`) editorial status strip at the top of the Report Compiler surface (`padding: {spacing.4} {spacing.5}`, `border: 1px solid`).
- **Visual States & Live Regions**:
  - **Queued / Compiling (`role="status" aria-live="polite"`)**: `{components.async-export-banner.compiling-background}` fill, `{components.async-export-banner.compiling-border}` border, `{components.async-export-banner.compiling-foreground}` text with an inline status badge (`Compiling PDF & CSV…`).
  - **Ready (`role="status" aria-live="polite"`)**: `{components.async-export-banner.ready-background}` fill (`#EAF3F4`), `{components.async-export-banner.ready-border}` border, `{components.async-export-banner.ready-foreground}` text with `.cc-button--teal` (`Download PDF`) and `.cc-button--secondary` (`Download CSV`) actions plus 10-minute signed URL expiry note.
  - **Failed (`role="alert"`)**: `{components.async-export-banner.failed-background}` fill, `{components.async-export-banner.failed-border}` border, `{components.async-export-banner.failed-foreground}` text with error code and `.cc-button--secondary` (`Retry export`).
- **Illustrated in**: [client-report-compiler.html](mockups/client-report-compiler.html).

### 21. `modal-dialog`
- **Anatomy**: Centered overlay dialog (`max-width: 560px`, `width: calc(100% - 32px)`, `background: {components.modal-dialog.surface}`, `border: 1px solid {components.modal-dialog.border}`, `border-radius: {components.modal-dialog.radius}`, `box-shadow: var(--cc-shadow-popover)`) over a `{components.modal-dialog.backdrop}` scrim.
- **Structure & Aliased Drawer / Sheet Variants**: Header with uppercase teal kicker, `h3` title in `{components.modal-dialog.title-color}`, `1px` `{components.modal-dialog.header-rule}` divider, Georgia body content, and right-aligned action footer. Mobile slide-over navigation sheets and inline action drawers (including the inline `Raise maintenance request` drawer in `checklist-runner`) are explicitly aliased under `modal-dialog` and share its border/header token anatomy, focus-management, `Escape`-close, and 1-level maximum stacking contract.

## Do's and Don'ts

| Do | Don't |
|---|---|
| Keep `{colors.white}` as the default page and card surface so typography and rules carry hierarchy. | Turn the UI into a dark-mode dashboard or fill grids with saturated coloured metric tiles. |
| Pair **Arial** (bold display headings, labels, buttons, badges) with **Georgia** (long-form prose, kickers, callouts, and table cells). | Set table bodies or editorial callouts in sans-serif, or set form labels and buttons in serif. |
| Use solid Ink Navy (`{colors.navy}`) headers with white Georgia text, 1px `{colors.line-strong}` borders, `tabular-nums`, and mobile scroll-shadow + sticky first column on all data tables. | Use borderless "floating" tables, hide table gridlines, or collapse mobile tables into illegible fragments instead of horizontal scrolling. |
| Use `{colors.border-control}` (`#6E7E91`, `3.95:1` contrast) on all interactive inputs, selects, dropdown triggers, and filter chips so control boundaries meet WCAG 2.2 AA SC 1.4.11. | Use low-contrast `#C7D1DC` (`1.53:1`) as the sole boundary of a form input or interactive filter chip on a white surface. |
| Reserve `{colors.teal}` (`#126E6A`, AA contrast) for uppercase kickers, recommendation labels, and positive confirm actions, and `{colors.teal-wash}` for at most one `.cc-callout` per screen. | Use teal as a generic decorative border/fill on ordinary cards or apply semantic status colours to entire card backgrounds. |
| Use tight `2px` to `3px` radii (`{rounded.sm}` / `{rounded.DEFAULT}`) on controls and `0px` (`{rounded.none}`) on tables and callouts. | Use pill-shaped buttons, rounded-full status badges, or `8px` to `16px` consumer-app card corners. |
| Always pair semantic status colours with an explicit text label (e.g., `Verified`, `Overdue`, `In progress`, `Draft`) and use sentence case across UI copy without em/en dashes. | Communicate status by colour alone, or use `Verified` on a postcode to imply property title or legal ownership. |
| Maintain `>=40px` height (`{spacing.control-height}`) on interactive controls, `16px` mobile input and body font size, `.cc-skip-link`, `scroll-padding` for sticky bars, and universal `:focus-visible` outlines. | Remove `:focus-visible` outlines, allow `<16px` mobile inputs to trigger iOS Safari auto-zoom, or shrink any metadata text below `12px`. |
