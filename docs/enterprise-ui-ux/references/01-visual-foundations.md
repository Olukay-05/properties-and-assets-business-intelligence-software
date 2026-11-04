# 01 Visual Foundations

Visual design exists to make structure and priority obvious. Decoration is last.

## 1. Hierarchy

Hierarchy is the order in which the eye reads. Control it with these levers, strongest first:

1. Size
2. Weight and contrast (value difference against the background)
3. Color and saturation
4. Position (top and left in left-to-right locales read first)
5. Space around an element (isolation draws attention)
6. Shape and containment

Rules:
- One focal point per view, one primary action per view. Secondary actions look quieter, tertiary actions look like text links.
- **Squint test:** blur your view or shrink the screenshot. The primary action and the main heading must still stand out.
- Use no more than three levels of emphasis inside one component. Beyond that, nothing is emphasized.
- Do not use size alone. Combine two levers (for example size plus weight) for primary items, and one lever for secondary items.
- De-emphasize instead of emphasizing everything: make supporting text lighter in value, not smaller than the legibility floor.

## 2. Gestalt principles in layout

| Principle | Use it to |
|---|---|
| Proximity | Group related items by spacing. Space between groups must clearly exceed space within groups (at least 1.5 to 2 times) |
| Similarity | Make items with the same function look the same |
| Common region | Put related items in a shared container or card |
| Continuity | Align items on a line so the eye flows along it |
| Closure | Let users complete simple shapes, which allows lighter borders |
| Figure and ground | Keep content clearly above its background, use scrims behind overlays |
| Uniform connectedness | Lines, arrows and shared backgrounds link items strongly |
| Focal point | Distinct items attract attention, so reserve distinction for what matters |

Prefer spacing over borders for grouping. Use borders and dividers only when spacing cannot do the job.

## 3. Layout and grids

- Use a base unit of 4px, with an 8px rhythm for most spacing. All spacing values come from a scale (for example 4, 8, 12, 16, 24, 32, 48, 64, 96).
- Web grids: 4 columns on mobile, 8 on tablet, 12 on desktop. Gutters 16px on mobile, 24px on desktop. Margins 16px mobile, 24 to 32px tablet, auto-centered containers on desktop.
- Content max width: 1200 to 1440px for app shells, 640 to 720px for reading content.
- Align to the grid, then align internal elements to each other. Misalignment by 1 or 2 pixels reads as sloppy.
- Use consistent page templates: app shell (nav, header, content, optional side panel), list and detail, form page, settings page, dashboard, empty and onboarding.
- Layout reading patterns (F-pattern for text heavy pages, Z-pattern for sparse pages) describe behavior, they are not templates to force. Put key content top left (LTR) and do not hide key actions below the fold on mobile.
- Whitespace is structure. Dense is fine when the user is an expert doing repeated work, but density needs strong alignment and clear grouping.

### Density
Offer comfortable and compact modes for data heavy products. Default row height 48px (comfortable) and 36 to 40px (compact). Never drop touch targets under minimums on touch devices.

## 4. Typography

### Choices
- One family is enough. Two at most (one for headings or display, one for text and UI). Prefer system or variable fonts for performance. Self-host and subset. Use `font-display: swap` and a metric-matched fallback to avoid layout shift.
- Weights: use 2 to 3 (regular 400, medium 500, semibold 600 or bold 700). Avoid thin weights for small text.
- Verify the font supports needed glyphs, including the Naira sign (₦) and diacritics for Yoruba, Igbo and Hausa where relevant.

### Scale
Use a modular scale with a ratio between 1.125 and 1.333. A common product scale (px at base 16):

| Token | Size | Line height | Typical use |
|---|---|---|---|
| text-xs | 12 | 16 | Captions, legal, badges |
| text-sm | 14 | 20 | Secondary UI text, table cells |
| text-base | 16 | 24 | Body text, inputs |
| text-lg | 18 | 28 | Lead text, large controls |
| text-xl | 20 | 28 | Card titles |
| text-2xl | 24 | 32 | Section headings |
| text-3xl | 30 | 36 | Page titles |
| text-4xl | 36 | 40 | Hero and marketing |

### Rules
- Body text 16px minimum on mobile web. Inputs 16px minimum on iOS to avoid zoom on focus.
- Line length 45 to 75 characters for reading text. Cap with `max-width: 65ch` to `75ch`.
- Line height: 1.4 to 1.6 for body, 1.1 to 1.3 for headings. Increase for long lines.
- Letter spacing: slight negative on large headings, slight positive on small all caps. Avoid all caps for long text.
- Left align text in LTR languages. Avoid justified text on the web. Center only short, isolated text.
- Use tabular (monospaced) numerals for numbers that align in columns or change live (`font-variant-numeric: tabular-nums`).
- Use sentence case for UI. Title case only for proper names.
- Fluid type for marketing and large headings: `clamp()` between a min and max. Keep app UI type fixed per breakpoint.
- Respect user font scaling. Use rem or equivalent. Test at 200 percent.
- Truncate with an ellipsis only when the full text is available elsewhere (tooltip, detail view). Prefer wrapping for important text.

## 5. Color

### Structure
Build color in three layers:
1. **Primitive scales:** 9 to 11 steps per hue (50 to 950). Generate in a perceptual space (OKLCH or HCT) so steps feel even. Neutrals are slightly tinted toward the brand hue.
2. **Semantic roles:** background, surface, surface-raised, border, border-strong, text-primary, text-secondary, text-disabled, text-inverse, accent, accent-hover, accent-pressed, success, warning, danger, info, focus-ring.
3. **Component tokens:** button-primary-bg and so on, mapped to semantic roles.

### Usage
- 60-30-10 as a starting balance: 60 percent neutral, 30 percent secondary surfaces, 10 percent accent. Accent is rare so it carries meaning.
- Color communicates meaning only with a second cue (icon, text, shape, pattern). See file 03.
- Reserve red for danger and errors, green for success, amber for warning, blue for info or links. Do not repurpose them for decoration. Respect cultural meaning in the target market.
- Limit chromatic hues in the UI to the brand color plus status colors, with extra hues only for data visualization.
- Text contrast minimums: 4.5:1 for normal text, 3:1 for large text (24px, or 19px bold) and for UI component boundaries and focus indicators. Aim higher for body text (7:1 is comfortable).
- Do not place text on photos without a scrim or overlay that guarantees contrast.
- Disabled states are exempt from contrast rules but should stay recognizable. Always explain why something is disabled in nearby text.

### Dark mode
- Not an inversion. Design surfaces from darkest (background) to lightest (raised elements), because elevation is shown with lighter surfaces, not shadows.
- Avoid pure black backgrounds with pure white text on large areas. Use dark neutral (for example near 8 to 12 percent lightness) and off-white (around 90 to 95 percent).
- Desaturate and lighten accent colors so they keep contrast on dark surfaces.
- Recheck every semantic color in both themes. Test images and illustrations.
- Support `prefers-color-scheme` and allow a manual override stored per user.

## 6. Elevation, borders, radius

- Elevation levels (0 to 5) map to meaning: 0 page, 1 card, 2 sticky header, 3 dropdown and popover, 4 modal, 5 toast. Use a small fixed set of shadows. Soft and layered shadows beat one hard shadow.
- Borders: 1px, low contrast against the surface, stronger on interactive controls (inputs need 3:1 against their background).
- Radius: pick a scale (for example 4, 8, 12, 16, full) and map to component size. Nested radii: inner radius equals outer radius minus padding.
- Be consistent. Mixing sharp and rounded styles randomly reads as unfinished.

## 7. Iconography and imagery

- One icon set, one stroke weight, one corner style, 24px grid with 16 and 20 variants when needed.
- Pair icons with text labels unless the icon is universally understood (close, search, menu, back). Icon-only controls need an accessible name and a tooltip on hover and focus.
- Use icons to aid scanning in lists and navigation, not as ornaments.
- Images: define aspect ratios to prevent layout shift, use responsive sources, modern formats (AVIF, WebP) with fallbacks, lazy load below the fold, and meaningful alt text (empty alt for decorative images).
- Illustrations and photos should reflect the real audience. Avoid stock imagery that misrepresents the user base.
- Avatars and fallbacks: initials on a deterministic background color when a photo is missing.

## 8. Motion

Motion explains change. It is not entertainment.

- Purposes: show where something came from or went, guide attention, confirm an action, smooth a state change, hide loading.
- Durations: 100 to 150ms for small feedback (hover, press), 200 to 300ms for element transitions, 300 to 500ms for large layout or page transitions. Longer than 500ms feels slow in product UI.
- Easing: ease-out for entering, ease-in for exiting, ease-in-out for moving between positions. Avoid linear except for continuous progress or loops.
- Animate transform and opacity only where possible. Avoid animating layout properties.
- Stagger lists gently (20 to 50ms steps, capped at about 300ms total).
- Respect `prefers-reduced-motion`: replace movement with fades or no animation, and remove parallax, auto-play and large zooms.
- Never rely on animation to carry essential information. Do not block interaction while animating.
- Marketing and brand sites can use richer scroll and motion work (for example GSAP) but must keep it performant, interruptible and reduced-motion safe.

## 9. Visual QA checklist

- [ ] The squint test passes: the primary action and heading stand out
- [ ] All spacing, sizes, colors come from tokens
- [ ] Type scale used, line length within limits, no orphaned tiny text
- [ ] Contrast checked in every theme
- [ ] Alignment checked on the grid and between sibling elements
- [ ] Group spacing is clearly larger than within-group spacing
- [ ] Icons consistent in style and size, labeled where needed
- [ ] Images have fixed aspect ratios and alt text
- [ ] Motion is purposeful, short, and reduced-motion safe
