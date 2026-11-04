# Project Design Context

Owner: UX designer agent (updates), all agents (read). Location in project: `docs/design/design-context.md`.
This file overrides the generic defaults of the enterprise-ui-ux skill, except its non-negotiables.

## 1. Product snapshot
- Product name:
- One sentence purpose:
- Primary users and their context of use (device, location, connectivity, urgency):
- Secondary users (admins, reviewers, support):
- Success metrics (task success, conversion, time on task, retention):

## 2. Platforms and tech
- Platforms: web / iOS / Android / desktop (mark which are in scope)
- Framework and UI libraries (example: Next.js, Tailwind, shadcn/ui, React Native):
- Component library status: none / partial / mature
- Theming: light only / light and dark / user brandable
- Languages and locales (example: en-NG, plus Pidgin later):
- Currency, date and number formats:

## 3. Brand and visual direction
- Personality in five adjectives:
- Anti-personality (what we must not feel like):
- Logo and brand assets location:
- Brand colors (hex, with roles):
- Type families and fallbacks:
- Imagery and illustration style:
- Reference products that feel right, and what specifically we borrow:

## 4. Design tokens
Source of truth file (example: `src/styles/tokens.css`, `tailwind.config.ts`):

| Category | Decision |
|---|---|
| Color scales | |
| Semantic colors (bg, surface, text, border, accent, success, warning, danger, info) | |
| Type scale and line heights | |
| Spacing scale (base unit) | |
| Radius | |
| Elevation | |
| Motion durations and easing | |
| Breakpoints | |
| Z-index layers | |

## 5. Components in use
List core components, where they live, and their status (stable, in progress, missing).

## 6. Accessibility target
- Standard: WCAG 2.2 AA (default)
- Extra commitments (example: AAA contrast for body text, screen reader tested on TalkBack):
- Assistive tech test matrix:

## 7. Performance and network budgets
- Target devices (example: mid-range Android, 3 GB RAM):
- Network assumptions (example: 4G with frequent drops, data cost sensitive):
- Budgets: LCP, INP, CLS, initial JS (gzipped), image weight per page:
- Offline needs:

## 8. Content and voice
- Voice and tone rules:
- Terminology list (preferred words, banned words):
- Legal copy that must appear:

## 9. Constraints and decisions log
Record each decision with date, decision, reason, and who made it. Newest first.

| Date | Decision | Reason | By |
|---|---|---|---|

## 10. Open questions
