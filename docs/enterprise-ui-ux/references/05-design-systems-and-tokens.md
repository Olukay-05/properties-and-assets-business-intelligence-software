# 05 Design Systems and Tokens

A design system is shared language between design and code. Its job is consistency at speed and quality at scale.

## 1. System layers

1. **Foundations:** principles, color, type, spacing, grid, elevation, motion, iconography, accessibility rules.
2. **Tokens:** named design decisions that code and design tools consume.
3. **Components:** reusable UI parts with defined anatomy, variants, states and behavior.
4. **Patterns:** recurring solutions that combine components (forms, filters, empty states, navigation, data tables).
5. **Templates and page layouts:** the shells that hold patterns.
6. **Guidelines and governance:** usage rules, contribution process, versioning, deprecation.

Atomic design is a useful mental model (atoms, molecules, organisms, templates, pages). Use it to reason about composition, not as a rigid folder structure.

## 2. Design tokens

### Tiers
| Tier | Example | Purpose |
|---|---|---|
| Primitive (global) | `color.green.500`, `space.4`, `radius.md` | Raw palette and scale values. Never used directly in components |
| Semantic (alias) | `color.bg.surface`, `color.text.primary`, `color.border.default`, `color.action.primary` | Meaning and role. The main layer components use. Themes remap these |
| Component | `button.primary.bg`, `input.border.focus` | Component specific overrides, only when needed |

### Categories
Color, typography (family, size, weight, line height, letter spacing), spacing, sizing, radius, border width, elevation (shadow), opacity, z-index, motion (duration, easing), breakpoints, icon sizes.

### Naming rules
- Name by role, not by value: `color.text.danger`, not `color.red`.
- Structure: `category.type.item.state` for example `color.action.primary.hover`.
- States as suffixes: `default`, `hover`, `pressed`, `focus`, `disabled`, `selected`.
- Scales use consistent steps: `space.1` to `space.12`, `color.neutral.50` to `950`, `text.sm`, `text.md`.
- Avoid names that encode a theme ("lightGray"). Avoid abbreviations nobody knows.

### Implementation
- Store source tokens in one place (JSON following the W3C Design Tokens format, or a tokens file). Generate CSS variables, Tailwind theme, and native constants from it (Style Dictionary or similar).
- Web: CSS custom properties at `:root`, theme overrides on `[data-theme="dark"]`. Components reference semantic variables only.
- Tailwind: map the theme to token variables so utilities use system values. Disallow arbitrary values in lint where practical.
- React Native: export tokens as a typed theme object and a `useTheme` hook.
- Theming and brand variants swap the semantic layer. Components do not change.
- Tokens are versioned. Renaming or removing one is a breaking change.

### Anti-drift rules
- A raw hex, pixel or font stack inside a component is a defect. Lint for it.
- If a needed value is missing, add a token with a reason, do not inline.
- Audit for near-duplicates (#333 and #343434) and consolidate.

## 3. Component design

### Anatomy checklist (every component spec)
- Purpose and when to use or not use
- Anatomy (named parts)
- Variants (visual styles) and sizes
- States (see file 02): default, hover, focus, pressed, disabled, loading, error, selected
- Content rules (min and max lengths, truncation, icon use)
- Behavior and interaction (keyboard map, touch, motion)
- Accessibility (role, name, focus, announcements)
- Responsive behavior
- Tokens used
- Do and don't examples
- Related components

### API principles
- Few, orthogonal props: `variant`, `size`, `tone` or `intent`, `disabled`, `loading`. Avoid boolean explosion (`isPrimary`, `isSecondary`). Use enums.
- Composition over configuration: slots and children (for example `Card.Header`, `Card.Body`) beat many props.
- Controlled and uncontrolled modes for stateful components. Sensible defaults.
- Forward refs, spread remaining props, merge class names, allow `asChild` or `as` for polymorphism where it helps.
- Accessibility built in: the component refuses (types or warnings) to render without required labels.
- Defaults produce the correct, most common, accessible result.
- Stable, minimal public surface. Hide internals.

### Core component set for most products
Button, IconButton, Link, Input, Textarea, Select, Combobox, Checkbox, Radio, Switch, Slider, DatePicker, FileUpload, Form field wrapper (label, help, error), Card, Badge or Tag, Avatar, Tooltip, Popover, Dropdown menu, Dialog, Drawer or Sheet, Toast, Alert or Banner, Tabs, Accordion, Breadcrumb, Pagination, Table, Skeleton, Spinner, Progress, Empty state, Stepper, Navigation (top, side, bottom), Command palette.

### Reuse rules
1. Use an existing component as is.
2. Use it with a variant or composition.
3. Extend it and contribute the extension back to the system.
4. Only then build a new component, with a spec, and add it to the system.

Component libraries that pair well with token-driven web projects: headless primitives (Radix UI, React Aria, Headless UI) for accessibility and behavior, with the project's own styling on top (shadcn/ui pattern). Choose based on the project's stack and license. Document the choice in the design context.

## 4. Theming and dark mode

- Semantic tokens map to different primitives per theme. Components never branch on theme.
- Provide at least light and dark when the audience will use the product in varied lighting. Respect system preference with manual override.
- Avoid flash of wrong theme: set the theme attribute before first paint.
- Test elevation, borders, shadows, charts and images in each theme.

## 5. Documentation and discoverability

- A living catalog (Storybook or similar) with every component, variants, states, props and a11y notes.
- Usage guidance written for the dev and the designer: when to use, when not to, content guidelines.
- Searchable, versioned, with changelog and migration notes.
- Show real examples and copy-paste code.

## 6. Governance

- Ownership: someone is accountable for the system, even in a team of one.
- Contribution flow: propose, spec, review (design and accessibility and engineering), build, document, release.
- Versioning: semantic versioning for the package, deprecation warnings before removal, codemods for breaking changes.
- Metrics: component adoption, token coverage, number of detached or custom styles, accessibility defects per release, time to build a screen.
- Retire duplicates. Resist one-off variants.

## 7. Design to code handoff

- One source of truth: tokens and components in code are the truth. Design files mirror them.
- Spec what code cannot infer: states, behavior, responsive rules, motion, content limits, edge cases, accessibility.
- Use design tool features that map to code: auto layout to flex and grid, variables to tokens, component properties to props.
- Review implemented UI against the spec in a real browser and device, not only against static frames.
- Keep a visual regression suite (Chromatic, Playwright snapshots) for critical components and pages.

## 8. Starting a system in a new project (lean path)

For small teams and early products:
1. Define tokens: color roles, type scale, spacing scale, radius, shadow, motion.
2. Install or build the small core set (Button, Input, Field, Card, Dialog, Toast, Tabs, Table, Badge).
3. Document them in a short guide inside the repo (`docs/design/`).
4. Add lint rules to prevent raw values.
5. Grow the system only when a pattern is needed twice.

## 9. Definition of done for token and component work

- [ ] Uses semantic tokens only, no raw values
- [ ] All states implemented and visible in the catalog
- [ ] Keyboard, focus, ARIA and screen reader behavior verified
- [ ] Works in all themes and at all supported breakpoints
- [ ] Props typed and documented, defaults are safe and accessible
- [ ] Usage guidance and examples written
- [ ] Visual regression snapshot added
- [ ] Added to the design context component list
