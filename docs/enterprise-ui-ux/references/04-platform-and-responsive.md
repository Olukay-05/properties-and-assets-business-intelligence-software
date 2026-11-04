# 04 Platform and Responsive

## 1. Mobile first, content first

- Design the smallest viewport first (320 to 375px wide). It forces priority decisions. Enhance upward.
- Decide content priority once, then let layout adapt around it. Do not hide important content on small screens, reorder or collapse it.
- Test on real mid-range devices, not only on flagship phones and simulators.

## 2. Breakpoints and layout behavior

Use content-driven breakpoints, with these common defaults:

| Name | Min width | Typical layout |
|---|---|---|
| xs | 0 | Single column, bottom or hamburger nav, full width cards |
| sm | 480 | Single column with wider margins |
| md | 768 | Two columns possible, side panel collapses, tab bar to sidebar rail |
| lg | 1024 | Full sidebar, multi-column content, side panels inline |
| xl | 1280 | Wider content, extra panels, density options |
| 2xl | 1536 | Max width container, avoid stretching lines |

Techniques:
- Fluid widths with max-widths. Use `min()`, `max()`, `clamp()`.
- **Container queries** so components adapt to their container, not the viewport.
- CSS grid with `auto-fit` and `minmax()` for card grids. Flexbox for one-dimensional flows.
- Use logical properties (`margin-inline`, `padding-block`) to support RTL languages if needed.
- Avoid fixed heights. Avoid horizontal scrolling except for tables, code, maps and carousels that need it.
- `dvh` and `svh` units for mobile full height layouts. Handle on-screen keyboard resize for forms.
- Prevent layout shift with aspect ratios and reserved space.

## 3. Touch ergonomics

- Minimum touch target 44 by 44 pt (iOS) and 48 by 48 dp (Android), with at least 8px between adjacent targets.
- Thumb zones: primary actions in the lower and middle of the screen on phones. Avoid top corners for frequent actions. Bottom sheets and bottom navigation suit one-handed use.
- Provide pressed states instantly. Avoid 300ms tap delay (use `touch-action: manipulation` and a proper viewport meta).
- Avoid hover dependent UI. Use tap or long-press with alternatives.
- Swipe, pull to refresh, and long-press are accelerators with visible alternatives.
- Keep inputs at 16px or more to avoid zoom on iOS. Use `inputmode`, `type` and `autocomplete`.
- Respect safe areas (notches, home indicators) with `env(safe-area-inset-*)`.

## 4. Navigation by platform

| Pattern | Use when | Notes |
|---|---|---|
| Bottom tab bar | 3 to 5 top-level destinations on mobile | Persistent, labeled icons, active state, no hamburger duplication |
| Hamburger or drawer | Many secondary destinations | Poor discoverability for primary nav. Use alongside, not instead of, tabs for key items |
| Top app bar | Page title, back, contextual actions | Back behavior consistent and predictable |
| Sidebar | Desktop apps with many sections | Collapsible, with icons and labels, remembers state |
| Command palette | Power users, large apps | Cmd or Ctrl K, searches actions and content |
| Breadcrumbs | Deep hierarchies on desktop | Not a replacement for primary nav |
| Stepper | Linear flows | Shows progress and allows going back |

Back behavior must work: system back, browser back, and in-app back all do the expected thing. Deep links open the right place with correct back stack.

## 5. iOS (Human Interface Guidelines) essentials

- Principles: clarity, deference (content first), depth (layers and motion communicate hierarchy).
- Use native components when building native: navigation bar with large titles, tab bar at bottom, sheets (detents), context menus, swipe actions, SF Symbols, SF Pro, Dynamic Type.
- Support Dynamic Type, Dark Mode, VoiceOver, Reduce Motion, Bold Text and Larger Accessibility Sizes.
- Edge swipe goes back. Do not conflict with it.
- Permissions: ask in context with a custom pre-prompt first.
- Haptics: sparing, matched to meaning (success, warning, selection).
- Respect safe areas, keyboard avoidance and Sign in with Apple requirements where third-party sign-in is offered.

## 6. Android (Material Design 3) essentials

- Principles: adaptive layouts, expressive and personal (dynamic color), accessible by default.
- Components: top app bar, navigation bar (bottom), navigation rail (tablets and foldables), FAB for the primary action, bottom sheets, snackbars, chips, cards, dialogs.
- Use window size classes (compact, medium, expanded) to adapt layouts. Support foldables, tablets, multi-window.
- State layers (hover, focus, pressed, dragged) communicate interaction. Use elevation and tonal surfaces.
- Support predictive back gesture, edge to edge display, TalkBack, font scaling, dark theme.
- Material color roles (primary, on-primary, container, surface, outline) map well to semantic tokens.

## 7. Web application conventions

- Use real links, real buttons and standard form behavior. Do not break back, forward, refresh, open in new tab, copy URL.
- State in URL: filters, tabs, selected item, pagination.
- Progressive enhancement: core content and functions work before JavaScript hydrates where feasible.
- Handle slow networks with skeletons, streaming, pagination and lazy loading.
- Prevent double submit. Idempotent actions where possible.
- Browser features: autofill, password managers, passkeys, share sheets (`navigator.share`), clipboard, install prompts.

## 8. Desktop patterns

- Keyboard shortcuts for frequent actions, shown in tooltips and menus. Do not collide with browser or OS shortcuts.
- Multi-pane layouts (list and detail), resizable panels, remembered layout, drag and drop with alternatives.
- Right-click context menus as accelerators only.
- Density options for data-heavy workflows. Large monitors need max widths and multi-column use, not stretched lines.
- Hover enhancements (tooltips, row actions) always with focus and touch equivalents.

## 9. Responsive tables and data

Choose a pattern per use case:
- **Scroll with sticky first column** for dense comparison data.
- **Stacked cards** per row on mobile for record-like data. Preserve labels.
- **Priority columns** show critical columns and move others into an expandable row or detail view.
- **Pivot to summary plus detail** for very wide data.
Never shrink text below readable sizes to fit columns.

## 10. Images, media, performance on devices

- Responsive images (`srcset`, `sizes`), AVIF and WebP, lazy loading, explicit width and height.
- Video: no autoplay with sound, poster images, adaptive streaming, captions.
- Reduce JavaScript on mobile. Split bundles by route. Avoid heavy client libraries for simple UI.
- Respect `prefers-reduced-data` where available and the Save-Data header.

## 11. PWA and offline

- Installable PWA when repeat use justifies it: manifest, icons (including maskable), theme color, splash.
- Service worker strategies: cache-first for static assets, network-first with fallback for data, stale-while-revalidate for lists.
- Design offline states explicitly: banner, queued actions with visible status, conflict handling on reconnect, last-synced time.
- Background sync for form submissions in unreliable networks.
- Provide an offline page that is useful, not a blank error.

## 12. Cross-platform consistency

- Share the same tokens, terminology and mental model across platforms. Let each platform keep its own navigation, gestures, controls and typography conventions.
- Feature parity is about outcomes, not identical pixels.
- Document platform differences in the design context.

## 13. Responsive QA checklist

- [ ] Checked at 320, 375, 768, 1024, 1440 and one ultra-wide width
- [ ] Portrait and landscape on phones and tablets
- [ ] Touch targets meet minimums, spacing adequate
- [ ] No horizontal scroll except intentional
- [ ] Keyboard (on-screen) does not hide inputs or primary actions
- [ ] Safe areas respected
- [ ] Back, refresh and deep link behavior correct
- [ ] Slow network and offline behavior verified on a throttled device
- [ ] Text scaled to 200 percent without breakage
