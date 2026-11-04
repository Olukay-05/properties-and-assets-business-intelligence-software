# 07 Data-Heavy and Common Product Patterns

Patterns that recur in enterprise and SME products. Each gives the rule, then the traps.

## 1. Data tables

**Structure**
- Left align text, right align numbers and currency (with tabular numerals), center only short status or icons. Header alignment matches column alignment.
- Sticky header, and a sticky first column for wide tables. Zebra stripes or row dividers, not both heavy.
- Row height: 48px comfortable, 36 to 40px compact. Offer a density toggle for power users.
- Column priority: identifier first, then status, then key attributes, then dates, then row actions on the far right.
- Truncate long text with a tooltip or expandable row, and keep the full value accessible.

**Behavior**
- Sorting: click header, show direction, one active sort by default. Stable sort.
- Filtering: filter bar above, active filters as chips, result count visible, clear all.
- Pagination for large sets and when users need position and shareable URLs (page size selector 10, 25, 50, 100). Infinite scroll only for feeds, not for records users must return to or act on.
- Selection: checkbox column, header checkbox for page and "select all N results" offer, a bulk action bar appearing on selection with a count.
- Row actions: primary action is clicking the row, secondary actions in a kebab or inline buttons visible on hover and focus, never hover only.
- Inline editing: clear affordance, Enter saves, Escape cancels, error shown in place.
- Column management: show or hide columns, reorder, persist per user.
- Export respects current filters, states the format and size.

**States:** loading skeleton rows, empty first use, empty after filter, partial error, row-level error and saving state, and offline.

**Responsive:** see file 04 section 9.

## 2. Dashboards and analytics

- Start from the questions the user must answer and the decisions they make, not from available data.
- Layout: inverted pyramid. Headline KPIs at the top, trends and breakdowns in the middle, detail tables at the bottom. Align on a grid and keep cards consistent.
- KPI tiles: big number, label, comparison to a baseline (period over period with direction and color plus icon), small trend sparkline. State the time range on the page.
- One message per chart. Title states the insight or the metric, not "Chart 1".
- Global controls (date range, segment) at the top and visibly applied to everything. Show last updated time.
- Limit to about 5 to 9 widgets above the fold. More invites scanning without understanding.
- Make everything drill-downable to the underlying records, and make numbers on dashboards match the numbers in detail views exactly.
- Empty and low-data states explain what is needed to populate and avoid misleading charts with one data point.
- Real-time or auto-refresh must not move things under the cursor. Show a "new data" indicator instead of jumping layouts.

## 3. Charts

Choose the form by the question:

| Question | Chart |
|---|---|
| Change over time | Line (continuous), column (discrete periods) |
| Compare categories | Horizontal bar (long labels), column (few categories), sorted by value |
| Part of a whole | Stacked bar or a simple donut with 2 to 5 parts. Avoid pie with many slices |
| Distribution | Histogram, box plot |
| Relationship | Scatter plot |
| Progress to goal | Bullet chart, progress bar |
| Geographic | Map, only if location matters |
| Exact values | Table |

Rules:
- Start bar axes at zero. Do not truncate to exaggerate.
- Direct label lines and bars where possible. Minimize legends.
- Limit color: neutral for context, one accent for the focus. Categorical palettes max 6 to 8 colors that remain distinguishable for color vision deficiency. Add shape, pattern or labels as a second cue.
- Remove chartjunk: heavy gridlines, 3D, shadows, decorative backgrounds.
- Annotate key events and thresholds.
- Provide a table or text summary alternative and keyboard accessible tooltips.
- Format axis values with units and locale (for example ₦ and compact notation).
- Never mislead: consistent scales when comparing, state sample sizes and periods.

## 4. Forms, advanced

- **Multi-step wizard:** use when the form is long or has branches. Show step names and progress, validate each step before moving on, allow back without losing data, save drafts, end with a review screen and a clear final action, then a success state with next steps.
- **Conditional fields:** reveal fields directly below the triggering control, without shifting focus, and announce changes for screen readers.
- **Repeating groups:** "Add another" with clear remove actions and confirmation if data would be lost.
- **Autosave:** show "Saving…" and "Saved at 10:42" states, handle conflicts.
- **Inline editing vs edit mode:** inline for single values, edit mode with Save and Cancel for grouped changes. Warn on navigating away with unsaved changes.
- **Address and identity inputs:** support local address realities (landmarks, LGAs, states), offer NIPOST digital postcode lookup if the product uses it, and accept flexible formats for phone, BVN or NIN where legally required. Explain why sensitive IDs are needed and how they are protected.
- **Payment inputs:** card number with automatic spacing and brand detection, numeric keypad, clear inline errors, secure cues, alternative methods (bank transfer, USSD) visible without scrolling.

## 5. Authentication and account flows

- Sign in: one clear path, email or phone plus password or passwordless, show password toggle, allow paste and password managers, "Forgot password" visible, preserve where the user was going.
- Sign up: ask the minimum. Defer profile details until needed. Offer social login only where it adds value, and apply the platform's required rules.
- Passwordless and OTP: auto-focus the code field, support one-time-code autofill, resend with a countdown, accept pasted codes, explain delivery delays (SMS can be slow).
- Errors must not reveal whether an account exists when security requires ambiguity, but must still help real users recover.
- Sessions: warn before timeout and save work. Allow device and session management.
- Account recovery needs an alternative route that does not depend on one fragile channel.
- Security settings: clear explanations, step-up verification for sensitive actions.

## 6. Search, filters and facets

See file 02 section 12. Additional rules for data-heavy products:
- Saved views (filter plus sort plus columns) for repeated work, shareable by link.
- Operators for power users (is, is not, contains, greater than) with a simple default.
- Show facet counts and hide or disable impossible values.
- Global search groups results by type with keyboard navigation.

## 7. Settings and admin

- Organize by user mental model: account, notifications, security, billing, team, integrations.
- Changes apply immediately with feedback (toggles) or through an explicit Save for grouped changes. Be consistent per page.
- Show the effect of a setting in its description. Provide defaults and "Reset to default".
- Danger zone at the bottom, visually separated, with strong confirmation.

## 8. Roles, permissions (RBAC) UX

- Hide what a role can never use, show but disable what it could use with an explanation and a request-access path.
- Role and permission management: show effective permissions, who has access, and what changed (audit trail).
- Make impersonation, bulk grants and revocations explicit and reversible.
- Use plain role names that map to jobs.

## 9. Notifications center and activity feeds

- Group and prioritize. Read and unread states, mark all as read, filter by type.
- Link each item to the exact object. Keep a retention period visible.
- Respect user preferences. Do not make the badge count lie.

## 10. File upload, import and export

- Show accepted types and size limits before upload. Support drag and drop and a visible browse button, camera on mobile where useful.
- Progress per file, cancel, retry, and resilient chunked upload on weak networks. Compress images client-side when appropriate.
- Import: template download, column mapping step, validation preview with row-level errors, partial success option, undo after import.
- Export: choose format and scope, run long exports in the background with a notification.

## 11. Commerce and checkout

- Guest checkout. Ask only for what is needed to fulfill and pay.
- Show the full cost (delivery, fees, taxes) before the final step. No surprises.
- Cart is editable at all steps. Persist carts across sessions and devices.
- Payment options reflect local use: card, bank transfer, USSD, wallets. Explain pending states for bank transfer clearly and handle webhooks and retries.
- Trust signals with substance: clear return policy, contact options including WhatsApp where customers expect it, verifiable business details.
- Order confirmation includes number, summary, expected timing, and how to get help. Send it by the channels the customer uses.
- Product listing: consistent image ratios, price visible, stock and delivery information, filters that match how people shop, fast image loading on mobile data.

## 12. Pricing and plan pages

- Compare plans on outcomes, then features. Highlight the recommended plan. Show billing period and currency clearly, show annual savings honestly.
- Explain limits and overages in plain words. Make cancellation as easy as sign up.

## 13. Notifications of long-running or background tasks

- Start: confirm the task started and where to see it. Progress: show status and ability to leave the page. Complete: notify through the right channel with a direct link. Fail: explain and offer retry.

## 14. Pattern QA checklist

- [ ] Table has sorting, filtering, selection, row actions and all states defined
- [ ] Dashboard answers named questions, numbers match drill-downs, time range visible
- [ ] Charts chosen for the question, scales honest, accessible alternative present
- [ ] Forms validated at the right time, input preserved, autosave or drafts where long
- [ ] Auth flows tested with password managers, paste, OTP delays
- [ ] Permissions explained, not just hidden
- [ ] Uploads and imports resilient on weak networks with clear errors
- [ ] Checkout shows total cost early and supports local payment methods
