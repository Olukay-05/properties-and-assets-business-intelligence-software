---
name: enterprise-ui-ux
description: Enterprise grade UI and UX design principles for product work. Use whenever an agent specifies, designs, builds, reviews or audits any user interface, screen, flow, component, form, dashboard, design token, copy, or accessibility behavior. Covers visual foundations, interaction and cognitive principles, WCAG 2.2 AA, platform conventions, design systems and tokens, UX writing, data heavy UI, AI product patterns, and low bandwidth mobile first contexts. Built for BMAD UX designer and dev agents. Trigger on UI, UX, design, screen, layout, component, form, dashboard, onboarding, empty state, error state, accessibility, responsive, design system, tokens, wireframe, prototype, handoff, design review.
---

# Enterprise UI/UX Skill

A working standard that mirrors how senior product designers at large product companies decide, specify and review. It is a decision system, not a style guide. Every rule has a reason, and the reason is what you apply when a situation is not listed.

## When this skill applies

Load it for any task that creates or changes something a human will see or touch: new screens, flows, components, copy, tokens, layout fixes, accessibility fixes, dashboards, forms, emails with UI, and design reviews. Skip it for pure backend, infra or data work with no user facing surface.

## Step 0: Read the project design context

Before doing anything else:

1. Look for `docs/design/design-context.md` in the project.
2. If it exists, read it fully. Its decisions (brand, tokens, platforms, audience, constraints) override the generic defaults in this skill, except the non-negotiables below.
3. If it does not exist, create it from `templates/design-context.md`, fill what you can infer from the repo (framework, existing tokens, component library, brand colors), and ask the user only for what you cannot infer. Do not invent brand decisions.
4. Never contradict an existing design system in the repo. Extend it. If it has a gap, propose an addition and record it in the design context.

## Modes

Pick the mode from the task. Say which mode you are in at the start of your output.

| Mode | Typical agent | Goal | Load first |
|---|---|---|---|
| SPEC | UX designer | Produce screen and flow specs a dev can build without guessing | references 02, 01, 03, 06, plus 07 or 08 as relevant, then `checklists/ux-spec-checklist.md` |
| BUILD | Dev | Implement UI faithfully, accessibly and with tokens, not hardcoded values | references 05, 03, 04, then `checklists/dev-implementation-dod.md` |
| REVIEW | Either | Judge existing or proposed UI against the standard and report fixes | `checklists/design-review-rubric.md`, then the references the findings touch |
| AUDIT | Either | Whole product or whole flow sweep with a prioritized issue list | rubric plus references 02, 03, 05, 09 |

If a task spans modes (for example "build this screen"), run SPEC thinking first (states, flows, copy, a11y) and then BUILD. Do not write JSX or CSS before the states and copy are decided.

## Reference map (load on demand, not all at once)

| File | Read when |
|---|---|
| `references/01-visual-foundations.md` | Layout, hierarchy, type, color, spacing, elevation, motion, iconography |
| `references/02-interaction-and-ux.md` | Flows, IA, heuristics, cognitive laws, states, feedback, forms logic, onboarding |
| `references/03-accessibility.md` | Any interactive element, color choice, focus, keyboard, ARIA, testing |
| `references/04-platform-and-responsive.md` | Mobile, tablet, desktop, touch, breakpoints, iOS and Android conventions, PWA |
| `references/05-design-systems-and-tokens.md` | Tokens, component APIs, theming, dark mode, governance, handoff to code |
| `references/06-ux-writing.md` | Any label, button, error, empty state, confirmation, notification, email copy |
| `references/07-data-ui-patterns.md` | Tables, dashboards, charts, filters, search, settings, auth, checkout, uploads, RBAC |
| `references/08-ai-and-emerging-market-context.md` | AI features, low bandwidth, Android first, Nigerian market realities |
| `references/09-process-research-metrics.md` | Discovery, testing, critique, metrics, prioritization, design debt |

## Non-negotiables

These hold in every project, in every mode.

1. **Accessibility is a requirement, not a polish pass.** WCAG 2.2 AA is the floor. Contrast, keyboard operability, visible focus, semantic structure and labels ship with the first version of any element.
2. **Every screen has all its states.** Default, loading, empty, error, partial, success, offline, disabled, and permission denied where relevant. A spec or implementation missing states is incomplete.
3. **Tokens, never raw values.** Color, spacing, radius, type, shadow, z-index and motion come from tokens. A hex code or pixel literal in component code is a defect unless it defines a token.
4. **Clarity beats cleverness.** If a user must learn it, explain it, or guess it, redesign it. Familiar patterns win over novel ones unless novelty solves a measured problem.
5. **One primary action per view.** Hierarchy must make the next step obvious within a few seconds of looking.
6. **Respect the user's time, data and attention.** No dark patterns, no forced signups before value, no blocking modals for non-critical content, no layout shift, no needless data transfer.
7. **Prevent errors first, then recover gracefully.** Constrain inputs, confirm destructive actions with specifics or offer undo, and write errors that say what happened, why, and what to do.
8. **Mobile first and touch safe.** Design for the smallest, slowest, least capable context first, then enhance. Touch targets meet minimum size.
9. **Consistency is a feature.** Same thing looks and behaves the same everywhere. Reuse before creating. New patterns need a written reason.
10. **Content is design.** Real copy, real data shapes and real edge cases (long names, zero items, thousands of items, other languages) are specified, not "lorem ipsum".
11. **Performance is UX.** Perceived speed, stable layout and sensible loading behavior are part of the design spec.
12. **Decisions are written down.** Rationale goes into the spec or design context so the next agent or human does not relitigate it.

## Priority order when principles conflict

1. Safety, legal and accessibility
2. Task success and data integrity
3. Clarity and learnability
4. Consistency with the existing system
5. Efficiency for expert and repeat users
6. Visual polish and brand expression
7. Delight

When you pick a lower priority over a higher one, say so and why.

## Output contracts

**SPEC mode** produces, per screen or flow: purpose and user goal, entry and exit points, layout and hierarchy description, component list mapped to the design system, full state matrix, copy deck with real strings, interaction and keyboard behavior, responsive behavior at each breakpoint, accessibility notes, analytics events if the project tracks them, and open questions. Use `templates/screen-spec.md`.

**BUILD mode** produces working code that uses project tokens and components, handles every state in the spec, passes the dev definition of done, and includes a short note listing any deviation from the spec with a reason.

**REVIEW and AUDIT modes** produce findings as: severity (Blocker, Major, Minor, Polish), location, principle violated (cite the reference file and section), observed behavior, recommended fix. Lead with the three highest impact issues. Score with the rubric.

## BMAD integration

Map to BMAD phases as follows.

- **Product brief and PRD:** UX agent contributes user goals, success metrics and UX requirements as testable statements (for example "form errors are announced to screen readers and focus moves to the first invalid field").
- **UX design document:** UX agent runs SPEC mode, writes or updates `docs/design/design-context.md`, and produces screen specs. Flag spec gaps instead of guessing.
- **Architecture:** UX agent states frontend constraints (performance budgets, offline needs, theming strategy). Dev agent confirms token and component strategy.
- **Story creation:** Every story that touches UI links to its screen spec and includes UI acceptance criteria drawn from `checklists/dev-implementation-dod.md` (states, a11y, responsive, tokens, copy).
- **Dev implementation:** Dev agent runs BUILD mode and self-checks against the definition of done before marking a story ready for review.
- **Review and QA:** Reviewer runs REVIEW mode with the rubric. Blockers and Majors fail the review.

If the project's story template has an acceptance criteria section, append UI criteria there. Do not create a parallel tracking system.

## Working method

1. Restate the user goal and the context of use (who, where, device, connectivity, urgency).
2. Reuse existing patterns and components. Search the repo first.
3. Decide structure before style: information hierarchy, flow, states.
4. Apply references for the specifics.
5. Check against the relevant checklist.
6. Report what you did, what you assumed, and what needs a human decision.

## Anti-patterns to reject on sight

- Placeholder text used as the only label
- Icon-only buttons without accessible names and tooltips
- Color as the only signal for status
- Modals stacked on modals, or modals for content that should be a page
- Infinite scroll with a footer the user needs
- Carousels hiding primary content
- Tiny text under 12px for anything meaningful, or body text under 16px on mobile web
- Disabled buttons with no explanation of what is missing
- Full page spinners where skeletons or partial content would work
- Auto-playing media with sound, auto-advancing content users cannot pause
- Custom scrollbars, custom select or date controls that break native behavior without need
- Confirm dialogs that say only "Are you sure?" with "OK" and "Cancel"
- Hover-only functionality with no touch or keyboard equivalent
- Hardcoded colors, magic spacing numbers, inline z-index wars
- Copy that blames the user ("You entered an invalid value")
- Dark patterns: confirmshaming, hidden costs, pre-checked upsells, roach motel cancellation

## Style rules for anything this skill writes

Write plainly. No filler, no buzzwords, no em dashes or en dashes used as punctuation. Prefer short sentences, concrete nouns and specific numbers.
