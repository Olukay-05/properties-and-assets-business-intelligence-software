# Screen Spec: UJ-3 Mobile Work Order Execution (`Maintenance/WorkOrderDetail.tsx`)

Status: ready for dev
Owner: UX designer agent (Sally)
Related story or epic: UJ-3 (FR-13, FR-14, FR-15, FR-16, FR-20, FR-21, AD-11, AD-12)
Design context version or date: 2026-11-03 (`docs/design/design-context.md`)
Interactive HTML Prototype: [mobile-work-order-execution.html](../mockups/mobile-work-order-execution.html)

## 1. Purpose
- User goal: Review an assigned maintenance work order on a mobile phone browser, log honest multi-visit partial progress (`In progress` or `On hold` with work performed, outstanding work, and delay reason) when a job cannot be finished in one visit, and complete the work order with mandatory timestamped photo evidence (never blocked if indoor GPS is unavailable).
- Context of use (who, where, device, connectivity): Technician (`Tunde Adeyemi`) or Contractor inside an indoor generator room or plant room on a mid-range Android or iOS phone (`320px` to `390px` viewport) where GPS signal is often blocked by concrete walls and mobile data is intermittent.
- Business goal and success metric: 100% of completed work orders carry verified timestamped photo evidence reflected in the target asset's timeline (`FR-15`, `FR-16`), and 0% false closures on multi-visit jobs awaiting spare parts (`FR-14`).
- Entry points:
  1. Tapping `My work orders` in `mobile-quick-task-bar` and selecting `WO-2026-0142` (`/work-orders/[uuid]`).
  2. Tapping `View WO` from a linked inspection checklist item on `Inspections/InspectionRunner.tsx`.
  3. Clicking a work order row on `Maintenance/WorkOrdersIndex.tsx` or `Registry/AssetDetail.tsx`.
- Exit points and next screens:
  1. Saving partial progress keeps `WO-2026-0142` in `On hold` or `In progress` and prepends the event to `work_order_events` `timeline-feed`.
  2. Tapping `Complete work order` transitions status to `Completed`, locks all evidence attachments, and links directly to `Registry/AssetDetail.tsx` (`LVE-GEN-01` timeline).

## 2. Layout and hierarchy
- Primary action:
  - In Visit 1 (Partial progress / On hold): `Save partial progress & place on hold` (`.cc-button--primary`).
  - In Visit 2 (Final completion): `Complete work order` (`.cc-button--teal`, one primary completion action).
- Secondary actions: `Capture or select replacement photo` (`.cc-button--secondary`), `View asset timeline` (`.cc-button--secondary`).
- Reading order and focal point:
  1. `.cc-skip-link` and compact mobile top bar (`Client: Lagoon View Estates Ltd` + `40px x 40px` Menu button).
  2. Work order `page-header` showing `WO-2026-0142`, status badge (`On hold` or `In progress`), target Property (`LVE-VI-01`), Zone (`Generator House`), and Asset (`LVE-GEN-01`).
  3. Originating Fault Context card linking back to `INSP-2026-089 · Q5` with Amara's frayed-belt photo thumbnail (10-min signed R2 URL).
  4. Execution Form:
     - Mode A: Multi-visit `Record partial progress / place on hold` form (`Status transition` Tier-1 `.cc-select`, `Work performed`, `Outstanding work`, `Delay / hold reason`).
     - Mode B: `Complete work order with mandatory evidence` form (`evidence-uploader` with non-blocking `GPS unavailable · recorded without coordinates` notice, final resolution summary, and optional supervisor sign-off note).
  5. Append-only `Work order events & asset history` `timeline-feed` and bottom `mobile-quick-task-bar`.

| Breakpoint | Layout notes |
|---|---|
| 320 to 767 | Single-column mobile field layout (`32px 22px` padding, `16px` font size on all inputs/selects/textareas, `40px` min touch target height, `scroll-padding-bottom: 72px`). |
| 768 to 1023 | Centered `720px` single-column dossier with top `app-shell-header`. |
| 1024 and up | Centered `1120px` `.cc-page` container; prototype displays side-by-side comparison of Multi-Visit Partial Progress (`On hold`) vs. Evidence-Gated Completion (`Completed`), plus interactive UX State Switcher. |

## 3. Components
| Region | Component | Variant and size | Tokens or notes |
|---|---|---|---|
| Skip navigation | `.cc-skip-link` | First focusable element | `--cc-navy-2` bg, `z-index: 100` |
| Dossier header | `page-header` | Mobile H1 `24px` + rule | `{components.page-header.*}`, status badge + assignee meta |
| Fault origin | `editorial-card` + `evidence-gallery` | Quiet white card | Links to `INSP-2026-089` + signed R2 fault thumbnail |
| Progress fields | `form-field` + `select-native` | `16px` mobile font, `#6E7E91` border | `work_performed`, `outstanding_work`, `delay_reason` |
| Mandatory photo gate | `evidence-uploader` | `role="progressbar"` + GPS fallback | `{components.evidence-uploader.*}`, never blocks on missing GPS |
| Policy / Gate callout | `recommendation-callout` | Max 1 per view | `{components.recommendation-callout.*}` (`#EAF3F4`) |
| Event log | `timeline-feed` | Descending append-only | `{components.timeline-feed.*}` (`work_order_events`) |
| Mobile bottom nav | `mobile-quick-task-bar` | 4 cells, `40px` min target | `{components.mobile-quick-task-bar.*}` |

New components or tokens needed (with reason): None beyond canonical 21 components and `--cc-border-control: #6E7E91`.

## 4. State matrix
| State | Trigger | What the user sees | Copy IDs |
|---|---|---|---|
| Default (Visit 1 & Visit 2) | Assigned technician `Tunde Adeyemi` opens `WO-2026-0142` | Full fault context, partial progress (`On hold`) form, mandatory completion photo uploader (showing non-blocking `GPS unavailable` notice), and `work_order_events` timeline | `uj3.header.title`, `uj3.gps.fallback`, `uj3.cta.complete` |
| Loading first load | Initial route load on mobile | Header preserved; pulsing `#F4F7FA` skeleton blocks for fault card, form fields, and 3 timeline events | `uj3.loading.skeleton` |
| Loading refresh / Saving | Tapping `Complete work order` | Button sets `aria-busy="true"`, label swaps to `Completing work order…` | `uj3.cta.completing` |
| Empty events | Newly created work order with only initial creation event | Timeline displays single initial `Created & assigned` node with clear next step | `uj3.empty.events` |
| Error domain (`work_order_missing_completion_photo`) | Technician taps `Complete work order` with 0 attached completion photos | Top 3-part `.cc-status--danger` alert strip (`role="alert"`) + `evidence-uploader` border turns `2px solid #A34545` + focus moves to `Capture or select photo` button; all form notes preserved | `uj3.err.missing_photo` |
| Error recoverable (422) | Selecting `On hold` without entering `delay_reason` | Inline `.cc-error` below `Delay / hold reason` field (`aria-invalid="true"`) explaining why a delay reason is required | `uj3.err.delay_reason` |
| Partial / Interrupted upload | Completion photo upload interrupted at 50% | `evidence-uploader` retains compressed `312 KB` WebP blob in memory and shows one-tap `Retry upload` bar | `uj3.upload.interrupted` |
| Offline | Signal lost in generator room | Polite status banner (`Offline: draft notes preserved on screen. Reconnect to upload completion photo and close work order.`) | `uj3.offline.banner` |
| GPS unavailable | Indoor generator house blocks browser geolocation (`4s` timeout or permission denied) | Polite `.cc-status--neutral` strip (`GPS unavailable · recorded without coordinates`) inside `evidence-uploader`; `Complete work order` remains fully enabled | `uj3.gps.fallback` |
| Permission denied (`403` wrong assignee) | Technician `Musa Danjuma` (not assigned to `WO-2026-0142`) opens the work order | Completion and progress buttons disabled with explicit policy banner: `Only the assigned technician (Tunde Adeyemi) or a site supervisor may complete this work order.` | `uj3.perm.wrong_assignee` |
| Success / Completed Immutable | Work order transitions to `Completed` | Status badge changes to `.cc-status--success` (`Completed`), photo delete actions removed, event prepended to `work_order_events` and `LVE-GEN-01` asset timeline | `uj3.completed.banner` |
| Extreme data | 15+ multi-visit progress events or long technical part numbers | `timeline-feed` paginates cleanly; part numbers and timestamps wrap without horizontal page overflow | `uj3.timeline.header` |

## 5. Interaction and behavior
- Click, tap and keyboard behavior per control:
  - Tier-1 `.cc-select` (`Status transition`) invokes native Android bottom sheet or iOS wheel picker.
  - `Capture or select photo` button triggers `<input type="file" accept="image/jpeg,image/png,image/webp" capture="environment">`, compresses image client-side to `<=1920px` WebP, and updates `role="progressbar"`.
- Validation rules and timing:
  - `Record partial progress / place on hold` requires `work_performed` and (when `On hold`) `delay_reason`; does **not** require a photo.
  - `Complete work order` requires at least 1 uploaded completion photo (`captured_at` timestamp mandatory; GPS coordinates optional per `FR-15` & `AD-11`).
- Focus management: If `Complete work order` fails due to missing photo, focus moves directly to the `role="alert"` summary and `evidence-uploader` trigger button.
- Motion: `150ms ease` transitions; disabled under `prefers-reduced-motion: reduce`.
- Data saved and when: Every progress or completion submission executes inside `WorkOrderService::transition()` with `lockForUpdate()` (`AD-21`) and atomic `AuditService::record()` (`AD-18`).

## 6. Copy deck
| ID | Location | State | Text | Notes |
|---|---|---|---|---|
| `uj3.header.kicker` | `page-header` kicker | Default | `WORK ORDER EXECUTION · WO-2026-0142 · HIGH PRIORITY` | Uppercase Georgia `#126E6A` |
| `uj3.header.title` | `page-header` H1 | Default | `Replace frayed alternator drive belt` | Arial 24px bold `#17365D` |
| `uj3.cta.hold` | Visit 1 form | Default | `Save partial progress & place on hold` | `.cc-button--primary` |
| `uj3.cta.complete` | Visit 2 form | Default | `Complete work order` | `.cc-button--teal` (sole primary completion CTA) |
| `uj3.gps.fallback` | `evidence-uploader` | GPS unavailable | `GPS coordinates unavailable on this device. Photo and timestamp (03 Nov 2026, 16:45 WAT) are recorded. You may complete the work order without location data.` | `role="status" aria-live="polite"` |
| `uj3.err.missing_photo` | Top of completion form | Error (`work_order_missing_completion_photo`) | `Completion photo required (work_order_missing_completion_photo). A work order cannot be marked Completed without visual proof of the finished repair. Attach at least one timestamped photo below, or use Save partial progress if work is still ongoing.` | 3-part error (`role="alert"`) |
| `uj3.perm.wrong_assignee` | Top of form | Permission denied (403) | `Action restricted (work_order_unauthorised_assignee). Only the assigned technician (Tunde Adeyemi) or a site supervisor may record progress or complete this work order.` | `.cc-status--warning` banner |
| `uj3.completed.banner` | Top of dossier | Completed immutable | `Work order WO-2026-0142 completed on 03 Nov 2026, 16:46 WAT by Tunde Adeyemi. Evidence attachments are locked and reflected on Asset LVE-GEN-01.` | `.cc-callout` (`#EAF3F4`) |

## 7. Accessibility notes
- Heading outline and landmarks: `.cc-skip-link` -> `<header>` -> `<main id="main-content">` -> `<h1>` (`Replace frayed alternator drive belt`) -> `<h2>` (`Originating fault & target asset`, `Record partial progress`, `Complete work order with mandatory evidence`, `Work order event history`).
- Live regions: Non-blocking GPS fallback and upload progress announce via `role="status" aria-live="polite"`; missing-photo completion gate announces via `role="alert"`.
- Contrast & touch targets: `#6E7E91` (`3.95:1`) on all form inputs/textareas/selects; `40px` min height on all buttons and inputs; `16px` mobile font size.

## 8. Performance and offline
- Client-side WebP compression reduces camera uploads from `~6 MB` to `~312 KB` before transmission.
- Compressed blob and typed notes stay in memory if signal drops inside a concrete plant room.

## 9. Analytics
| Event | Trigger | Properties |
|---|---|---|
| `work_order.progress_recorded` | Partial progress save | `work_order_id`, `new_status` (`in_progress` \| `on_hold`) |
| `work_order.completed` | Completion commit | `work_order_id`, `has_gps`, `photo_count` |
| `work_order.completion_blocked_missing_photo` | Missing photo validation | `work_order_id` |

## 10. Acceptance criteria
1. Built from this screen spec; all states (`Multi-Visit On Hold`, `Evidence-Gated Completion`, `Missing Photo Error Gate`, `GPS Unavailable Fallback`, `Interrupted Upload Retry`, `Wrong Assignee 403`, and `Completed Immutable`) are implemented and reachable.
2. Technicians can log partial progress (`work_performed`, `outstanding_work`, `delay_reason`) and transition to `in_progress` or `on_hold` without a photo (`FR-14`).
3. Transitioning to `completed` requires at least 1 photo with `captured_at` timestamp, never blocks when GPS is unavailable (`location: null`), and is rejected if attempted by a different unassigned technician (`FR-15`, `AD-11`, `AD-12`).
4. Upon completion, the work order and its photo evidence appear in the target Asset's dynamic `timeline-feed` (`FR-16`, `AD-9`), and attachment delete controls are removed.

## 11. Open questions and assumptions
| Item | Type (question, assumption) | Owner | Status |
|---|---|---|---|
| `ASSUMPTION-UJ3-1`: Browser geolocation timeout set to `4000ms` before falling back to `location: null` | Assumption (`FR-15`, `FR-21`) | UX / Eng | Confirmed in `EXPERIENCE.md` |

