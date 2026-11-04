# 03 Accessibility (WCAG 2.2 AA as the floor)

Accessibility is built in from the first version. Retrofitting costs several times more. Roughly one in six people live with a disability, and many more have temporary or situational limits (a broken arm, bright sunlight, a noisy room, a cracked screen, a slow network).

## 1. The four principles (POUR)

- **Perceivable:** users can sense the content (text alternatives, contrast, captions, adaptable layout).
- **Operable:** users can operate the interface (keyboard, enough time, no seizure triggers, navigable, input modalities).
- **Understandable:** content and behavior are predictable and readable (language, consistent navigation, input assistance).
- **Robust:** works with current and future assistive technology (valid semantics, names and roles).

## 2. Requirements to meet by default

### Color and contrast
- Text contrast at least 4.5:1, large text (24px regular or 18.66px bold and above) at least 3:1. Check placeholder, disabled-adjacent helper text, text over images and gradients, and text in dark mode.
- Non-text contrast at least 3:1 for boundaries needed to identify controls (input borders, icons that carry meaning, chart marks, focus indicators).
- Never use color alone to convey meaning. Add icon, text, pattern or position.
- Links inside body text need a non-color cue (underline) or 3:1 contrast with surrounding text plus a second cue on hover and focus.

### Keyboard and focus
- Every function works with a keyboard alone. Tab order follows visual and logical order. No keyboard traps.
- Focus is always visible with a strong indicator (at least 2px outline, 3:1 contrast against adjacent colors, offset from the element). Never remove outlines without a better replacement. Use `:focus-visible`.
- **Focus not obscured (2.2):** sticky headers, cookie banners and chat widgets must not fully hide the focused element. Use `scroll-padding` and layout offsets.
- Manage focus on change: moving into a dialog, closing it returns focus to the trigger, route changes move focus to the main heading or a skip target, after deleting an item focus goes to a logical neighbor.
- Provide a "Skip to main content" link as the first focusable element.
- Do not use positive `tabindex` values. Use `tabindex="0"` only for custom widgets and `-1` for programmatic focus.
- Offer visible, discoverable keyboard shortcuts for expert tools, never single character shortcuts without a way to turn off or remap.

### Target size and input (WCAG 2.2)
- Pointer targets at least 24 by 24 CSS pixels (AA minimum, 2.5.8), with spacing exceptions. Design to 44 by 44 for touch (iOS) and 48 by 48 dp (Android) wherever possible.
- **Dragging alternative (2.5.7):** anything done by dragging (reorder, slider, map pan) must have a single-pointer alternative (buttons, tap targets, inputs).
- Do not require path-based or multi-finger gestures without an alternative. Support pointer cancellation (act on release, not press) for destructive actions.
- **Redundant entry (3.3.7):** do not make users re-enter information they already gave in the same flow. Prefill or let them select.
- **Accessible authentication (3.3.8):** no cognitive function tests (memorizing, transcribing, puzzles) as the only way to log in. Allow paste, password managers, passkeys, email magic links.
- **Consistent help (3.2.6):** help links or contact options appear in the same place across pages.

### Structure and semantics
- Use native HTML elements first (`button`, `a`, `input`, `select`, `dialog`, `details`, `nav`, `main`, `header`, `footer`, `ul`, `table`). They bring keyboard behavior, roles and states for free.
- Logical heading hierarchy (one `h1`, no skipped levels) and landmarks (`header`, `nav`, `main`, `aside`, `footer`, labeled `section`).
- Links navigate, buttons act. Do not use a div with a click handler.
- Language set on `html`, and on inline text in another language (`lang="yo"`).
- Unique, descriptive page titles that update on route changes in single page apps.
- Lists, tables, and forms use proper markup. Data tables have headers with `scope`. Do not use tables for layout.

### Forms
- Every input has a programmatically associated visible label (`label for` or wrapping). Use `aria-label` only when no visible label is possible.
- Group related controls with `fieldset` and `legend` (radio groups, address blocks).
- Required and optional state is conveyed in text. Use `required` plus clear text. Use `autocomplete` tokens for personal data (WCAG 1.3.5).
- Errors: identified in text (3.3.1), associated with the field via `aria-describedby`, `aria-invalid="true"` on the field, suggestions provided (3.3.3), and announced (summary receives focus or uses `role="alert"`).
- Help text is associated via `aria-describedby`, not placeholder.
- Do not rely on placeholder as a label, and do not use time limits without warning and extension.

### Content and media
- Meaningful images have concise alt text describing purpose, not appearance. Decorative images use `alt=""`. Complex images (charts) get a text summary and data table.
- Icons that are buttons need an accessible name (visually hidden text or `aria-label`). Decorative icons are `aria-hidden="true"`.
- Captions and transcripts for video and audio. No autoplay with sound. Provide pause, stop, hide for moving or auto-updating content over 5 seconds.
- No content flashing more than three times per second.
- Link text makes sense out of context ("Download invoice", not "Click here").

### Reflow, zoom, spacing
- Content reflows at 320 CSS px width and 400 percent zoom with no two-dimensional scrolling (except data tables, maps, code).
- Supports text resize to 200 percent without loss of content or function.
- Does not break when users override text spacing (line height 1.5, paragraph spacing 2x, letter spacing 0.12em, word spacing 0.16em). Avoid fixed heights on text containers.
- Support both portrait and landscape.
- Do not disable pinch zoom (`user-scalable=no`, `maximum-scale=1` are failures).

### Motion and time
- Honor `prefers-reduced-motion`. Honor `prefers-contrast` and `forced-colors` (Windows High Contrast) where possible. Avoid reliance on background images for meaning in forced colors.
- Time limits: warn, allow extension (at least 20 seconds to respond, at least ten times extension), or avoid limits.
- Auto-updating content can be paused. Session timeouts save work.

## 3. ARIA rules

1. Do not use ARIA if a native element does the job.
2. Do not change native semantics unless you must.
3. All interactive ARIA controls must be keyboard operable.
4. Do not use `role="presentation"` or `aria-hidden="true"` on focusable elements.
5. All interactive elements need an accessible name.

Common attributes: `aria-label`, `aria-labelledby`, `aria-describedby`, `aria-expanded`, `aria-controls`, `aria-current`, `aria-selected`, `aria-pressed`, `aria-live`, `aria-busy`, `aria-invalid`, `aria-modal`.

Live regions: `role="status"` (polite) for saved confirmations and non-urgent updates, `role="alert"` (assertive) for errors needing attention. The live region must exist in the DOM before its content changes.

## 4. Component patterns

| Component | Key requirements |
|---|---|
| Button | Native `button`. Name describes action. States: focus, disabled (use `disabled` or `aria-disabled` with explanation), loading (`aria-busy`, keep label, announce) |
| Link | Native `a` with `href`. Opens in new tab only when needed, and say so |
| Modal dialog | Native `dialog` or `role="dialog"` with `aria-modal="true"` and an accessible name. Trap focus inside, close with Escape, return focus to the trigger, lock background scroll, make background inert |
| Drawer and popover | Same as dialog if modal. Non-modal popovers close on Escape and outside click, keep focus order logical |
| Menu button and dropdown | Button with `aria-expanded` and `aria-haspopup`. Arrow keys move, Home and End jump, Escape closes, type-ahead. Do not use `role="menu"` for navigation links, use a list of links |
| Tabs | `tablist`, `tab`, `tabpanel`. Arrow keys move between tabs, Tab moves into the panel. `aria-selected` and `aria-controls` |
| Accordion | Button in a heading controlling a region, `aria-expanded` |
| Combobox and autocomplete | `role="combobox"`, `aria-expanded`, `aria-controls`, `aria-activedescendant`. Announce result counts. Escape closes |
| Select | Prefer native `select`. Custom selects are costly to make accessible |
| Checkbox and radio | Native inputs. Radios in `fieldset` with `legend`. Large click area including label |
| Switch | `role="switch"` or checkbox with `aria-checked`. Label states what it controls, not the state |
| Tooltip | Appears on hover and focus, dismissible (Escape), hoverable, persistent until dismissed, not the only place for essential info |
| Toast | `role="status"` for info, `role="alert"` for errors. Long enough to read, pausable, keyboard reachable if it has actions |
| Table | `table` with `caption`, `th scope`. Sortable headers expose `aria-sort`. Avoid nested tables. Responsive patterns must keep semantics |
| Pagination | `nav` labeled "Pagination", `aria-current="page"` on current |
| Breadcrumb | `nav` labeled "Breadcrumb", ordered list, `aria-current="page"` |
| Progress | `progress` or `role="progressbar"` with value and label. Announce stage changes in multi-step flows |
| Date picker | Provide typed input alternative with clear format. Calendar grid supports arrow keys and page keys |
| File upload | Native `input type="file"` as the base. Drag and drop is an enhancement. Announce upload status and errors |
| Carousel | Avoid. If used, provide pause, previous, next, and do not autoplay |
| Charts | Text summary, data table alternative, patterns or labels as well as color, keyboard-focusable data points where interaction exists |

## 5. Cognitive and situational accessibility

- Plain language, short sentences, one idea per sentence, defined acronyms.
- Predictable navigation and behavior. Do not change context on focus or input without warning.
- Reduce memory load: show formats and examples, retain entered data, show progress, offer recovery.
- Allow enough time. Avoid timeouts. Save progress.
- Provide clear errors and a path forward.
- Support users on bright sun, low battery, small screens and poor connections (contrast margin, large targets, lightweight pages).
- Consider users with low literacy or limited English: icons plus text, simple words, avoid idioms, provide localization.

## 6. Testing protocol

Run on every feature before review.

1. **Automated:** axe (browser extension or CI), Lighthouse accessibility, linting (`eslint-plugin-jsx-a11y`). Automation catches roughly a third of issues.
2. **Keyboard pass:** unplug the mouse. Tab through the page. Everything reachable, order logical, focus visible, no traps, Escape and Enter and Space behave.
3. **Screen reader pass:** at least one of NVDA with Firefox or Chrome, VoiceOver with Safari, TalkBack on Android. Check names, roles, states, reading order, live announcements, form errors.
4. **Zoom and reflow:** 200 percent and 400 percent browser zoom, 320px width, increased text spacing.
5. **Contrast:** check every text and component color pair in every theme.
6. **Motion and preferences:** reduced motion, high contrast, forced colors, dark mode.
7. **Touch:** target sizes, spacing, gestures on a real device.
8. **Content:** headings outline, alt text, link text, language, page titles.

## 7. Definition of done for accessibility

- [ ] All interactive elements reachable and operable by keyboard with visible focus
- [ ] Semantic HTML, landmarks, headings hierarchy correct
- [ ] Every control has an accessible name, role and state
- [ ] Contrast meets AA in all themes
- [ ] Color is never the only cue
- [ ] Forms: labels, errors identified, associated, announced, input preserved
- [ ] Focus managed on dialogs, route changes and dynamic content
- [ ] Target sizes meet minimums, dragging has an alternative
- [ ] Reflows at 320px, supports 200 percent text, no zoom blocking
- [ ] Reduced motion respected, no flashing, no autoplay with sound
- [ ] Automated scan clean, manual keyboard and screen reader checks done and recorded
