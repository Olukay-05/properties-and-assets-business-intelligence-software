# Input Reconciliation Report 3: Cloud Crafters Design System (`DESIGN_SYSTEM.md`, `cloud-crafters-design-system.css`, `design-system-preview.html`)

- **Sources Reconciled**:
  - `docs/cloud_crafters_design_system/DESIGN_SYSTEM.md`
  - `docs/cloud_crafters_design_system/cloud-crafters-design-system.css`
  - `docs/cloud_crafters_design_system/design-system-preview.html`
- **Target UX Spines**:
  - `_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/DESIGN.md`
  - `_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/EXPERIENCE.md`
- **Reconciliation Status**: **PASS — 100% of CSS custom properties, hex values, typography scales, spacing/geometry rules, component classes, interactive preview behaviors, and Do's/Don'ts are preserved with zero token loss.**

---

## 1. Colour Tokens & CSS Custom Properties (`:root` + Hardcoded Hex Values)

Every colour token in `DESIGN_SYSTEM.md` §2, `cloud-crafters-design-system.css`, and `design-system-preview.html` is accounted for in `DESIGN.md` frontmatter (`colors`) and prose:

| CSS Variable / Source Rule | Source Hex / Value | `DESIGN.md` Token Key & Value | Parity Status |
|---|---|---|---|
| `--cc-white` / `--cc-page` / `--cc-surface` | `#FFFFFF` | `colors.white`, `colors.page`, `colors.surface`: `'#FFFFFF'` | Exact match |
| `design-system-preview.html` `body { background }` | `#f2f4f6` | `colors.shell-backdrop`: `'#F2F4F6'` | Exact match |
| `--cc-row` | `#F4F7FA` | `colors.row-alt`: `'#F4F7FA'` | Exact match |
| `--cc-navy` (Ink navy) | `#14243A` | `colors.navy`: `'#14243A'` | Exact match |
| `--cc-navy-2` (Heading navy) | `#17365D` | `colors.navy-heading`: `'#17365D'` | Exact match |
| `--cc-blue` (Editorial blue) | `#4F81BD` | `colors.blue` & `colors.rule-blue`: `'#4F81BD'` | Exact match |
| `--cc-blue-dark` (Blue hover / interactive) | `#365F91` | `colors.blue-dark`: `'#365F91'` | Exact match |
| `--cc-teal` (Accent teal) | `#168C88` | `colors.teal`: `'#168C88'` | Exact match |
| `.cc-button--teal:hover` | `#117874` | `colors.teal-hover`: `'#117874'` | Exact match |
| `--cc-teal-wash` (Pale teal) | `#EAF3F4` | `colors.teal-wash`: `'#EAF3F4'` | Exact match |
| `--cc-line` (Strong table grid / rules) | `#263244` | `colors.line-strong`: `'#263244'` | Exact match |
| `--cc-border` (Quiet component border) | `#C7D1DC` | `colors.border`: `'#C7D1DC'` | Exact match |
| `.cc-input:hover`, `.cc-dropdown__trigger:hover` | `#8D9CAF` | `colors.border-hover`: `'#8D9CAF'` | Exact match |
| `--cc-muted` (Muted text) | `#5F6F82` | `colors.muted` & `colors.neutral`: `'#5F6F82'` | Exact match |
| `--cc-subtle` (Metadata) | `#7B8794` | `colors.subtle`: `'#7B8794'` | Exact match |
| `.cc-input::placeholder`, `.cc-dropdown__value--placeholder` | `#8793A0` | `colors.placeholder`: `'#8793A0'` | Exact match |
| `--cc-success` (Base semantic token) | `#168C88` | `colors.success`: `'#168C88'` | Exact match |
| `--cc-warning` (Base semantic token) | `#94651D` | `colors.warning`: `'#94651D'` | Exact match |
| `--cc-danger` (Base semantic token) | `#A34545` | `colors.danger`: `'#A34545'` | Exact match |
| `--cc-info` (Base semantic token) | `#4F81BD` | `colors.info`: `'#4F81BD'` | Exact match |
| `.cc-status--success` (FG / `--cc-success-bg` / border) | `#126E6A` / `#EAF3F1` / `#C8E0DC` | `colors.status-success-fg` (`#126E6A`), `status-success-bg` (`#EAF3F1`), `status-success-border` (`#C8E0DC`) | Exact match |
| `.cc-status--warning` (FG / `--cc-warning-bg` / border) | `#795014` / `#F7F1E6` / `#E8D9BA` | `colors.status-warning-fg` (`#795014`), `status-warning-bg` (`#F7F1E6`), `status-warning-border` (`#E8D9BA`) | Exact match |
| `.cc-status--danger` (FG / `--cc-danger-bg` / border) | `#853838` / `#F8EEEE` / `#EBCFCF` | `colors.status-danger-fg` (`#853838`), `status-danger-bg` (`#F8EEEE`), `status-danger-border` (`#EBCFCF`) | Exact match |
| `.cc-status--info` (FG / `--cc-info-bg` / border) | `#365F91` / `#EEF3F9` / `#D4E0EF` | `colors.status-info-fg` (`#365F91`), `status-info-bg` (`#EEF3F9`), `status-info-border` (`#D4E0EF`) | Exact match |
| `.cc-status--neutral` (FG / BG / border) | `#5F6F82` / `#F1F3F5` / `#DCE1E6` | `colors.status-neutral-fg` (`#5F6F82`), `status-neutral-bg` (`#F1F3F5`), `status-neutral-border` (`#DCE1E6`) | Exact match |

*Note on Semantic vs. Status Badge Reconciliation*: `cloud-crafters-design-system.css` defines base semantic variables (`--cc-success: #168C88`, `--cc-warning: #94651D`, `--cc-danger: #A34545`, `--cc-info: #4F81BD`) in `:root` while using slightly darker, higher-contrast foreground hexes (`#126E6A`, `#795014`, `#853838`, `#365F91`) with dedicated border hexes (`#C8E0DC`, `#E8D9BA`, `#EBCFCF`, `#D4E0EF`, `#DCE1E6`) in `.cc-status--*` rules (which also match the semantic table in `DESIGN_SYSTEM.md` §2). `DESIGN.md` explicitly preserves **both** sets of tokens (`colors.success`..`colors.neutral` for borders/validation and `colors.status-*-fg/bg/border` for badges) and documents their WCAG 2.1 AA contrast ratios in §Colors.

---

## 2. Typography, Spacing, Borders & Elevation Variables

| Source Variable / Spec (`DESIGN_SYSTEM.md` §§3–4 & `.css`) | Source Value | `DESIGN.md` Mapping | Parity Status |
|---|---|---|---|
| `--cc-font-display` & `--cc-font-ui` | `Arial, Helvetica, sans-serif` | `typography.display-*`, `heading-*`, `subtitle`, `ui-label`, `ui-button`, `status-badge`, `card-value` | Exact match |
| `--cc-font-body` | `Georgia, 'Times New Roman', serif` | `typography.body-*`, `kicker`, `table-header`, `table-cell`, `card-eyebrow`, `hint` | Exact match |
| `--cc-text-4xl` (`42px`, `clamp(2.1rem, 4vw, 2.625rem)`) | `42px` / `700` / `1.12` / `-0.025em` | `typography.display-hero` + prose `clamp(2.1rem, 4vw, 2.625rem)` | Exact match |
| `--cc-text-3xl` (`32px`) | `32px` / `700` | `typography.display-page` (`1.15`) & `typography.card-value` (`1.1`) | Exact match |
| `--cc-text-2xl` (`24px` `h2`) | `24px` / `700` / `1.2` | `typography.heading-section` | Exact match |
| `--cc-text-xl` (`20px` `h3` & `.cc-subtitle`) | `20px` (`700` `1.25` / `400 italic` `1.35`) | `typography.heading-subsection` & `typography.subtitle` | Exact match |
| `--cc-text-lg` (`18px` desktop `p`, `.cc-callout__body`) | `18px` / `400` / `1.42` | `typography.body-lg` | Exact match |
| `--cc-text-base` (`16px` mobile `p`, compact table copy) | `16px` / `400` / `1.42` | `typography.body-base` | Exact match |
| `--cc-text-sm` (`14px` labels, buttons, table copy, kickers, meta) | `14px` | `typography.body-sm`, `ui-label`, `ui-button`, `kicker`, `table-header`, `table-cell` | Exact match |
| `--cc-text-xs` (`12px` status badges, eyebrows, hints, `--small` btn) | `12px` | `typography.status-badge`, `card-eyebrow`, `hint` | Exact match |
| Reading measure (`65–85 characters`) | `65–85ch` | `DESIGN.md` §Typography (`65ch–85ch`) | Exact match |
| `--cc-space-1` .. `--cc-space-16` | `4, 8, 12, 16, 20, 24, 32, 40, 48, 64px` | `spacing.1` .. `spacing.16` | Exact match |
| `.cc-page` geometry (`1120px`, `56px 64px` desktop, `32px 22px` mobile `<=760px`) | `1120px`, `56px 64px`, `32px 22px` | `spacing.page-max`, `spacing.page-pad-desktop`, `spacing.page-pad-mobile` | Exact match |
| `--cc-control-height` (`40px`), `.cc-button--small` (`32px`), `.cc-page-link` (`34px`) | `40px`, `32px`, `34px` | `spacing.control-height`, `spacing.control-height-sm`, `spacing.pagination-cell` | Exact match |
| `--cc-radius-sm` (`2px`), `--cc-radius` (`3px`), square (`0px`) | `0px`, `2px`, `3px` | `rounded.none` (`0px`), `rounded.sm` (`2px`), `rounded.DEFAULT` (`3px`) | Exact match |
| `--cc-shadow`, `--cc-shadow-popover`, `--cc-focus`, `--cc-transition` | `0 2px 8px rgba(20,36,58,.08)`, `0 8px 24px rgba(20,36,58,.14)`, `0 0 0 3px rgba(79,129,189,.22)`, `150ms ease` | `DESIGN.md` §Elevation & Depth | Exact match |

---

## 3. Component & Preview Pattern Reconciliation

| Design System Component / Class | Source Spec (`DESIGN_SYSTEM.md` §5, `.css`, `design-system-preview.html`) | `DESIGN.md` & `EXPERIENCE.md` Component Mapping | Status |
|---|---|---|---|
| **Page Header** (`.cc-kicker`, `h1`, `.cc-rule`, `.cc-subtitle`, `.cc-meta`) | Uppercase teal Georgia kicker, large navy Arial title, 1px blue rule, italic blue subtitle, muted metadata row. | Component #3 `page-header` | Covered |
| **Recommendation Callout** (`.cc-callout`, `.cc-callout__label`, `.cc-callout__body`) | Pale teal `#EAF3F4`, `16px 20px` pad, `0` border, `0px` radius, teal uppercase Georgia label, `18px` navy Georgia body; max 1 per screen. | Component #4 `recommendation-callout` | Covered |
| **Tables** (`.cc-table-wrap`, `.cc-table`, `th`, `td`, `caption`) | `1px solid #263244` grid, `#14243A` header with white bold Georgia text, `#F4F7FA` even rows, bold `td:first-child` (`34%` in 2-col tables), `min-width: 560px` horizontal scroll on mobile, top `caption`. | Component #5 `data-table` | Covered |
| **Cards** (`.cc-card`, `.cc-card--emphasis`, `__eyebrow`, `__value`, `__note`) | White surface, `1px solid #C7D1DC`, `2px` radius, `20px` pad (`16px` mobile), `box-shadow: none`; optional `3px solid #4F81BD` top edge on 1 focal card. | Component #6 `editorial-card` | Covered |
| **Buttons** (`.cc-button`, `--primary`, `--secondary`, `--teal`, `--text`, `--small`, `:disabled`) | `40px` (`32px` small), `2px` radius, bold Arial; primary `#17365D` → `#365F91`; secondary white + `#17365D` border → `#EEF3F9`; teal `#168C88` → `#117874`; text `#365F91` underlined; destructive red; disabled `opacity: 0.46`. | Component #7 `button` | Covered |
| **Inputs & Forms** (`.cc-field`, `.cc-label`, `.cc-input`, `.cc-textarea`, `.cc-hint`, `.cc-error`) | `6px` grid gap, `#17365D` bold Arial label, `40px` input (`100px` textarea), `#8793A0` placeholder, `#8D9CAF` hover border, `--cc-focus`, `aria-invalid="true"` `#A34545` border + `.cc-error`. | Component #8 `form-field` | Covered |
| **Tier-1 Native Select** (`.cc-select`) | `appearance: none`, embedded `#5F6F82` SVG chevron (`14px` at `right 11px`), `padding-right: 34px`, disabled `#F4F7FA` + `0.46` opacity; native OS mobile picker. | Component #9 `select-native` + `Interaction Primitives` #1 | Covered |
| **Tier-2 Custom Editorial Dropdown** (`.cc-dropdown`, `__trigger`, `__value`, `__arrow`, `__menu`, `__item`, `__item-title`, `__item-sub` + JS keyboard nav in `design-system-preview.html`) | `40px` trigger, `180deg` arrow rotation on `.is-open`, `max-height: 280px` popover with `--cc-shadow-popover`, item hover `#EEF3F9`, selected `#F4F7FA` bold, inline `.cc-status` badge cloning, `ArrowDown`/`ArrowUp`/`Enter`/`Space`/`Escape`/`Tab` + outside-click close. | Component #10 `dropdown-editorial` + `Interaction Primitives` #1 | Covered |
| **Status Labels** (`.cc-status`, `--success`, `--warning`, `--danger`, `--info`, `--neutral`) | `4px 8px` pad, `2px` radius, `12px` bold Arial, `1px` semantic border; always includes readable word; never implies property title on postcode. | Component #11 `status-badge` + `Voice and Tone` rule #2 | Covered |
| **Tabs** (`.cc-tabs`, `.cc-tab`) | `gap: 22px`, `1px solid #C7D1DC` bottom border, `padding: 10px 1px`, active `aria-selected="true"` `#17365D` text + `2px solid #4F81BD` bottom border. | Component #12 `tabs` | Covered |
| **Filters & Dividers** (`.cc-filter`, `.cc-divider`) | `.cc-filter` (`7px 10px`, `2px` radius, `aria-pressed="true"` `#EEF3F9` fill + `#4F81BD` border); `.cc-divider` (`1px solid #C7D1DC`). | Component #13 `filter-bar` | Covered |
| **Pagination** (`.cc-page-link`) | `34px × 34px` square grid cell (`40px` touch target on mobile), `aria-current="page"` `#17365D` fill + white text. | Component #14 `pagination` | Covered |
| **Domain Workflow Extensions** (Components #1, #2, #15–#21 in `DESIGN.md`) | Built strictly from the `DESIGN_SYSTEM.md` token set (`app-shell-header`, `mobile-quick-task-bar`, `property-map`, `evidence-uploader`, `evidence-gallery`, `checklist-runner`, `timeline-feed`, `async-export-banner`, `modal-dialog`). | Components #1, #2, #15–#21 | Covered |

---

## 4. Do's, Don'ts & Product Usage Rules (`DESIGN_SYSTEM.md` §§6–8)

| Source Rule (`DESIGN_SYSTEM.md` §§6–8) | Verification in `DESIGN.md` & `EXPERIENCE.md` | Status |
|---|---|---|
| **Postcode "Verified" guardrail**: May mean postcode validated via authorised source; must **never** imply property title or legal ownership. | `DESIGN.md` §Colors table, §Do's and Don'ts, and `EXPERIENCE.md` §Voice and Tone rule #2. | Covered |
| **Semantic state assignment**: Amber for due-soon maintenance; red for overdue work order or failed inspection; teal for completed/submitted inspection after completion event is recorded. | `DESIGN.md` §Colors semantic table + `checklist-runner` (#18) + `button` (#7 `.cc-button--teal`). | Covered |
| **Callout restraint**: Pale teal callout for one high-priority portfolio note or decision at a time, not for every asset summary. | `DESIGN.md` §Colors, §Do's and Don'ts, and `EXPERIENCE.md` Component #4 (`At most one .cc-callout per surface`). | Covered |
| **Asset table scanability**: Keep asset ID/tag, property/site, custodian, condition, maintenance status, and last-inspected date easy to scan. | `EXPERIENCE.md` `Registry/AssetsIndex.tsx`, `data-table` (#5), and `UJ-1` step 5. | Covered |
| **Do / Do Not anti-patterns**: No gradients, glassmorphism, neon colours, oversized rounded cards, pill badges, thick shadows, decorative icons on every heading, or body text `<14px`. | `DESIGN.md` §Elevation & Depth, §Shapes, §Do's and Don'ts, and `EXPERIENCE.md` §Inspiration & Anti-patterns. | Covered |

---

## 5. Dropped Qualitative Ideas or Triage Items

- **Dropped Qualitative Ideas**: **None.** Every principle, CSS token, component class, and product-specific usage example from `DESIGN_SYSTEM.md`, `cloud-crafters-design-system.css`, and `design-system-preview.html` is preserved in `DESIGN.md` and `EXPERIENCE.md`.
- **Triage Items**: **None required.**
