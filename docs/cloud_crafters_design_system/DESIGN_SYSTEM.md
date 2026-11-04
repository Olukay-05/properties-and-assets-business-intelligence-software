# Cloud Crafters Technologies — Editorial Intelligence Design System

**Version 1.0 · 30 October 2026**  
**Visual reference:** the supplied *Property Strategy Report* screenshots.

## 1. Design intent

Use an **editorial business-report aesthetic**: authoritative, calm, highly legible and evidence-led. The UI should feel like a well-designed consulting report translated into a working product.

The visual hierarchy comes from typography, whitespace, fine rules, table structure and selective colour. Do not turn the interface into a dashboard full of coloured cards, gradients, large rounded pills or heavy shadows.

### Core principles

1. **White is the default canvas.** Use a clean white page and white content surfaces.
2. **Navy carries authority.** Use deep navy for body copy, primary headings, table headers and primary actions.
3. **Blue explains hierarchy.** Use medium blue for section headings, links, key rules and focus states.
4. **Teal signals emphasis.** Reserve teal for uppercase kickers, recommendation labels and positive/confirmed states.
5. **Pale teal is for important context.** Use the light teal wash for a recommendation, decision note or short explanatory callout, not for every card.
6. **The body remains editorial.** Use a readable serif for long-form explanation and a clean sans serif for interface controls and display headings.
7. **Borders stay thin and square.** Prefer 1px lines and 2–3px corner radii. Avoid pill-shaped controls and thick outlines.
8. **Colour must communicate meaning.** Every coloured element should indicate hierarchy, selection, status or an action.

## 2. Colour tokens

| Token | Hex | Intended use |
|---|---|---|
| Ink navy | `#14243A` | Body text, table header background, strong gridlines |
| Heading navy | `#17365D` | Page titles, primary buttons, important labels |
| Editorial blue | `#4F81BD` | Section headings, links, rules, focus accents |
| Blue hover | `#365F91` | Hover and active link/button state |
| Accent teal | `#168C88` | Kicker labels, recommendation labels, positive state |
| Pale teal | `#EAF3F4` | Recommendation and context callout background |
| Alternating row | `#F4F7FA` | Even table rows and low-emphasis surface |
| Quiet border | `#C7D1DC` | Inputs, cards, tabs and separators |
| Muted text | `#5F6F82` | Secondary descriptions and metadata |
| White | `#FFFFFF` | Page and surface background |

### Semantic colours

| Meaning | Foreground | Background |
|---|---|---|
| Success / confirmed | `#126E6A` | `#EAF3F1` |
| Warning / attention | `#795014` | `#F7F1E6` |
| Error / overdue | `#853838` | `#F8EEEE` |
| Information | `#365F91` | `#EEF3F9` |
| Neutral | `#5F6F82` | `#F1F3F5` |

**Usage rules:**
- Do not use teal as a generic decoration or apply every semantic colour to a whole card.
- Red is reserved for destructive actions, errors, overdue items or serious risk.
- Amber is reserved for due-soon, pending review or warning conditions.
- Green/teal means completed, verified, active or healthy; do not use it to imply ownership or legal validation.
- Maintain strong text/background contrast. Never communicate status by colour alone; include a word or icon.

## 3. Typography

| Role | Typeface | Weight | Guidance |
|---|---|---|---|
| Display / page title | Arial / Helvetica | Bold | Large navy, compact line-height |
| Section heading | Arial / Helvetica | Bold | Blue, clear hierarchy |
| Body / long-form | Georgia / Times New Roman | Regular | Editorial reading, 1.4–1.5 line-height |
| Kicker / eyebrow | Georgia | Bold uppercase | Teal, slightly tracked |
| Subtitle | Arial / Helvetica | Italic | Blue, one level below title |
| Controls / labels | Arial / Helvetica | Bold or regular | Compact, functional, consistent |
| Table copy | Georgia | Regular; key column bold | 14–16px depending on density |
| Metadata | Georgia or Arial | Regular | Muted, smaller than body |

### Type scale

- 42px: report/product hero title (responsive down to 34px)
- 32px: major page heading
- 24px: section heading
- 20px: card or subsection heading
- 18px: body copy and subtitles
- 16px: compact body and table copy
- 14px: labels, control text and secondary information
- 12px: metadata, helper text and compact status labels

Keep long-form paragraph widths comfortable (roughly 65–85 characters). Avoid all-caps for paragraphs or ordinary UI labels.

## 4. Spacing, geometry and borders

- Use a 4px base spacing scale: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64px.
- Keep major sections separated by 32–48px; related controls by 8–16px.
- Standard control height: 40px; compact action: 32px.
- Default component border: 1px `#C7D1DC`.
- Strong table grid: 1px `#263244`.
- Signature divider: 1px `#4F81BD`.
- Corner radius: 2–3px. Use square corners for report callouts and table structure.
- Default shadow: none. Use a very subtle shadow only for an overlay or menu that must separate from content.

## 5. Component standards

### Page header

Use an uppercase teal kicker, a large navy display title, a thin blue rule and an optional italic blue subtitle. Organisation name and report metadata sit below in a quieter typographic tier. Keep the header open and uncluttered.

### Section headings

Use a blue sans-serif heading. A teal uppercase kicker may precede it when a section needs a category label. Do not put headings inside filled cards by default.

### Recommendation callout

Use the pale teal background `#EAF3F4`, a teal uppercase label and navy serif body copy. No thick border, no shadow and no unnecessary rounded corners. Keep it for one key recommendation or decision at a time.

### Tables

Tables are a signature part of the visual system.
- Header: solid ink navy `#14243A`, white bold serif text.
- Body: white with alternate rows in `#F4F7FA`.
- Grid: thin 1px dark lines; square corners.
- Cell padding: 10–12px vertical and 12px horizontal.
- First/descriptor column: bold; use a sensible width (about one third for two-column decision tables).
- Keep numeric values right-aligned and use consistent units.
- On small screens, preserve table structure and allow horizontal scroll rather than collapsing values into unreadable fragments.
- Use captions to explain scope, units or source where needed.

### Cards

Cards should be quiet, content-led and mostly white. Use a fine border and small radius. Do not add a shadow by default. For one key metric or selected card, use a thin blue top edge. Avoid grids of heavily coloured metric tiles.

### Buttons

Buttons should look like the report's typography and rules, not like a generic consumer app.

| Variant | Appearance | Use |
|---|---|---|
| Primary | Solid heading navy, white bold text, 1px navy border | Main action on a page or form |
| Secondary | White background, 1px navy border, navy text | Alternate or lower-priority action |
| Teal | Solid teal, white bold text | Confirm/complete/verify action only when positive meaning is clear |
| Text | Transparent, blue underlined text | Low-priority inline action |

- Use 2–3px radius; do not use pill buttons.
- Standard height 40px; compact height 32px.
- Keep labels short and action-oriented: “Save changes”, “Create work order”, “Review inspection”.
- Destructive actions use a restrained red treatment and should not resemble the primary action.
- Disabled state: muted with reduced opacity and no hover treatment.
- Focus state: visible 3px soft blue ring; never remove keyboard focus indicators.
- Provide loading and success feedback for actions that save data; do not rely on colour alone.

### Inputs and forms

- White field, quiet 1px border, 2px radius.
- Navy sans-serif labels; muted serif helper copy.
- Focus: blue border and soft blue ring (`var(--cc-focus)`).
- Error: red border plus a concise text explanation.
- Keep labels visible; do not rely on placeholder text as the label.
- Group related fields with whitespace and clear section headings rather than heavy boxes.

### Selects and dropdowns

The design system supports a two-tier approach to dropdown menus:

1. **Enhanced native select (`.cc-select`)**:
   - Uses `appearance: none;` with an embedded SVG chevron in `--cc-muted` (`#5F6F82`), custom right padding, and standard focus ring.
   - Recommended for standard single-choice form fields (e.g. priority, status filter, category).
   - Zero JavaScript dependency and native OS picker on mobile devices (iOS wheel picker / Android bottom sheet).
   - Limitation: `<option>` popup list items are rendered by the OS and cannot host custom markup.

2. **Custom editorial dropdown (`.cc-dropdown`)**:
   - Used when choices need rich hierarchy, secondary descriptions, or inline status pills (e.g. `<span class="cc-status cc-status--warning">Due soon</span>`).
   - Trigger button matches form input height (`40px`), quiet border, and rotating chevron indicator.
   - Popover menu uses `--cc-surface`, `--cc-border-thin`, 2px radius, and `--cc-shadow-popover`.
   - Accessible ARIA keyboard navigation: `ArrowUp`/`ArrowDown` selection, `Enter`/`Space` activation, `Escape` to close, and full focus restoration.

### Status labels

Use restrained tinted rectangles with a small radius, not bright pills. Include a readable word such as “Verified”, “In progress”, “Overdue” or “Draft”. Status labels should be compact and consistent.

### Tabs and filters

Use text tabs with a thin blue underline for the active tab. Filters use white backgrounds and quiet borders; selected filters get a pale blue wash and blue border. Avoid filled saturated tabs.

### Alerts and empty states

Use a neutral text-first message with a subtle semantic accent. Alert text should identify what happened, the impact and the next action. Empty states should be helpful and restrained; no large illustrations are required by this visual language.

## 6. Product usage examples

For the Property & Asset Intelligence product:
- “Verified” can mean the postcode has been validated through an authorised source; it must not imply property title or legal ownership.
- Use amber for a maintenance request approaching its due date.
- Use red for an overdue work order or failed safety inspection.
- Use teal for an inspection completed and submitted, after the completion event is recorded.
- Use the pale teal callout for a high-priority portfolio note, not for every asset summary.
- In asset tables, keep the asset ID, property/site, custodian, condition, maintenance status and last-inspected date easy to scan.

## 7. Accessibility and responsive behaviour

- Maintain text contrast and visible keyboard focus.
- Controls must have labels and clear accessible names.
- Pair status colour with text; use icons only as supporting cues.
- Make interactive targets at least 40px high where practical.
- Preserve table headers and relationships for assistive technology.
- On mobile, stack content sections; keep tables scrollable; keep primary actions visible without making them full-screen banners.
- Do not reduce body text below 14px for core business workflows.

## 8. Do / do not

**Do** use white space, navy hierarchy, blue rules, teal kickers, Georgia reading text, fine borders and disciplined tables.  
**Do not** use gradients, glassmorphism, neon colours, oversized rounded cards, thick shadows, excessive badges, decorative icons on every heading or multiple competing accent colours.

## 9. Implementation

The accompanying `cloud-crafters-design-system.css` defines reusable CSS variables and component classes. The `design-system-preview.html` demonstrates the typography, callout, table, cards, form fields, status labels, tabs and button variants.

For an existing product, implement the tokens first, then migrate components one family at a time. Keep the visual identity consistent across dashboards, admin screens, reports, exports and customer-facing portals, while allowing each surface to use an appropriate information density.
