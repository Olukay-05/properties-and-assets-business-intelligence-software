# 02 Interaction and UX

## 1. Nielsen's ten usability heuristics, applied

| # | Heuristic | What it means in practice |
|---|---|---|
| 1 | Visibility of system status | Show progress, saving state, sync state and results. Respond to every action within 100ms (visual change), show progress for waits over 1s, and use determinate progress when duration is known |
| 2 | Match between system and real world | Use the user's words and mental models, not internal terms. Order things the way users think about them |
| 3 | User control and freedom | Provide undo, cancel, back, and clear exits. Prefer undo over confirmation dialogs |
| 4 | Consistency and standards | Follow platform conventions and your own system. Same label, same place, same behavior |
| 5 | Error prevention | Constrain inputs, default sensibly, disable impossible actions with an explanation, confirm destructive actions with specifics |
| 6 | Recognition over recall | Show options, recents, suggestions and context instead of requiring memory |
| 7 | Flexibility and efficiency | Shortcuts, bulk actions, saved filters, defaults for experts without burdening novices |
| 8 | Aesthetic and minimalist design | Every extra element competes with the important ones. Remove before adding |
| 9 | Help users recognize, diagnose and recover from errors | Plain language, state the cause, offer the fix, keep user input |
| 10 | Help and documentation | Contextual help, empty-state guidance, searchable docs, but design so help is rarely needed |

## 2. Laws and principles that predict behavior

Use these as design checks, not as rigid rules.

| Principle | Statement | Apply it by |
|---|---|---|
| Fitts's law | Time to hit a target grows with distance and shrinks with size | Make primary targets large and close to where the pointer or thumb already is. Put destructive actions away from frequent ones |
| Hick's law | Decision time grows with the number and complexity of choices | Cut options, group, use progressive disclosure, highlight a recommended default |
| Miller's law (caution) | People hold a limited number of items in working memory | Chunk information (phone numbers, card numbers, steps). Do not use "7 plus or minus 2" as a menu size rule |
| Jakob's law | Users spend most time on other products and expect yours to work the same | Use familiar patterns. Innovate only where it solves a measured problem |
| Doherty threshold | Productivity soars when response is under about 400ms | Optimistic updates, skeletons, instant local feedback |
| Tesler's law | Complexity cannot be removed, only moved | Decide whether the system or the user carries it. Prefer the system |
| Postel's law | Be liberal in what you accept, conservative in what you send | Accept spaces and dashes in phone and card numbers, then normalize |
| Aesthetic-usability effect | Attractive interfaces are perceived as easier to use | Visual polish buys patience, but never hides usability defects |
| Von Restorff effect | The different item is remembered | Make the primary action or key data distinct, and only one of them |
| Serial position effect | First and last items are remembered best | Put key items at the start and end of lists and navigation |
| Peak-end rule | People judge experiences by the peak and the end | Invest in the success moment and the final step |
| Zeigarnik effect | Unfinished tasks stay in mind | Use progress indicators and saved drafts to bring users back |
| Goal-gradient effect | Effort rises as the goal nears | Show progress toward completion, start progress bars slightly filled |
| Law of common region and proximity | Grouped things are perceived as related | See file 01 |
| Parkinson's law | Tasks expand to fill time | Set sensible time and length limits, use defaults |
| Pareto principle | Roughly 80 percent of use comes from 20 percent of features | Put the common 20 percent up front |
| Cognitive load theory | Intrinsic, extraneous and germane load add up | Remove extraneous load (noise, inconsistency), chunk intrinsic load, support germane load (learning) |
| Choice overload | Too many equal options causes paralysis | Offer curated defaults and sorting |

## 3. Information architecture

- Start from user tasks and mental models. Card sorting (open then closed) and tree testing validate structure.
- Navigation models: top nav (few sections, content sites), sidebar (many sections, apps), bottom tab bar (3 to 5 destinations on mobile), hub and spoke, nested doc, search-led. Pick by the number of top-level destinations and how often users switch.
- Keep navigation persistent and in the same place. Show current location (active state, breadcrumbs for deep hierarchies, page titles that match nav labels).
- Depth: aim for 3 clicks of effort to important content in practice, but clarity matters more than click count. A few clear clicks beat one confusing one.
- Labels: single clear nouns or verbs from user language. Avoid internal jargon, clever names and overlapping labels.
- Provide search when the content set is large or users know what they want. Search needs tolerant matching, recent searches, suggestions and a helpful no-results state.
- URLs reflect structure and state (filters, tabs, pagination) so pages can be shared, bookmarked and restored with the back button.

## 4. User flows

For every flow, specify:
1. Trigger and entry points (more than one is normal)
2. User goal and success definition
3. Happy path steps (keep to the minimum number)
4. Alternate paths (skip, edit, go back)
5. Error and edge paths (validation, network failure, timeouts, conflicts, permission denied, duplicates, expired sessions)
6. Exit points and where the user lands next
7. Data saved at each step, and what happens on interruption

Principles:
- Ask for information only when needed (just in time) and never ask twice for the same thing.
- Reveal complexity progressively. Start with the most common choice, offer "advanced" for the rest.
- Make progress visible in multi-step flows (step count, saved state) and allow going back without data loss.
- Provide a sensible default for every choice that has a common answer.
- Let users do the valuable thing before demanding an account when the product allows it.

## 5. State design (mandatory)

Every component and screen defines these states. A state matrix is part of every spec.

**Component states:** default, hover, focus (keyboard), pressed or active, selected, disabled, read-only, loading, error, success, indeterminate.

**Data and screen states:**
| State | Design guidance |
|---|---|
| Loading (first load) | Skeletons that match final layout, no spinner-only pages, no layout shift |
| Loading (refresh) | Keep existing content visible, show subtle progress |
| Empty (first use) | Explain value, give one clear action, optionally show sample data |
| Empty (no results) | Say what was searched or filtered, offer to clear filters, suggest alternatives |
| Empty (cleared or done) | Positive confirmation (inbox zero), no pressure |
| Partial | Show what loaded, mark what did not, allow retry per section |
| Error (recoverable) | Say what happened, keep user data, offer retry and an alternate path |
| Error (fatal) | Clear message, a way out, a reference code for support |
| Offline | Show status, queue actions where possible, show last sync time |
| Permission denied | Explain what is needed and how to request access |
| Success | Confirm what happened and what comes next. Keep it proportionate to the action |
| Stale or conflict | Explain the conflict and let the user pick or merge |
| Long content | Define truncation, wrapping, and "show more" behavior |
| Extreme data | One item, many items (thousands), very long text, missing optional fields, other scripts |

## 6. Feedback and system status

- Instant feedback for direct manipulation (under 100ms). Optimistic UI for low-risk actions, with rollback on failure and a clear message.
- Waits: under 1s no indicator needed, 1 to 10s show indeterminate indicator, over 10s show determinate progress with estimate and let users continue elsewhere.
- Saving: auto-save with visible "Saved" status for long forms and documents. Do not rely on a single Save button users may forget.
- Toasts: for low-importance confirmations. Auto-dismiss only for non-critical info (5 to 8 seconds, pausable), never for errors that need action. Never put the only copy of important information in a toast.
- Banners for persistent system-level notices. Inline messages for field and section level feedback. Dialogs only for decisions that block progress.
- Notifications hierarchy: critical (blocking), important (needs action), informational (FYI). Give users control over channels and frequency.

## 7. Forms

Forms are where products succeed or fail. See file 07 for complex form patterns.

- Single column layout. Group related fields. Order fields as users think.
- Labels above fields, always visible. Placeholders show examples only and never replace labels.
- Mark optional fields as "(optional)" instead of marking required ones with asterisks when most are required. Be consistent.
- Use correct input types and attributes: `type`, `inputmode`, `autocomplete`, `enterkeyhint`. Show the right keyboard on mobile.
- Size fields to expected input length. Do not split phone, date or card inputs into many tiny boxes unless there is a strong reason.
- Validation timing: validate on blur for format, on submit for completeness, and live only for help (password strength, character count, username availability). Never show an error before the user has had a chance to type.
- Errors: next to the field, in text, with an icon, and in a summary at the top for long forms. Move focus to the first error on submit. Preserve all user input.
- Do not clear a form on error. Do not make users retype.
- Buttons: a primary submit with a specific label ("Create account", "Send invoice"). A disabled submit is a last resort. Prefer enabled with clear errors on press.
- Support paste, password managers, autofill, and one-time-code autofill. Do not block paste.
- Reduce effort: defaults, remember choices, address lookup, input masks that accept flexible entry, inline format hints.
- Long forms: break into steps with a clear progress indicator, save drafts, allow review before submit.
- Confirm on high-stakes submissions with a review step that summarizes entries.

## 8. Onboarding and first run

- Time to first value is the key metric. Get users to a meaningful result quickly.
- Prefer learning by doing, contextual tips and checklists over long product tours. Tours that users must click through are skipped.
- Ask only what you need to personalize. Allow "skip for now" for everything not essential.
- Sample data or templates solve the blank-page problem.
- Persistent but dismissible setup checklist for multi-step activation. Show progress.
- Re-engage with contextual prompts when users reach relevant features, not all at once on day one.

## 9. Destructive and irreversible actions

Preference order:
1. **Undo:** perform the action, show an undo toast for 8 to 10 seconds, finalize afterwards. Best for most deletions.
2. **Soft delete:** trash with restore period.
3. **Confirm with specifics:** dialog names the object and the consequence ("Delete invoice INV-0042? This cannot be undone."). The confirm button repeats the action ("Delete invoice"), never "OK". Make the destructive button visually distinct (danger style) and not the default focus.
4. **Typed confirmation:** for catastrophic actions (delete project, delete account). Ask the user to type the resource name.

Separate destructive actions spatially from common ones. Never put "Delete" next to "Save".

## 10. Affordance, signifiers and discoverability

- Interactive elements must look interactive (buttons look pressable, links look like links, inputs look editable). Do not make plain text clickable without a signifier.
- Hover is not available on touch. Never hide required functionality behind hover. Provide an always-visible or tap alternative.
- Gestures (swipe, long-press, pinch) are shortcuts only. Provide visible alternatives. Teach gestures with first-use hints.
- Show selection state, current step, current page, and loading targets clearly.

## 11. Microinteractions

Structure: trigger, rules, feedback, loops and modes. Good microinteractions confirm, reassure, or prevent errors (a toggle that animates, a copy button that says "Copied", a like that bounces). Keep them under 300ms, make them skippable, and never delay the primary task for them.

## 12. Search, filter, sort

- Search box visible and wide enough, with clear and recent searches. Handle typos and synonyms. Highlight matches.
- Filters: show active filters as removable chips, show result counts, show a single "Clear all". Do not apply filters that produce zero results without warning, or disable impossible facet values.
- Sorting: sensible default (relevance or newest), visible current sort, stable ordering.
- Preserve search, filter and sort state in the URL and when navigating back.

## 13. Trust, privacy and ethics

- Ask for permissions (notifications, location, camera, contacts) at the moment of need, with a pre-prompt explaining why. Never at launch.
- Be honest about data use. Make consent granular and reversible. Make opting out as easy as opting in.
- Forbidden dark patterns: confirmshaming, forced continuity, hidden costs, pre-ticked upsells, roach motel, misdirection, fake urgency, nagging, disguised ads, friend spam.
- Show security cues where users make trust decisions (payment, identity, sensitive data) with real substance, not decoration.
- Handle sensitive states with care: failed payments, health data, bereavement, financial hardship.

## 14. Perceived performance

- Skeletons and progressive rendering beat spinners.
- Preload likely next steps. Prefetch on intent (hover, touch start).
- Keep layout stable: reserve space for images, ads, async content.
- Optimistic updates, background sync, and local drafts.
- Defer non-critical work. Prioritize content above the fold.
- Do not fake speed with deceptive progress bars.

## 15. Spec-ready questions to answer for any screen

1. What is the user trying to do, and what happens right before and after?
2. What is the one primary action?
3. What do they need to see to decide?
4. What can go wrong, and what do they see then?
5. What does it look like with zero, one and a thousand items?
6. What happens on a slow or lost connection?
7. How does it work with keyboard only and with a screen reader?
8. What does it look like at 320px wide and at 1440px wide?
9. What is the exact copy for every state?
10. What do we measure to know it works?
