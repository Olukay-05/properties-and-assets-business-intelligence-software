# Input Reconciliation Report 1: PRD & Technical Addendum (`prd.md` + `addendum.md`)

- **Sources Reconciled**:
  - `_bmad-output/planning-artifacts/prds/prd-business-intelligence-2026-11-02/prd.md`
  - `_bmad-output/planning-artifacts/prds/prd-business-intelligence-2026-11-02/addendum.md`
- **Target UX Spines**:
  - `_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/DESIGN.md`
  - `_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/EXPERIENCE.md`
- **Reconciliation Status**: **PASS — 100% of roles, protagonists, user journeys (`UJ-1`..`UJ-4`), functional requirements (`FR-1`..`FR-23`), non-functional requirements (`NFR-5.1`..`NFR-5.5`), and qualitative UX constraints are preserved.**

---

## 1. User Roles, Non-Users & Named Protagonists

| Source Element (`prd.md` §2) | Coverage in `DESIGN.md` / `EXPERIENCE.md` | Status |
|---|---|---|
| **Platform admin** (`FR-20`) | `EXPERIENCE.md` §Information Architecture (`Tenancy/MembershipsIndex.tsx`, `Audit/AuditEventsIndex.tsx`), §Foundation (`>760px` desktop canvas). | Covered |
| **Organisation owner** (`FR-20`) | `EXPERIENCE.md` §Information Architecture (all 14 surfaces), Flow S-3 (membership role assignment, `client_scope`, audit & NDPA ops). | Covered |
| **Operations manager** (`FR-20`) | `EXPERIENCE.md` §Information Architecture (Registry, Inspections, Maintenance, Reporting), `UJ-1` & `UJ-4` protagonist workflows. | Covered |
| **Site supervisor** (`FR-20`) | `EXPERIENCE.md` §Information Architecture, `mobile-quick-task-bar`, `UJ-2` inspection execution, fault raising, and supervisor sign-off. | Covered |
| **Technician** (`FR-20`) | `EXPERIENCE.md` §Information Architecture, `mobile-quick-task-bar`, `UJ-3` multi-visit progress and photo-gated work order completion. | Covered |
| **Contractor (minimal)** (`FR-20`, §3) | `EXPERIENCE.md` §Information Architecture (`WorkOrdersIndex.tsx`, `WorkOrderDetail.tsx`), `DESIGN.md` §Layout & Spacing (`mobile-quick-task-bar`). | Covered |
| **Multi-role employees in smaller FM companies** (§2.1, `FR-20`) | `EXPERIENCE.md` Flow S-3 explicitly enforces that a single staff member can hold multiple roles (e.g., `Site supervisor` + `Technician`) and inherits the union of permissions. | Covered |
| **Non-Users in v1** (Client staff / property owners, External integrators / AI agents, Tenants/occupants — §2.2, §7) | `EXPERIENCE.md` `UJ-4` (manual email/sharing of exported PDF/CSV report; no self-service portal), §Foundation (Sanctum first-party only, zero MCP/OAuth/lease UI). | Covered |
| **Protagonist 1: `Chidi Okafor`** (Operations Manager) | `EXPERIENCE.md` §Key Flows `UJ-1`, `UJ-4`, `Flow S-1`, `Flow S-2`, and `Component Patterns` #19. | Covered |
| **Protagonist 2: `Amara Bello`** (Site Supervisor) | `EXPERIENCE.md` §Key Flows `UJ-1` (assigned Custodian), `UJ-2` (runs inspection & raises work order), `Voice and Tone` lock notice. | Covered |
| **Protagonist 3: `Tunde Adeyemi`** (Technician) | `EXPERIENCE.md` §Key Flows `UJ-2` (assigned work order), `UJ-3` (logs partial progress & completes with photo), `State Patterns` #10. | Covered |

---

## 2. User Journeys (`UJ-1` .. `UJ-4`)

| Journey | Narrative, Climax & Edge Case in `prd.md` §2.3 | Verification in `EXPERIENCE.md` §Key Flows | Status |
|---|---|---|---|
| **UJ-1: Chidi registers a new client's site and its assets** | Create client account → register property (address, site code, optional postcode, GPS location) → add zones → register assets → assign accountable custodian. **Climax**: Property on map + assets/custodians listed. **Edge case**: Wrong GPS pin re-recorded; previous reading kept in history; Custodian distinct from Technician. | `EXPERIENCE.md` `UJ-1` (steps 1–6) + `Flow S-2`: covers `ClientAccountsIndex` → `PropertiesIndex`/`PropertyDetail` (`<PropertyMap>`, `LocationService::setLocation()`, NIPOST non-title disclaimer) → `Zones` tab → `Assets` tab (`LVE-GEN-01`) → Custodian `Amara Bello` → step 6 re-survey edge case preserving `Location History` `timeline-feed`. | Covered |
| **UJ-2: Amara runs a scheduled inspection and raises a work order** | Open today's inspection on phone → answer template checklist (Pass/Fail/N/A, Yes/No, notes) + attach photos → record asset condition → raise maintenance request on failing asset → becomes work order → complete & lock inspection. **Edge case**: Mistake on approved inspection creates a corrected version linked to original. | `EXPERIENCE.md` `UJ-2` (steps 1–6): covers mobile (`<=760px`) `InspectionRunner.tsx`, `checklist-runner`, client-side downscaled photo upload, failing asset condition update, inline `Raise Maintenance Request` → `Work Order` (`WO-2026-0142`), `Approve & Sign Off Inspection` lock, and step 6 `Create Corrected Version` (`supersedes_id`). | Covered |
| **UJ-3: Tunde completes an assigned work order with evidence** | Open assigned work order → see asset, location, fault → record progress (work performed, outstanding work, delay reasons) without false closure → complete job with photo + timestamp (location optional) → reflected in asset history. **Edge case**: Multi-visit partial progress (`In Progress` / `On Hold`). | `EXPERIENCE.md` `UJ-3` (steps 1–5): covers fault review, step 2 multi-visit `On Hold` progress logging (`work_performed`, `outstanding_work`, `delay_reason`), step 3 completion with photo when indoor generator-house GPS is unavailable (`location: null`), step 4 asset `timeline-feed` reflection, and step 5 missing-photo / wrong-assignee gates. | Covered |
| **UJ-4: Chidi compiles and exports the client's monthly report** | Select client & period → review compiled summary (visits, jobs by status, outstanding issues, evidence, condition) → export PDF/CSV scoped to that client alone → share manually. **Edge case**: Zero activity in period states that explicitly rather than rendering an empty document. | `EXPERIENCE.md` `UJ-4` (steps 1–5) + `State Patterns` #3 + `Voice and Tone`: covers `ReportCompiler.tsx`, synchronous HTML preview (`ReportData`), queued `Export PDF & CSV` (`async-export-banner` polling), manual download/sharing, and explicit zero-activity certification callout/table. | Covered |

---

## 3. Functional Requirements (`FR-1` .. `FR-23`) & Addendum Entities

| FR | Requirement & Testable Consequences (`prd.md` §4 & `addendum.md`) | UX Spine Realization (`EXPERIENCE.md` & `DESIGN.md`) | Status |
|---|---|---|---|
| **FR-1** | **Manage client accounts**: company name, primary contact, email, phone, address, status (`active`/`inactive`), internal notes; org-scoped; forged `organisation_id` ignored; no invoicing/CRM/client logins. | `Registry/ClientAccountsIndex.tsx`, `app-shell-header` Client Account Scope Switcher, `UJ-1` step 1, `Foundation` tenant isolation rules. | Covered |
| **FR-2** | **Register a property**: address, site code, single Client Account, optional postcode + validation status; never blocks on postcode lookup; NIPOST optional/server-side/feature-flagged; never proves title/ownership. | `Registry/PropertiesIndex.tsx`, `Registry/PropertyDetail.tsx`, `UJ-1` step 2, `Voice and Tone` rule #2 (`Postcode verified (NIPOST)` + non-title disclaimer). | Covered |
| **FR-3** | **Record property location**: single canonical `geom` point + append-only `locations` history (`accuracy_m`, `source`, `captured_by`, `captured_at`); latest row is current; lat/lng derived. | `property-map` component (#15), `timeline-feed` (#19), `Interaction Primitives` #2, `UJ-1` steps 2 & 6. | Covered |
| **FR-4** | **Organise a property into zones**: Zones (`site_zones`) nested under a single Property and scoped to acting Organisation. | `Registry/PropertyDetail.tsx` (`Zones` tab), `UJ-1` step 3. | Covered |
| **FR-5** | **Register assets**: belongs to one Property; unique `tag` within Property, `category`, `condition`, `status`, optional make/model/serial/description. | `Registry/AssetsIndex.tsx`, `Registry/AssetDetail.tsx`, `UJ-1` step 4. | Covered |
| **FR-6** | **Assign asset custody**: recorded as append-only `asset_custody` history; latest is current Custodian; stored distinctly from maintenance Technician. | `Registry/AssetDetail.tsx` (`Assign / Handover Custody` modal), `Voice and Tone` rule #3, `UJ-1` step 4, `Flow S-2`. | Covered |
| **FR-7** | **Locate a property on the map**: Property renders at canonical coordinates on in-app map. | `DESIGN.md` #15 `property-map` & `EXPERIENCE.md` #15 (`PropertiesIndex.tsx`, `PropertyDetail.tsx`). | Covered |
| **FR-8** | **List a client's assets and custodians**: scoped strictly to selected Client Account. | `Registry/AssetsIndex.tsx`, `PropertyDetail.tsx` (`Assets` tab), `UJ-1` step 5. | Covered |
| **FR-9** | **Schedule an inspection**: scoped to a Property of acting Organisation. | `Inspections/InspectionsIndex.tsx`. | Covered |
| **FR-10** | **Manage inspection templates**: sections, questions (`Pass`/`Fail`/`N/A`, `Yes`/`No`, notes), associated with property type/asset category/inspection type; versioned (`version` badge). | `Inspections/TemplatesIndex.tsx`, `checklist-runner` (#18), `Flow S-1`. | Covered |
| **FR-11** | **Conduct an inspection**: checklist responses, attached photos, per-asset condition; pinned template `version`; approved inspection immutable; correction creates new version (`supersedes_id`); supervisor sign-off is a field on `inspections`. | `Inspections/InspectionRunner.tsx`, `checklist-runner` (#18), `State Patterns` #11, `UJ-2` steps 1–6. | Covered |
| **FR-12** | **Raise a maintenance request**: fault description; linked to originating Inspection when raised from one (or raised directly). | `Maintenance/RequestsIndex.tsx`, `checklist-runner` inline `Raise Maintenance Request` drawer, `Interaction Primitives` #3, `UJ-2` step 2. | Covered |
| **FR-13** | **Create and assign a work order**: target Property (optional Asset), work description, assigned Technician/Contractor; status flow `open → assigned → in_progress → completed` + `on_hold` / `cancelled`; supervisor sign-off field on `work_orders`. | `Maintenance/WorkOrdersIndex.tsx`, `Maintenance/WorkOrderDetail.tsx`, `UJ-2` step 3, `UJ-3`. | Covered |
| **FR-14** | **Record work progress**: Technician records `work_performed`, `outstanding_work`, `delay_reason`; moves to `in_progress`, `on_hold`, or `cancelled` without false closure. | `Maintenance/WorkOrderDetail.tsx` (`Record Progress / Place On Hold`), `Interaction Primitives` #4, `UJ-3` step 2. | Covered |
| **FR-15** | **Complete a work order with evidence**: requires photo + timestamp; optional GPS location never blocks completion; Technician cannot complete a Work Order assigned to someone else. | `Maintenance/WorkOrderDetail.tsx`, `evidence-uploader` (#16), `State Patterns` #9 & #10, `UJ-3` steps 3 & 5. | Covered |
| **FR-16** | **Reflect work in asset history**: completed Work Order and evidence appear in Asset's history & timeline. | `Registry/AssetDetail.tsx`, `timeline-feed` (#19), `UJ-3` step 4, `Flow S-2`. | Covered |
| **FR-17** | **Compile a client report**: client & period, properties covered, inspections completed, work orders by status, outstanding issues, asset condition summary, evidence references; scoped to single Client Account; explicit zero-activity state. | `Reporting/ReportCompiler.tsx`, `State Patterns` #3, `UJ-4` steps 1, 2 & 5. | Covered |
| **FR-18** | **Export a report**: readable client-facing PDF (with evidence images) and structured CSV (records + evidence references). | `Reporting/ReportCompiler.tsx`, `async-export-banner` (#20), `UJ-4` steps 3–4. | Covered |
| **FR-19** | **Resolve tenant from session**: `organisation_id` resolved strictly from session; forged input ignored; enforced across HTTP, service, SQL, jobs, signed URLs. | `EXPERIENCE.md` §Foundation, `app-shell-header` (#1), `State Patterns` #10. | Covered |
| **FR-20** | **Authorise by role**: 6 Phase 1 roles + `client_scope`; multi-role union supported. | `EXPERIENCE.md` §Information Architecture table, `Tenancy/MembershipsIndex.tsx`, `State Patterns` #10, `Flow S-3`. | Covered |
| **FR-21** | **Manage evidence as private attachments**: records `uploaded_by`, `uploaded_at`, owner (`inspection`/`asset`/`work_order`), optional location; private R2 storage; 10-min signed URLs after auth/scope check. | `DESIGN.md` & `EXPERIENCE.md` #16 `evidence-uploader` & #17 `evidence-gallery` (including auto-refresh of expired 403 signed URLs). | Covered |
| **FR-22** | **Record audit events**: actor, action, target, timestamp; queryable for compliance review. | `Audit/AuditEventsIndex.tsx`, `Flow S-3`. | Covered |
| **FR-23** | **Support data-protection operations (NDPA 2023)**: 72-hour breach notification support, retention/deletion, data-subject access/rectification/deletion requests. | `Audit/AuditEventsIndex.tsx` (`/organisation/audit`), `Flow S-3`. | Covered |

---

## 4. Non-Functional Requirements (`NFR-5.1` .. `NFR-5.5`) & Qualitative Constraints

| NFR / Constraint | Source Specification (`prd.md` §5–§9) | UX Spine Realization | Status |
|---|---|---|---|
| **NFR-5.1 Security & Tenant Isolation** | Two-layer isolation (app + RLS), 5-vector isolation matrix, HTTPS, Sanctum first-party auth. | `EXPERIENCE.md` §Foundation, `State Patterns` #10 (`404` on cross-tenant/cross-client UUIDs). | Covered |
| **NFR-5.2 Performance (Nigerian Mobile Networks)** | Responsive on ordinary/unreliable Nigerian mobile connections (`LCP < 2.5s` target); paginated lists/maps; fast server responses (`p95 < 500ms`). | `DESIGN.md` §Typography (zero external webfonts — system Arial & Georgia only); `EXPERIENCE.md` §Responsive & Platform (client-side `<=1920px` WebP/JPEG compression, `20`-row pagination, Inertia partial reloads). | Covered |
| **NFR-5.3 Reliability & Operability** | Structured error shape (never raw DB errors); backups & PITR; `99.5%` availability target. | `EXPERIENCE.md` `State Patterns` #5 (`HTTP 422`), #6 (`DomainException` 3-part alert strip), #7 (`HTTP 429`), #8 (network-degraded non-destructive retry bar). | Covered |
| **NFR-5.4 Accessibility** | **WCAG 2.1 AA** hard requirement — keyboard nav, ARIA labels, contrast ratios, audited before first pilot. | `DESIGN.md` §Colors (explicit contrast table `4.66:1` to `15.8:1`) & `EXPERIENCE.md` §Accessibility Floor (`40px` touch target floor, `14px` workflow text floor, `--cc-focus`, semantic tables, `aria-live`). | Covered |
| **NFR-5.5 Observability** | Frontend/backend error tracking (Sentry), uptime monitoring, and audit logging before real customer data enters. | `EXPERIENCE.md` `Audit/AuditEventsIndex.tsx` & `State Patterns` #6. | Covered |
| **Counter-Metrics Guardrails (`SM-C1`..`SM-C3`)** | Do not chase raw photo volume (`SM-C1`), junk asset count (`SM-C2`), or rushed inspection throughput (`SM-C3`). | `EXPERIENCE.md` `editorial-card` (#6) focuses on `Evidence Completeness` and `Overdue Work Orders` rather than vanity volume counters; `checklist-runner` (#18) prioritizes thorough per-item review. | Covered |

---

## 5. Surfaced `[ASSUMPTION]`, `[NOTE FOR PM]`, and Open Questions Triage

No qualitative UX ideas from `prd.md` or `addendum.md` were dropped. The following upstream items from `prd.md` §§6, 10, 11 remain tracked for PM/Engineering validation and require **no changes** to the UX spines (`DESIGN.md` / `EXPERIENCE.md` already accommodate both outcomes where applicable):

1. **`[ASSUMPTION]` Persona Composites (`prd.md` §2.3 / §11 #1)**: `Chidi Okafor`, `Amara Bello`, and `Tunde Adeyemi` are realistic Nigerian composites used consistently across `EXPERIENCE.md` `UJ-1`..`UJ-4`.
2. **`[ASSUMPTION]` Engineering Performance & Availability Benchmarks (`prd.md` §5.2, §5.3 / §11 #2–#4)**: `LCP < 2.5s`, `p95 API < 500ms`, and `99.5%` availability are supported by zero-webfont typography, client-side photo compression, and non-destructive network retry banners in `EXPERIENCE.md`.
3. **Open Question #2 (`D5` Map Provider Licensing — `prd.md` §10 #2)**: MapLibre GL JS + OpenFreeMap commercial licensing check before locking; encapsulated in `<PropertyMap>` (`property-map` #15) with graceful coordinate-table fallback.
4. **Open Question #6 & `[NOTE FOR PM]` (`prd.md` §6.2 / §10 #6)**: Confirm Nigeria National Cloud Policy classification for ordinary commercial FM data hosted in EU Central (Frankfurt).
5. **Open Question #7 (`prd.md` §10 #7 — Retention Periods)**: Specific retention durations for evidence and audit data are displayed/managed in `Audit/AuditEventsIndex.tsx` once counsel/PM locks the numeric day counts.
