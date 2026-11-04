# Screen Spec: UJ-2 Mobile Field Inspection Runner (`Inspections/InspectionRunner.tsx`)

Status: ready for dev
Owner: UX designer agent (Sally)
Related story or epic: UJ-2 (FR-9, FR-10, FR-11, FR-12, FR-13, FR-21, AD-10, NFR-5.2)
Design context version or date: 2026-11-03 (`docs/design/design-context.md`)
Interactive HTML Prototype: [mobile-inspection-runner.html](../mockups/mobile-inspection-runner.html)

## 1. Purpose
- User goal: Execute a scheduled site inspection checklist on a mobile phone browser, attach compressed photo evidence, update asset condition, raise an inline maintenance request/work order when a fault is found, and sign off to lock the inspection (or create a linked corrected version if an approved record needs amendment).
- Context of use (who, where, device, connectivity): Site Supervisor (`Amara Bello`) walking a generator house or plant room with a mid-range Android or iOS phone (`320px` to `390px` viewport) over fluctuating Nigerian 3G/4G mobile data.
- Business goal and success metric: Complete a 25-item mobile inspection checklist with photo evidence and inline fault-to-work-order conversion in under 8 minutes without losing draft responses during signal drops.
- Entry points:
  1. Tapping `My inspections` in `mobile-quick-task-bar` and selecting a scheduled or in-progress inspection (`/inspections/[uuid]`).
  2. Clicking an inspection row on `Inspections/InspectionsIndex.tsx` or `Registry/PropertyDetail.tsx`.
- Exit points and next screens:
  1. Tapping `Approve & sign off inspection` locks the record in `Immutable` state (`approved_at` set).
  2. Tapping `View WO` on a raised fault navigates to `Maintenance/WorkOrderDetail.tsx` (`WO-2026-0142`).
  3. Tapping `Create corrected version` on an approved inspection creates a new draft inspection with `supersedes_id` linked to the original record.

## 2. Layout and hierarchy
- Primary action:
  - In active draft state: `Approve & sign off inspection` (`.cc-button--teal`, one primary completion action at the bottom of the runner).
  - Inside the inline fault drawer: `Create & assign work order (WO-2026-0142)` (`.cc-button--primary`).
  - In immutable locked state: `Create corrected version (supersedes_id)` (`.cc-button--secondary`).
- Secondary actions: `Save draft & exit` (`.cc-button--secondary`), `Capture or select additional photo` (`.cc-button--secondary`).
- Reading order and focal point:
  1. `.cc-skip-link` and compact mobile top bar (`Client: Lagoon View Estates Ltd` + `40px x 40px` Menu trigger).
  2. Mobile inspection `page-header` showing template version (`v2`), property/zone, progress counter (`5 of 5 answered`), and live draft sync badge (`All 5 responses saved`).
  3. `checklist-runner` section header (`Section A: 500kVA Generator Room`) followed by numbered question cards with `40px`-high segmented `Pass` / `Fail` / `N/A` radio buttons (`role="radiogroup"`).
  4. On failing Question 5 (`Alternator drive belt tension & wear`): inline `evidence-uploader` (with WebP compression progressbar and interrupted-upload retry bar) + inline `Raise maintenance request` drawer (`modal-dialog` contract).
  5. Supervisor sign-off footer card and persistent bottom `mobile-quick-task-bar`.

| Breakpoint | Layout notes |
|---|---|
| 320 to 767 | Single-column mobile field layout (`32px 22px` page padding, `16px` body and input font size to prevent iOS Safari auto-zoom, `40px` min-height on all radio buttons, inputs, selects, and bottom nav cells). `scroll-padding-bottom: 72px` prevents `mobile-quick-task-bar` from obscuring focused controls. |
| 768 to 1023 | Centered `720px` single-column checklist container with top `app-shell-header`. |
| 1024 and up | Centered `1120px` `.cc-page` container; prototype displays side-by-side comparison of Active Checklist Runner vs. Immutable Approved Record (or single-view mode via the UX State Switcher). |

## 3. Components
| Region | Component | Variant and size | Tokens or notes |
|---|---|---|---|
| Skip navigation | `.cc-skip-link` | First focusable element | `--cc-navy-2` bg, `z-index: 100` |
| Mobile shell top | Compact header | `56px` height | Active Client Account eyebrow (`12px`) + `40px x 40px` Menu button |
| Inspection header | `page-header` | Mobile H1 `24px` + rule | `{components.page-header.*}` + pinned `Template v2` badge |
| Checklist items | `checklist-runner` | `role="radiogroup"`, `40px` radios | `{components.checklist-runner.*}`, live sync badge (`role="status"`) |
| Photo capture | `evidence-uploader` | Client-side `<=1920px` WebP | `{components.evidence-uploader.*}`, `role="progressbar"` + retry strip |
| Inline fault form | `modal-dialog` (inline drawer) | 1-level deep inside Q5 | Pre-filled fault description, Tier-1 priority `.cc-select`, Tier-2 Technician `.cc-dropdown` |
| Supersede notice | `recommendation-callout` | Max 1 per view | `{components.recommendation-callout.*}` (`#EAF3F4`) on locked record |
| Mobile bottom nav | `mobile-quick-task-bar` | 4 cells, `56px` bar / `40px` targets | `{components.mobile-quick-task-bar.*}` |

New components or tokens needed (with reason): None beyond canonical 21 components and `--cc-border-control: #6E7E91`.

## 4. State matrix
| State | Trigger | What the user sees | Copy IDs |
|---|---|---|---|
| Default (Active Runner) | Supervisor opens scheduled/in-progress inspection `INSP-2026-089` | Interactive `checklist-runner` with Q1 to Q4 `Pass`, Q5 `Fail` highlighting frayed alternator belt, compressed photo uploaded, and inline `Raise maintenance request` drawer open | `uj2.header.title`, `uj2.sync.saved`, `uj2.q5.prompt` |
| Loading first load | Opening inspection route over 3G/4G | Header structure + 5 layout-matched pulsing skeleton question cards (`#F4F7FA`) | `uj2.loading.skeleton` |
| Loading refresh / Saving | Tapping `Approve & sign off inspection` | Button sets `aria-busy="true"`, `opacity: 0.46`, label changes to `Approving & locking inspection…` | `uj2.cta.approving` |
| Empty first use | Inspection template section has 0 questions (edge case) | Editorial empty notice explaining template configuration and linking Ops Manager to `TemplatesIndex` | `uj2.empty.template` |
| Error recoverable (422) | Supervisor taps `Approve & sign off inspection` while Question 3 is unanswered | Top `.cc-status--danger` alert strip (`role="alert"`) + Question 3 border turns `2px solid #A34545` + focus scrolls to Question 3 | `uj2.err.unanswered` |
| Error domain (`inspection_already_approved`) | Concurrent sign-off attempt on an already approved inspection | Top 3-part domain alert explaining the record was already locked and switching view to Immutable state | `uj2.err.already_approved` |
| Partial / Interrupted upload | Photo upload drops at 65% over weak MTN 4G signal | `evidence-uploader` shows `.cc-status--warning` retry strip (`Connection interrupted. Compressed photo (248 KB) saved on screen. Tap Retry upload.`) | `uj2.upload.interrupted` |
| Offline | Device loses mobile data during walkthrough | Header sync badge switches to `.cc-status--warning` (`Offline: 5 responses saved on device · tap to retry sync`); checklist buttons remain interactive | `uj2.sync.offline` |
| Success / Immutable Locked | Supervisor completes sign-off (`approved_at` set) | All radio groups and inputs replaced by static Georgia text; delete buttons hidden on photos; `.cc-callout` explains immutability; `Create corrected version (supersedes_id)` button visible | `uj2.locked.badge`, `uj2.locked.callout`, `uj2.locked.cta` |
| Disabled or permission denied | Authenticated as `Technician` (cannot approve inspections) | Checklist rendered read-only with notice `Read-only access: only the assigned Site Supervisor or Operations Manager may sign off this inspection.` | `uj2.perm.readonly` |
| Extreme data | 40-question template or 500-character supervisor finding note | Sticky section header + smooth scroll with `scroll-padding-bottom: 72px`; notes wrap cleanly without horizontal scroll | `uj2.section.header` |

## 5. Interaction and behavior
- Click, tap and keyboard behavior per control:
  - Each question's `Pass` / `Fail` / `N/A` segmented group (`role="radiogroup"`) supports tap, click, and `ArrowLeft`/`ArrowRight` + `Space` selection.
  - Selecting `Fail` on any question immediately highlights the row border (`#EBCFCF`), sets `aria-checked="true"`, and expands the inline `Raise maintenance request` drawer pre-populated with the Property, Asset, Inspection ID, and question prompt.
  - Tier-2 Technician picker (`.cc-dropdown`) supports full keyboard and touch selection.
- Validation rules and timing: Draft answers autosave on every radio tap or input blur. Completeness (all required questions answered) is validated when `Approve & sign off inspection` is pressed.
- Focus management: When `Approve & sign off inspection` is tapped, a confirmation `modal-dialog` traps focus (`Confirm inspection approval & lock`); on confirm, focus moves to the locked status heading.
- Motion: `150ms ease` on radio button state changes; disabled under `prefers-reduced-motion: reduce`.
- Data saved and when: Each checklist answer autosaves to draft state; final sign-off commits atomically via `InspectionService::approve()` and `AuditService::record()` (`AD-10`, `AD-18`).

## 6. Copy deck
| ID | Location | State | Text | Notes |
|---|---|---|---|---|
| `uj2.header.kicker` | `page-header` kicker | Default | `FIELD INSPECTION RUNNER · INSP-2026-089 · TEMPLATE V2` | Uppercase Georgia `#126E6A` |
| `uj2.header.title` | `page-header` H1 | Default | `Generator & plant room weekly inspection` | Arial 24px bold `#17365D` |
| `uj2.sync.saved` | Section header badge | Default | `All 5 responses saved` | `.cc-status--success` (`role="status"`) |
| `uj2.sync.offline` | Section header badge | Offline | `Offline: 5 responses saved locally · tap to retry sync` | `.cc-status--warning` (`role="status"`) |
| `uj2.q5.prompt` | Question 5 title | Default | `5. Alternator drive belt tension & wear` | Georgia 16px bold `#14243A` |
| `uj2.upload.interrupted` | `evidence-uploader` | Interrupted upload | `Connection interrupted. Your notes and compressed photo (248 KB) are still on this screen. Tap Retry upload when signal returns.` | 3-part recovery strip |
| `uj2.err.unanswered` | Top of checklist | Error 422 | `Cannot approve inspection yet (incomplete_checklist). Question 3 (Fuel line & primary water-separator integrity) has no recorded answer. Select Pass, Fail, or N/A to sign off.` | `role="alert"` |
| `uj2.cta.approve` | Sign-off card | Default | `Approve & sign off inspection` | `.cc-button--teal` (sole primary completion CTA) |
| `uj2.locked.badge` | Locked header | Immutable | `Immutable record · Approved 03 Nov 2026, 14:20 WAT` | `.cc-status--neutral` |
| `uj2.locked.callout` | Locked callout | Immutable | `Approved on 03 Nov 2026, 14:20 WAT by Amara Bello. This record is immutable. To amend a finding, create a corrected version linked to this inspection.` | `.cc-callout` (`#EAF3F4`) |
| `uj2.locked.cta` | Locked header | Immutable | `Create corrected version (supersedes_id)` | `.cc-button--secondary` |

## 7. Accessibility notes
- Heading outline and landmarks: `.cc-skip-link` -> `<header>` -> `<main id="main-content">` -> `<h1>` (`Generator & plant room weekly inspection`) -> `<h2>` (`Section A: 500kVA Generator Room`) -> `<h3>` (`Raise maintenance request`).
- Accessible names and roles: Segmented controls use `role="radiogroup" aria-labelledby="qN-text"` with `<button type="button" role="radio" aria-checked="true|false">`. Upload progress uses `role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"`.
- Reflow and zoom: Reflows cleanly at `320px` CSS width; all mobile inputs use `16px` font size to prevent iOS Safari viewport zoom.

## 8. Performance and offline
- Client-side canvas compression downscales camera photos (`4` to `12 MB`) to `<= 1920px` WebP (`~248 KB`) before upload over Nigerian mobile networks (`NFR-5.2`).
- Compressed photo blob is retained in browser memory until server `201 Created` confirmation so network drops never force the supervisor to re-shoot a photo in a dark plant room.

## 9. Analytics
| Event | Trigger | Properties |
|---|---|---|
| `inspection.question_answered` | Radio tap | `inspection_id`, `template_version`, `answer` (`pass` \| `fail` \| `na`) |
| `maintenance_request.raised_from_inspection` | Inline drawer submit | `inspection_id`, `priority`, `has_work_order` |
| `inspection.approved` | Supervisor sign-off commit | `inspection_id`, `template_version`, `duration_seconds` |
| `inspection.superseded` | Create corrected version commit | `original_inspection_id`, `new_inspection_id` |

## 10. Acceptance criteria
1. Built from this screen spec; all states (`Active Runner`, `Loading Skeleton`, `Validation Error 422`, `Offline / Interrupted Upload`, `Immutable Locked Record`, and `Supersede Draft`) are reachable and tested.
2. Template questions render strictly from the pinned `inspection_template_id` version bound when the inspection was scheduled (`AD-10`).
3. Selecting `Fail` reveals the inline `Raise maintenance request` drawer pre-linked to the Property, Asset, and Inspection without losing checklist scroll position.
4. Once approved (`approved_at != null`), all checklist inputs and photo delete actions are permanently locked in the UI and service layer; `Create corrected version` creates a new draft with `supersedes_id`.
5. All mobile inputs/selects use `16px` font size and `#6E7E91` (`3.95:1`) borders, and all radio/action buttons meet `>=40px` touch target height.

## 11. Open questions and assumptions
| Item | Type (question, assumption) | Owner | Status |
|---|---|---|---|
| `ASSUMPTION-UJ2-1`: Client-side WebP compression targets `0.82` quality and max `1920px` dimension | Assumption (`NFR-5.2`) | UX / Eng | Confirmed in `DESIGN.md` & `EXPERIENCE.md` |

