# Design Review Rubric (REVIEW and AUDIT modes)

Use for reviewing specs, pull requests, built screens or whole products.

## How to score

Score each category from 0 to 3.

| Score | Meaning |
|---|---|
| 0 | Fails. Users are blocked, harmed or misled |
| 1 | Weak. Significant problems, fix before release |
| 2 | Acceptable. Minor issues, can ship with a fix list |
| 3 | Strong. Meets the standard, no meaningful issues |

Weights: categories marked with an asterisk are gates. A 0 or 1 in a gate category fails the review regardless of total.

## Categories

| # | Category | What to check | Reference |
|---|---|---|---|
| 1* | Accessibility | Keyboard, focus, semantics, names, contrast, forms, reflow, motion | 03 |
| 2* | Task success and flow | Goal achievable, minimum steps, clear primary action, recoverable errors | 02 |
| 3* | States and edge cases | All states present, extreme data handled, failure paths designed | 02 |
| 4 | Visual hierarchy and layout | Squint test, grouping, alignment, spacing rhythm, density | 01 |
| 5 | Consistency and system use | Tokens, components, patterns, terminology | 05 |
| 6 | Content and copy | Clear, concise, specific, error quality, empty states, locale formats | 06 |
| 7 | Responsive and platform fit | Breakpoints, touch targets, conventions, offline | 04 |
| 8 | Performance and perceived speed | Budgets, layout stability, loading patterns | 02, 08 |
| 9 | Trust, privacy and ethics | No dark patterns, honest data use, security cues with substance | 02, 09 |
| 10 | Context fit | Matches the project design context and the target users' realities | design context |

Total out of 30. Interpretation: 27 to 30 ship, 22 to 26 ship with listed fixes, 15 to 21 rework required, under 15 redesign.

## Severity for findings

- **Blocker:** prevents task completion, causes data loss, violates accessibility at A or AA level for core flows, or is unsafe or deceptive. Must fix before release.
- **Major:** serious friction, high error risk, or a clear system violation. Fix in this release.
- **Minor:** low impact or low frequency. Schedule.
- **Polish:** refinement and consistency tweaks.

## Finding format

```
[Severity] Short title
Where: screen, component, or file and line
Principle: reference file and section (for example 03 Forms, 02 State design)
Observed: what happens, with evidence (screenshot, steps, measurement)
Impact: who is affected and how
Fix: the specific change recommended
```

## Review procedure

1. Read the design context and the spec or story.
2. Walk the primary flow as the target user on the target device, using keyboard and touch.
3. Walk error, empty, loading and offline paths on purpose (throttle the network, break requests, use no data and huge data).
4. Run the automated accessibility scan, then a keyboard pass and a screen reader spot check.
5. Inspect tokens and component use in code or inspector.
6. Score the categories, list findings, lead with the top three impact issues.
7. State a verdict: Approve, Approve with fixes, or Changes required. Gate categories decide.

## Audit additions (whole product or flow)

- Inventory screens and patterns, count one-off components and duplicate styles.
- Map the main journeys and measure steps, drop-off and time on task where data exists.
- Sample the top 10 screens by traffic for accessibility and performance.
- Produce a prioritized backlog grouped by theme with effort estimates and a recommended sequence.
