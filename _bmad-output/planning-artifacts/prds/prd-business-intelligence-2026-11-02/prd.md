---
title: Property & Asset Intelligence
status: final
created: 2026-11-02
updated: 2026-11-03
---

# PRD: Property & Asset Intelligence

## 0. Document Purpose

This PRD defines what **Property & Asset Intelligence** will do for its first release (the MVP and paid pilot), for the product owner, the delivery team, and the downstream owners of architecture, epics, stories, and tests. It is capability-first: features are grouped and carry globally numbered Functional Requirements (FR-1…) whose terms are pinned in §3 Glossary. Inline assumptions are tagged `[ASSUMPTION: …]` and collected in §11.

This PRD builds on and does **not** duplicate the following approved inputs, which remain the deeper source of truth where they say more than this document:

- `docs/development-plan-review.md` (Revision 2) — approved scope, tenancy, geospatial, hosting, costs, roadmap, pilot measures, NDPA checklist.
- `docs/knowledge-base/mvp-scope.md`, `data-model.md`, `architecture-decisions.md`, `api-conventions.md` — operational conventions.
- `docs/cloud_crafters_design_system/` — the visual source of truth.

Technical decisions (stack, ADRs, endpoint list, entity set) are captured in `addendum.md` so this PRD stays about *what* and *why*, not *how*.

## 1. Vision

**Property & Asset Intelligence** gives Nigerian facilities-management (FM) providers a reliable way to know what is happening across every property and asset they manage, to ensure maintenance work is properly carried out and documented, and to give their clients clear evidence of the service they are paying for.

Today an FM company's operational picture lives in spreadsheets, WhatsApp threads, and memory. Inspections happen without a durable record, work orders are agreed verbally, and the monthly client report is reassembled by hand — so proof of work is weak, disputes are frequent, and billing leverage is lost. The product replaces that with a single, tenant-isolated system where a client's sites, assets, inspections, work orders, and evidence are registered once, updated in one place, and turned into a client-ready report on demand.

The product is sold as **operational control + proof of work** — not as postcode lookup, AI, or advanced geospatial analytics. It ships only when three complete workflows work end-to-end and pass automated tests: (1) property & asset registration, (2) inspection & maintenance, and (3) client reporting & evidence. Everything else is deferred until the pilot proves it is needed.

The product's visual and written character is an **editorial, document-like look** — `docs/cloud_crafters_design_system/` is the source of truth, and client-facing reports read like clean, professional documents rather than raw data dumps.

## 2. Target User

### 2.1 Jobs To Be Done

- **Know what is happening** — an FM operations lead wants a current, correct view of every site and asset under management, without chasing field staff for updates.
- **Get maintenance actually done and documented** — supervisors and technicians want a clear path from "this needs fixing" to "fixed, with photo and timestamp proof."
- **Prove the work to the client** — the FM provider wants to hand a client a report that shows completed visits, open jobs, and evidence, so the service is undeniable and the bill is defensible.
- **Keep each client's world separate** — the provider must never let one client's sites, assets, or evidence leak into another client's (or another organisation's) view.
- **Stay lightweight** — field users (supervisors, technicians) need a phone-browser experience that works on ordinary Nigerian mobile networks, not a heavyweight desktop tool.

Roles are **not** one-to-one with employees. In a smaller FM company one person may hold more than one role; permissions are assigned per role, and the system never assumes a separate employee per role.

### 2.2 Non-Users (v1)

- **The client's own staff** — property owners receive an exported report, not a login. A client-facing portal is deferred.
- **External integrators / AI agents** — there is no public API developer, OAuth client, or MCP surface in v1.
- **Tenants, occupants, or residents of managed properties** — the product does not manage leases, rent, or access control.

### 2.3 Key User Journeys

*Named-persona narratives the product enables. Personas are invented Nigerian composites `[ASSUMPTION: personas are realistic composites, not real people]`. FRs reference these journeys by ID.*

- **UJ-1. Chidi registers a new client's site and its assets.**
  - **Persona + context:** Chidi Okafor, Operations Manager at the FM provider, onboards a new client account (a commercial property owner) and needs their site live on the map with assets ready to inspect.
  - **Entry state:** Authenticated, scoped to his organisation and to the new client account.
  - **Path:** Creates the client account (name, primary contact, phone/email, address) → registers the property (address, site code, optional postcode, GPS location) → adds zones (e.g. "Ground floor", "Car park") → registers assets (tag, category, condition, status) → assigns each asset a custodian who is accountable for it.
  - **Climax:** The Property appears on the map at the right point, and its assets and custodians are listed.
  - **Resolution:** The client's portfolio is ready for inspection scheduling; Chidi moves to the next client.
  - **Edge case:** If the GPS pin is wrong, Chidi re-records the location — the system keeps the previous reading in history rather than silently overwriting it. *(The custodian Chidi assigns is the person accountable for the asset; the technician who later performs maintenance may be a different person — see UJ-3.)*

- **UJ-2. Amara runs a scheduled inspection and raises a work order.**
  - **Persona + context:** Amara Bello, Site Supervisor, walks a client site against a checklist and finds a fault that needs a technician.
  - **Entry state:** Authenticated on her phone, on-site, with today's inspection open.
  - **Path:** Opens the inspection, which runs against the site's template → answers each checklist item (Pass / Fail / N/A, Yes/No, notes) and attaches photos → records asset condition → finds a failing asset and raises a maintenance request → the request becomes a work order.
  - **Climax:** The work order exists with the checklist findings and photos attached, ready to assign.
  - **Resolution:** The inspection is complete and immutable; the raised work order is awaiting assignment.
  - **Edge case:** Amara made a mistake on an approved inspection — she can't edit it; the system lets her create a corrected version linked to the original.

- **UJ-3. Tunde completes an assigned work order with evidence.**
  - **Persona + context:** Tunde Adeyemi, a Technician who may be internal staff or an external contractor, receives a work order for the fault Amara raised and fixes it on-site.
  - **Entry state:** Authenticated, sees the work order assigned to him.
  - **Path:** Opens the work order → sees the asset, location, and what's wrong → records progress (work performed, outstanding work, delay reasons) without falsely closing it → completes the job → uploads completion evidence (photo + timestamp; location if available) → marks it complete.
  - **Climax:** The work order closes and the asset's history now shows the job and its evidence.
  - **Resolution:** The asset's timeline reflects the completed work; the client's next report will include it.
  - **Edge case:** The fix can't be done in one visit — Tunde records partial completion and the work order moves to In Progress (or On Hold) rather than being falsely closed.

- **UJ-4. Chidi compiles and exports the client's monthly report.**
  - **Persona + context:** Chidi, at month end, needs to send the client a report of visits completed, jobs by status, evidence, and asset condition — scoped to that client alone.
  - **Entry state:** Authenticated, at the client's account.
  - **Path:** Opens reporting for the client → selects the period → reviews the compiled summary (visits, jobs by status, outstanding issues, evidence, condition) → exports PDF/CSV.
  - **Climax:** The report is generated and exported, containing only that client's sites and records.
  - **Resolution:** Chidi shares the report manually with the client (no self-service portal in v1).
  - **Edge case:** If the client has no activity in the period, the report states that explicitly rather than rendering an empty document.

## 3. Glossary

*Downstream readers and workflows must use these terms exactly. FRs, UJs, and SMs use these terms verbatim.*

- **Organisation** — the FM company that is the paying tenant. Each tenant-owned row carries its `organisation_id`.
- **Client Account** — a property owner managed by an Organisation; lives *inside* that Organisation, not as a separate tenant. One Organisation has many Client Accounts.
- **Property** — a client's site or premises; has an address, a site code, an optional postcode, and a canonical **geometry** (location). One Client Account has many Properties.
- **Zone** — a named sub-area within a Property (e.g. "Ground floor", "Car park"). One Property has many Zones.
- **Asset** — a maintainable item at a Property, with a tag, category, condition, and status. One Property has many Assets.
- **Custodian** — the person **accountable** for an Asset, recorded per assignment in the asset's custody history. Distinct from the Technician who performs maintenance.
- **Inspection** — a scheduled visit that records checklist responses, photos, and asset condition against an Inspection Template.
- **Inspection Template** — a reusable set of sections and checklist questions that an Inspection runs against. May be associated with a property type, asset category, or inspection type. Each Inspection records the template **version** it used.
- **Maintenance Request** — a request for work, raised from an Inspection (or directly), that describes what needs fixing.
- **Work Order** — the actionable unit of maintenance, assigned to a Technician and completed with evidence. Moves through a status flow: **Open → Assigned → In Progress → Completed**, plus **On Hold** and **Cancelled**. A Work Order may reference a Property without a registered Asset.
- **Technician** — the person who performs the work; may be internal staff or a Contractor.
- **Contractor** — an external party who can be assigned work (minimal role in v1).
- **Attachment / Evidence** — a photo or file proving work or condition; records who uploaded it, when, and which Inspection, Asset, or Work Order it belongs to, plus optional location. Stored privately and served only via signed URLs.
- **Report** — a client-ready summary of visits completed, jobs by status, evidence, and condition, exported as PDF/CSV.
- **Geometry (geom)** — the canonical location of a Property, stored as a point in WGS 84 (`4326`); the single source of truth for coordinates. Latitude/longitude are derived from it, never stored independently.
- **Postcode** — an optional location reference on a Property; it never proves title, ownership, or occupancy.
- **Tenant isolation** — the guarantee that one Organisation (and one Client Account within it) can never read or write another's rows or files.
- **Row-Level Security (RLS)** — the database-level backstop enforcing Tenant isolation on every tenant table.
- **Tenant Context** — the current Organisation and user binding resolved from the authenticated session for the duration of a request or job.
- **Client scope** — the restriction of a user's view to one Client Account (or to the whole Organisation), applied on top of tenant identity.

## 4. Features

### 4.1 Property & asset registration

**Description:** Onboards a client's portfolio into the system so it can be inspected and maintained. Realizes UJ-1. A user scoped to a Client Account registers Properties (with a canonical location), organises them into Zones, registers Assets, and assigns Custodians. The property's location is recorded through a single mutator that writes the geometry and appends a location-history entry, so a re-surveyed site never silently loses its previous reading.

#### FR-1: Manage client accounts
An authenticated user can create and maintain a Client Account within their Organisation. Realizes UJ-1.

**Consequences (testable):**
- A Client Account records the client/company name, primary contact person, contact email and phone, and address.
- A Client Account has an account status (active or inactive) and supports internal notes.
- A Client Account is always associated with the acting Organisation and is invisible to other Organisations.
- Creating a Client Account with a forged `organisation_id` in the request is ignored; the tenant is taken from the session.

**Out of Scope:** invoicing, contracts, CRM pipelines, and client logins — none of these are part of the MVP.

#### FR-2: Register a property
An authenticated user can register a Property with an address, site code, optional postcode, and location. Realizes UJ-1.

**Consequences (testable):**
- A Property records its address and site code.
- A Property is created under exactly one Client Account of the acting Organisation.
- The optional postcode is stored only as the customer-entered string plus its validation status; saving a Property never blocks on postcode lookup.
- Postcode verification (NIPOST) is optional, server-side, and feature-flagged; only the verification outcome is stored, never a copy of NIPOST data.

#### FR-3: Record property location
An authenticated user can set or update a Property's location; the system stores it as canonical geometry and keeps location history. Realizes UJ-1.

**Consequences (testable):**
- Setting a location writes a single canonical point and appends a history entry with accuracy, source, captured-by, and captured-at.
- The current location is the latest history entry; earlier readings remain retrievable.
- Latitude/longitude returned to clients are derived from the canonical geometry, never from a second stored copy.

#### FR-4: Organise a property into zones
An authenticated user can add Zones to a Property. Realizes UJ-1.

**Consequences (testable):**
- Zones are nested under a single Property and scoped to the acting Organisation.

#### FR-5: Register assets
An authenticated user can register an Asset at a Property with a tag, category, condition, and status, plus optional descriptive details. Realizes UJ-1.

**Consequences (testable):**
- An Asset belongs to exactly one Property and carries a tag that uniquely identifies it within that Property, its category, condition, and status.
- Optional details (e.g. make/model/serial or a short description) can be recorded but are not required.

#### FR-6: Assign asset custody
An authenticated user can assign a Custodian to an Asset, recording the assignment as history. Realizes UJ-1.

**Consequences (testable):**
- Custody assignments are recorded as history; the current Custodian is the latest assignment.
- The Custodian (accountable for the Asset) is stored distinctly from any Technician who performs maintenance on it.

#### FR-7: Locate a property on the map
An authenticated user can see a Property's location on a map within the application. Realizes UJ-1.

**Consequences (testable):**
- A Property with a recorded location renders at its canonical coordinates on the map.

#### FR-8: List a client's assets and custodians
An authenticated user can list a Client Account's Assets and their Custodians, scoped to that client. Realizes UJ-1.

**Consequences (testable):**
- The list contains only Assets belonging to that Client Account, never another client's or organisation's.

### 4.2 Inspection & maintenance

**Description:** Turns inspections into documented evidence and actionable work. Realizes UJ-2 and UJ-3. A user schedules an Inspection, runs it against an Inspection Template (checklist + photos + condition), raises a Maintenance Request when a fault is found, which becomes a Work Order that is assigned to a Technician, tracked through its status flow, completed with evidence, and reflected in the asset's history. Approved inspections are immutable; corrections create a new version.

#### FR-9: Schedule an inspection
An authenticated user can schedule an Inspection for a Property. Realizes UJ-2.

**Consequences (testable):**
- A scheduled Inspection is scoped to a Property of the acting Organisation.

#### FR-10: Manage inspection templates
An authorised user can create, edit, and reuse Inspection Templates. Realizes UJ-2.

**Consequences (testable):**
- An Inspection Template contains sections and checklist questions.
- Questions support answer types such as Pass, Fail, N/A, Yes/No, and notes, where appropriate.
- A template can be associated with a property type, asset category, or inspection type (e.g. a generator inspection uses different criteria than a fire-extinguisher inspection).

#### FR-11: Conduct an inspection
An authenticated user can run a scheduled Inspection by completing its template's checklist, attaching photos, and recording asset condition. Realizes UJ-2.

**Consequences (testable):**
- An Inspection records its checklist responses, attached evidence, and per-asset condition.
- An Inspection retains the version of the Inspection Template it used, so later template changes do not rewrite historical inspections.
- An approved Inspection cannot be edited; a correction creates a new version linked to the original.
- Supervisor sign-off is a field on the Inspection, not a separate approval entity.

#### FR-12: Raise a maintenance request
An authenticated user can raise a Maintenance Request, including from within an Inspection. Realizes UJ-2.

**Consequences (testable):**
- A Maintenance Request carries a description of the fault and is linked to the originating Inspection when raised from one.

#### FR-13: Create and assign a work order
An authenticated user can create a Work Order (from a Maintenance Request or directly) and assign it to a Technician. Realizes UJ-2 and UJ-3.

**Consequences (testable):**
- A Work Order records its target Property (and optionally an Asset), the work to be done, and the assigned Technician.
- A Work Order follows the status flow Open → Assigned → In Progress → Completed, with On Hold and Cancelled as additional outcomes.
- A Work Order can reference a Property without a registered Asset.
- Supervisor sign-off is a field on the Work Order, not a separate approval entity.

#### FR-14: Record work progress
An authenticated Technician can record progress on an assigned Work Order without falsely marking it complete. Realizes UJ-3.

**Consequences (testable):**
- A Technician can record work performed, outstanding work, and reasons for delay.
- The Work Order can move to In Progress, On Hold, or Cancelled without being marked Completed.

#### FR-15: Complete a work order with evidence
An authenticated Technician can complete an assigned Work Order by recording completion and attaching evidence. Realizes UJ-3.

**Consequences (testable):**
- Completing a Work Order requires completion evidence (photo + timestamp) and moves it to Completed.
- Location on evidence is captured where available and does **not** block completion — poor GPS reception or device permissions must not prevent legitimate work from being recorded.
- A Technician cannot complete a Work Order assigned to someone else within their client scope.

#### FR-16: Reflect work in asset history
Completed work is reflected in the relevant Asset's history and timeline. Realizes UJ-3.

**Consequences (testable):**
- An Asset's history and timeline include the Work Order and its evidence after completion.

### 4.3 Client reporting & evidence

**Description:** Produces the client-ready proof of work. Realizes UJ-4. A user compiles a Report for one Client Account over a period and exports it as PDF or CSV. The report is scoped to that client alone; sharing is manual (no self-service portal in v1).

#### FR-17: Compile a client report
An authenticated user can compile a Report for a Client Account over a chosen period. Realizes UJ-4.

**Consequences (testable):**
- The compiled Report includes: the client and reporting period; the properties covered; inspections/visits completed; work orders grouped by status; outstanding maintenance issues; an asset condition summary; and relevant photos/evidence references.
- The Report contains only the selected Client Account's sites and records.

#### FR-18: Export a report
An authenticated user can export a compiled Report as PDF and CSV. Realizes UJ-4.

**Consequences (testable):**
- The PDF export renders a readable, client-facing report including evidence images where appropriate.
- The CSV export contains structured records and evidence references (not a visual reproduction of the report).

### 4.4 Platform foundations (cross-cutting)

**Description:** The guarantees every other feature depends on: tenant resolution, role authorisation, private evidence, audit, and data-protection operations. These FRs are not a workflow but underpin all three.

#### FR-19: Resolve tenant from the session
The acting Organisation is resolved from the authenticated session for every request and job, never from request input.

**Consequences (testable):**
- A request or job body that includes an `organisation_id` has it ignored; the tenant is the session's Organisation.
- Cross-tenant requests at the HTTP layer, the service layer, and raw SQL each return empty/denied (see §5.1).

#### FR-20: Authorise by role
Actions are authorised by the authenticated user's role and client scope. Phase 1 roles are Platform admin, Organisation owner, Operations manager, Site supervisor, Technician, and Contractor (minimal).

**Consequences (testable):**
- A user cannot perform an action their role does not permit, and cannot act on a Client Account outside their scope.
- A single person may hold more than one role in a smaller organisation; permissions follow the role, not a distinct employee per role.

#### FR-21: Manage evidence as private attachments
Evidence (photos/files) is stored privately, records its provenance, and is served only through short-lived signed URLs issued after authorisation. Realizes UJ-2, UJ-3.

**Consequences (testable):**
- Each Attachment records who uploaded it, when it was uploaded, and which Inspection, Asset, or Work Order it belongs to.
- Optional location is captured on evidence where available, and its absence never blocks the owning work.
- There is no public or guessable URL for an Attachment.
- A signed URL cannot be obtained for another Organisation's or Client Account's Attachment.

#### FR-22: Record audit events
Sensitive operations (login, data export, admin actions, and other compliance-relevant actions) are recorded in an audit trail.

**Consequences (testable):**
- Each audit event records the actor, action, target, and timestamp, and is queryable for compliance review.

#### FR-23: Support data-protection operations
The platform supports the pilot's NDPA obligations: breach notification, retention/deletion, and data-subject requests.

**Consequences (testable):**
- A security incident can be surfaced to the pilot customer promptly enough for the controller to meet NDPA's 72-hour clock.
- Retention periods are defined and enforced; deletion on request or offboarding is supported.
- Access, rectification, and deletion of stored personal data can be fulfilled.

## 5. Cross-Cutting NFRs

*System-wide quality attributes, not tied to a single feature. Hard requirements are stated plainly; targets to validate are tagged `[ASSUMPTION]`.*

### 5.1 Security & tenant isolation (hard requirement)

- Tenant isolation is enforced at **two layers** and both are tested: (1) every query is scoped by the app, and (2) **RLS** enforces `organisation_id` at the database on every tenant table.
- **Automated tests must prove** one Organisation cannot access another Organisation's records through **application queries (HTTP + service layer), raw SQL, background jobs, and file access (signed URLs)**. RLS alone is not the complete security design — the app scope and RLS must both hold, and both are verified.
- HTTPS everywhere; security headers configured; secrets never in client code or in version control.
- Authentication is first-party only in v1; no external OAuth, no public API client registry.

### 5.2 Performance (targets to validate)

- Page loads should feel responsive on **ordinary Nigerian mobile connections, including less reliable networks**. `[ASSUMPTION: target Largest Contentful Paint < 2.5s and Core Web Vitals within the "good" thresholds on a mid-range Android device — an engineering benchmark whose exact test conditions are defined during implementation.]`
- API responses should be fast server-side. `[ASSUMPTION: target p95 API latency < 500ms measured at the application, excluding the unavoidable ~110–150ms user→EU round-trip documented in the approved plan — a sensible engineering objective, not a customer-validated requirement.]`
- Map rendering must not degrade list/detail interactions; assets and properties load paginated.

### 5.3 Reliability & operability (targets + hard requirement)

- `[ASSUMPTION: target 99.5% monthly availability for the pilot — roughly 3h36m of downtime in a 30-day month — as an objective, not a contractual SLA, until the infrastructure is proven.]`
- **Hard requirement:** automated backups (point-in-time recovery plus nightly portable export) and a documented, tested restore procedure before the first pilot customer.
- Errors are surfaced through a structured error shape; raw database errors are never returned to clients.

### 5.4 Accessibility (hard requirement)

- The UI targets **WCAG 2.1 AA** — keyboard navigation, ARIA labels, and sufficient contrast, applied through the design-system tokens. Accessibility is built in, not postponed, and is verified by an accessibility audit before the first pilot customer.

### 5.5 Observability (hard requirement)

- Error tracking (frontend and backend), uptime monitoring with alerts, and audit logging are in place **before real customer data enters the platform**.

## 6. Constraints & Guardrails

### 6.1 Privacy & compliance (NDPA 2023)

*These are pilot-readiness items, not "later" items — compliance is not a feature that can be added later. See the approved plan §11.*

- The pilot customer is the **controller**; the platform is the **processor**. A data-processing agreement is signed before onboarding the first customer.
- **Data minimisation:** store only what the workflows need — technician/custodian names and phones, site/asset photos. No identity documents, no tenant/occupant data.
- **Purpose limitation & retention:** document what is stored, why, and for how long; support deletion on request/offboarding.
- **Breach notification:** the platform notifies the pilot customer promptly enough for the controller to meet NDPA's 72-hour clock.
- **Cross-border transfer:** data is hosted in the EU; the transfer basis is documented via the DPA/standard clauses.
- **Data-subject requests:** a simple process for access, rectification, and deletion.

### 6.2 Data governance & hosting

- **Residency:** data resides in the EU (hosting decision in the approved plan); no African region exists at the chosen provider.
- **EU hosting is the approved deployment decision.** Before onboarding a pilot, the team documents the transfer basis, the data-processing agreement, and the customer's controller responsibilities. The coding agent must not treat compliance as something to bolt on later. [NOTE FOR PM: confirm the National Cloud Policy classification/residency position for ordinary commercial FM data before the pilot — Open Question #6.]
- **Retention:** evidence and audit data retention is defined and enforced; offboarding deletes or hands back a client's data on request.
- **Postcode truthfulness:** a postcode is an optional reference and never presented as proof of title, ownership, or occupancy (a hard "never" per `mvp-scope.md`).

### 6.3 Cost

- `[ASSUMPTION: target ≈ $35–55/month for the initial production deployment (pilot + first customers), with dev/staging ≈ $8–15/month, per the approved plan's verified budget. This is a constraint for initial deployment, not a promise that the system will always cost that as customers and usage grow.]`
- Billing/usage alerts are configured before production; free-tier limits are monitored so a spike cannot silently become a bill.

## 7. Non-Goals (Explicit)

This product is **not** any of the following, in v1:

- A **client-facing portal** or self-service login for property owners — the client's proof is the exported Report, shared by the FM provider.
- A **public integration platform** — no MCP server, AI-agent tools, webhooks, or third-party OAuth in v1.
- A **desktop / offline / native mobile** app — one responsive web application.
- An **AI-drafted-report** product, WhatsApp integration, or contractor SLA/portal.
- A **preventive-maintenance rules engine**, parts/inventory system, or IoT platform.
- A **rent/lease/tenant/access-control** system, and **never** title, ownership, identity, or occupancy verification.
- Advanced geospatial analytics (clustering, proximity) and NIPOST level 2+ integration.

## 8. MVP Scope

### 8.1 In scope

- Three complete workflows: **property & asset registration**, **inspection & maintenance**, **client reporting & evidence**.
- Multi-tenant isolation (app scoping + RLS) and the tenant-isolation test matrix.
- First-party authentication; role- and client-scoped authorisation.
- Private evidence storage with provenance and signed URLs.
- Client-ready PDF/CSV reporting.
- Audit trail, automated backups, CI, and the NDPA pilot-readiness checklist.

### 8.2 Out of scope for MVP

- **Deferred to Phase 3 (gated on evidence):** MCP/AI-agent tools, webhooks, OAuth/Passport for external integrators, `api_clients`, Tauri desktop, offline sync, advanced geospatial analytics, AI-drafted reports, WhatsApp, contractor portal/SLA scorecards, preventive-maintenance rules, parts/inventory, IoT, client-facing portal, NIPOST levels 2+.
- **Deferred to launch (Phase 2):** billing/subscriptions (`subscriptions`, `usage_records`) — pilots are invoiced manually.
- **Never:** title, ownership, identity, or tenant verification.

## 9. Success Metrics

*Each SM cross-references the FR(s) it validates. Counter-metrics guard against gaming. `[ASSUMPTION: targets are the provisional pilot success measures from the approved plan §10; baselines are set in week 1 of the pilot and re-measured at weeks 4–6 against the pilot customer's actual baseline.]`*

**Primary**
- **SM-1**: Work-order adoption — ≥80% of agreed pilot work orders are created and closed in the app (vs WhatsApp/paper). Validates FR-13, FR-15.
- **SM-2**: Inspection recording — ≥70% of scheduled inspections are recorded in-app. Validates FR-9, FR-11.
- **SM-3**: Evidence completeness — ≥90% of completed visits have photo + timestamp evidence; location is captured where available and reported separately. Validates FR-15, FR-21.
- **SM-4**: Register completeness — ≥85% of agreed assets have Property + tag + category + condition + custodian. Validates FR-2, FR-5, FR-6.
- **SM-5**: Time saved — monthly client-report preparation time reduced ≥50% (timed before/after). Validates FR-17, FR-18.

**Secondary**
- **SM-6**: Willingness to pay — the pilot signs (or states written intent to) a paid plan at a stated price. Validates the whole product. [NOTE FOR PM: pricing remains a hypothesis — Open Question #3.]
- **SM-7**: Retention signal — the customer keeps using the product into a second month without founder prompting.

**Counter-metrics (do not optimize)**
- **SM-C1**: Volume of photos captured — do not chase raw attachment counts; it counterbalances SM-3 (evidence *completeness*, not volume).
- **SM-C2**: Number of registered assets — do not inflate asset counts with junk entries to hit SM-4; counterbalances SM-4.
- **SM-C3**: Inspection throughput — do not rush inspections to hit SM-2 at the cost of SM-1/SM-3 quality.

**Pilot go/no-go** (from the approved plan §10.3): proceed with ≥1 paid pilot (target 2) plus measured improvement in ≥2 measures plus renewal intent at a paid tier; pivot or stop at <2 paid pilots after 15–20 discovery interviews, or no measurable improvement with price objections below the floor.

## 10. Open Questions

1. **PostGIS on Neon** — does the chosen Neon plan support `CREATE EXTENSION postgis` in a dedicated schema with the expected `Point`/GiST/`ST_DWithin` behaviour? (Week-1 spike; D1.)
2. **Map provider licensing** — confirm MapLibre GL JS + OpenFreeMap licensing for commercial use before locking the map layer (D5).
3. **Pricing** — final pilot price point; all figures remain hypotheses until a paid pilot signs.
4. **NDPC registration** — whether either party is a "controller/processor of major importance" requiring NDPC registration (confirm with counsel; do not guess).
5. **NIPOST fees** — unknown and excluded by design; revisit only if postcode verification is requested by a pilot.
6. **National Cloud Policy** — confirm the classification/residency position for ordinary commercial FM data before the pilot.
7. **Retention periods** — define the retention periods for evidence and audit data before the first pilot customer (see FR-23).

## 11. Assumptions Index

*Every `[ASSUMPTION]` from the document, surfaced for explicit confirmation. Hard requirements (RLS, accessibility, monitoring/audit logging, backups) are stated in §5 and are intentionally **not** listed here — they are commitments, not assumptions.*

1. §2.3 — User-journey personas (Chidi, Amara, Tunde) are realistic Nigerian composites, not validated customer research.
2. §5.2 — LCP < 2.5s and "good" Core Web Vitals are engineering benchmarks to validate; exact test conditions are defined during implementation.
3. §5.2 — p95 API latency < 500ms at the application is a sensible engineering objective, not a customer-validated requirement.
4. §5.3 — 99.5% monthly availability is a pilot objective (≈3h36m/month), not a contractual SLA.
5. §6.3 — production budget ≈ $35–55/month is an initial-deployment constraint to validate, not a permanent cost promise.
6. §9 — success-metric targets are provisional; they are measured against the pilot customer's actual baseline.
