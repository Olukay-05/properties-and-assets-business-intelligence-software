---
stepsCompleted: ["step-01", "step-02", "step-03", "step-04"]
inputDocuments:
  - "_bmad-output/planning-artifacts/prds/prd-business-intelligence-2026-11-02/prd.md"
  - "_bmad-output/planning-artifacts/prds/prd-business-intelligence-2026-11-02/addendum.md"
  - "_bmad-output/planning-artifacts/architecture/architecture-business-intelligence-2026-11-03/ARCHITECTURE-SPINE.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/DESIGN.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/EXPERIENCE.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/specs/screen-spec-uj1-property-asset-register.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/specs/screen-spec-uj2-mobile-inspection-runner.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/specs/screen-spec-uj3-mobile-work-order-execution.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/specs/screen-spec-uj4-client-report-compiler.md"
  - "docs/design/design-context.md"
  - "docs/security/AUTH.md"
  - "docs/security/SECURITY.md"
  - "docs/security/RLS-POLICIES.md"
  - "docs/security/ENV.md"
  - "docs/security/THREAT-MODEL.md"
  - "docs/security/DATA-PROTECTION.md"
---

# Property & Asset Intelligence - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for Property & Asset Intelligence, decomposing the requirements from the PRD, UX Design, and Architecture requirements into implementable stories.

**Story split convention (user-mandated):** every user-facing screen is built through a **paired story set** —
- **`*.data` stories** deliver the data flow: Inertia page props, Spatie Data DTOs, application-service methods, REST endpoints, and mutations. No pixel work.
- **`*.ui` stories** deliver the screen craft: layout, all 11 operational states, copy deck, responsive behavior, WCAG 2.2 AA, tokens only, against a named screen spec or the DESIGN/EXPERIENCE spine. Acceptance criteria draw from `.agents/skills/enterprise-ui-ux/checklists/dev-implementation-dod.md`.

A UI story is never started before its paired data story exists (or its props are stubbed from the DTO contract), so the dev agent gives the UI its own focused pass.

## Requirements Inventory

### Functional Requirements

FR1: Manage client accounts — create and maintain a Client Account (company name, primary contact, email/phone, address, active/inactive status, internal notes) within the acting Organisation; forged `organisation_id` ignored.
FR2: Register a property — address, site code, optional postcode, and location under exactly one Client Account; postcode stored as entered string + validation status; NIPOST check optional, server-side, feature-flagged, outcome-only, never blocking.
FR3: Record property location — single canonical geometry write plus append-only location history (accuracy, source, captured-by, captured-at); lat/lng derived, never stored twice.
FR4: Organise a property into zones — named Zones nested under a Property, tenant-scoped.
FR5: Register assets — tag (unique per property), category, condition, status, plus optional descriptive details (make/model/serial).
FR6: Assign asset custody — accountable Custodian recorded as append-only history; Custodian distinct from Technician; scope-checked.
FR7: Locate a property on the map — canonical coordinates rendered on an in-app map.
FR8: List a client's assets and custodians — scoped to the selected Client Account only.
FR9: Schedule an inspection — against a Property, tenant-scoped.
FR10: Manage inspection templates — create/edit/reuse templates with sections + checklist questions (Pass/Fail/N/A, Yes/No, notes); associable to property type, asset category, or inspection type; row-versioned when edited after use.
FR11: Conduct an inspection — complete the pinned template checklist with photos and per-asset condition; template version retained; approved inspections immutable with corrected versions via `supersedes_id`; supervisor sign-off is a field.
FR12: Raise a maintenance request — from an Inspection or directly; linked to the originating inspection.
FR13: Create and assign a work order — from a Maintenance Request or directly; status flow Open → Assigned → In Progress → Completed plus On Hold / Cancelled; may target a Property without an Asset; supervisor sign-off field.
FR14: Record work progress — technician logs work performed, outstanding work, delay reasons without falsely completing; transitions to In Progress / On Hold / Cancelled.
FR15: Complete a work order with evidence — requires at least one timestamped photo; GPS optional and never blocking; only the assigned technician (or authorised supervisor) completes.
FR16: Reflect work in asset history — completed work and evidence appear in the Asset's dynamic timeline.
FR17: Compile a client report — client + period, properties covered, inspections/visits completed, work orders by status, outstanding issues, asset condition summary, evidence references; strictly client-scoped; explicit zero-activity certification.
FR18: Export a report — PDF (readable, with evidence images) and CSV (structured records + evidence references) from one canonical report data structure; async with signed URLs.
FR19: Resolve tenant from the session — Organisation resolved from authenticated membership only, for every request and job; cross-tenant access denied at HTTP, service, and raw SQL layers.
FR20: Authorise by role — six roles (Platform admin, Organisation owner, Operations manager, Site supervisor, Technician, Contractor); role + client_scope enforced; multi-role memberships inherit the union.
FR21: Manage evidence as private attachments — provenance (who, when, owning Inspection/Asset/Work Order, optional location); private storage; short-lived signed URLs after authorisation; no public URLs.
FR22: Record audit events — actor, action, target, timestamp for sensitive operations (auth, exports, role/scope changes, NDPA events).
FR23: Support data-protection operations — breach notification surfacing (72-hour clock), retention/deletion, and data-subject access/rectification/deletion requests.

### NonFunctional Requirements

NFR1: Security & tenant isolation (hard) — two-layer isolation (app scoping + RLS on every tenant table), automated tests proving isolation across HTTP, service layer, raw SQL, background jobs, and signed URLs; HTTPS, security headers, no secrets in client code.
NFR2: Performance (targets) — responsive on ordinary Nigerian mobile connections; LCP < 2.5s and good Core Web Vitals (benchmark); p95 API latency < 500ms at the application; paginated lists; map rendering never degrades other interactions.
NFR3: Reliability & operability — 99.5% availability target for pilot (not SLA); automated backups (PITR + nightly portable export) with tested restore (hard); structured error shape, never raw DB errors.
NFR4: Accessibility (hard) — WCAG 2.1 AA minimum, executed to WCAG 2.2 AA per the UX contract; verified by audit before first pilot customer.
NFR5: Observability (hard) — error tracking (FE + BE), uptime monitoring with alerts, audit logging before real customer data enters the platform.
NFR6: Privacy & compliance (NDPA 2023) — controller/processor DPA before onboarding; data minimisation; purpose limitation; 72-hour breach support; documented cross-border transfer basis; data-subject request process.
NFR7: Data governance — EU residency; retention defined and enforced; postcode never presented as title/ownership/occupancy proof.
NFR8: Cost — initial production deployment ≈ $35–55/month (dev/staging ≈ $8–15); billing/usage alerts before production.

### Additional Requirements

From the Architecture Spine (ARCHITECTURE-SPINE.md, status: final):

- AD-1: Unified service layer — adapters (Inertia/REST/jobs) are thin; all business rules in application services; a feature is incomplete without a direct service-layer test.
- AD-2: Strict module ownership — one owning module per table (Tenancy & Identity, Registry, Inspections, Maintenance, Evidence, Reporting [read-only], Audit); cross-module writes via owner services; no separate `approvals` table.
- AD-3: Tenant from session only — `TenantContext` singleton from membership; `organisation_id` barred from DTOs, `$fillable`, FormRequests, query params; stamped by a model `creating` hook.
- AD-4: PostgreSQL RLS backstop — `ENABLE` + `FORCE RLS` on all 17 tenant tables; non-owner runtime role without `BYPASSRLS`; `SET LOCAL app.org_id` per transaction; Neon transaction-pooler compatible.
- AD-5: Client scoping — `memberships.client_scope` (null = unrestricted, else UUID array); denormalized `client_account_id` on all client-owned entities; global `ClientScope`.
- AD-6: Universal ordered UUID primary/foreign keys (HasUuids); no integer IDs anywhere.
- AD-7: Canonical `geometry(Point,4326)` + GiST; single mutator `LocationService::setLocation()`; distances only via `Geo::distanceMetres()`/`withinMetres()` with geography cast.
- AD-8: Append-only history tables (`locations`, `asset_custody`, `work_order_events`, `audit_events`) — no UPDATE/DELETE grants for the runtime role.
- AD-9: Asset timeline is a computed read model (no `timeline_events` table).
- AD-10: Template row versioning (`template_family_id` + `version`); inspections pin `inspection_template_id`; approved inspections immutable, corrections via `supersedes_id`.
- AD-11: Work-order state machine via `WorkOrderService::transition()` only; completion requires ≥1 timestamped photo; GPS never blocks; attachments immutable after completion/approval.
- AD-12: Service-layer authorisation via central policies for the six roles; verified before business logic executes.
- AD-13: Evidence via `EvidenceService` — magic-byte validation, R2 keys namespaced per tenant/client/owner, provenance persisted, 10-minute signed URLs after scope verification.
- AD-14: Tenant-aware queued jobs — serialize org + user, middleware re-establishes context + `SET LOCAL`; unscoped job execution is fatal.
- AD-15: Unified report read model — one `ReportData` object feeds both PDF (spatie/laravel-pdf + Chromium) and CSV; async TenantAware jobs; PDF worker concurrency = 1.
- AD-16: Spatie Data objects as full-stack contract; TypeScript types auto-generated (typescript-transformer); hand-written duplicate TS interfaces forbidden.
- AD-17: Domain exceptions with permanent snake_case codes → `{ error: { code, message } }` REST envelope and mapped Inertia errors; DB exceptions sanitized to Sentry.
- AD-18: Atomic audit logging inside the originating transaction.
- AD-19: Tests run on real PostgreSQL 17 + PostGIS as the non-owner role; 5-vector isolation matrix (HTTP, service, raw SQL, jobs, signed URLs).
- AD-20: Cloud/AI-agnostic abstractions — portable Postgres SQL, S3-compatible storage, env-configured tile endpoints; nightly `pg_dump` to isolated backup bucket.
- AD-21: Concurrency — pessimistic locks on transitions, composite unique indexes (`assets` org+property+tag; templates org+family+version), `ShouldBeUnique` jobs with tries/backoff/timeout.
- AD-22: Tiered rate limiting (auth 5/min, uploads 30/min, exports 5/min/org, API 120/min), magic-byte upload verification (allowlist jpeg/png/webp/pdf ≤10 MB), HTTP security headers (HSTS, nosniff, DENY, referrer, CSP).
- Stack: Laravel 13 (React starter kit) + PHP 8.4, Inertia 3 + React 19 + TS 5, Tailwind 4 + reskinned shadcn/ui, Sanctum 4, Pest, PostgreSQL 17 + PostGIS 3.5.7 (Neon EU), MapLibre GL JS 5 + OpenFreeMap, spatie/laravel-pdf, spatie/laravel-data, Docusaurus, Docker on Hetzner.
- Environments: local docker-compose (postgis container + S3-compatible object storage), staging (Neon branch + R2 staging), production (Hetzner VPS + Neon paid PITR + R2 prod + isolated backup bucket).
- Local object storage: a maintained, publicly available S3-compatible service (RustFS, pinned by digest) reached through Laravel's standard `s3` disk and configured exclusively by `AWS_*` environment variables; production uses Cloudflare R2 with no application change. MinIO was the original choice but its public images were withdrawn, and LocalStack was rejected because its current image is licence-gated and the last free tag does not enforce private buckets.
- Greenfield starter: official Laravel React starter kit (Inertia + React + Tailwind) as the Epic 1 scaffold base.

### Security & Data Protection Foundation Requirements (`docs/security/`)

Every story touching authentication, permissions, tenant-scoped models/migrations, APIs, uploads, exports, or compliance must satisfy the corresponding acceptance criteria defined in `docs/security/`:
- **`docs/security/AUTH.md` (Stories 1.2, 1.3, 1.4, 5.1):** Migrate `users.id` and `sessions.user_id` from integer to UUID (`HasUuids`, `AD-6`); disable/gate public uninvited self-registration (`Features::registration()`) so all users authenticate into an Organisation via an active `memberships` record; enforce 6-role union permissions inside Application Services (`AD-12`); return `404` on out-of-scope tenant/client UUIDs and `403` on unauthorised in-scope mutations.
- **`docs/security/RLS-POLICIES.md` (Stories 1.2, 2.1–5.5, 6.1):** Enforce `ENABLE` + `FORCE ROW LEVEL SECURITY` with fail-closed `NULLIF(current_setting('app.org_id', true), '')::uuid` policy on all **17** tenant tables (`memberships`, `client_accounts`, `properties`, `locations`, `site_zones`, `assets`, `asset_custody`, `inspection_templates`, `inspection_template_items`, `inspections`, `inspection_responses`, `maintenance_requests`, `work_orders`, `work_order_events`, `contractors`, `attachments`, `audit_events`); separate schema-owner DB role (`pai_owner`) from runtime non-owner role (`pai_app` with `NOBYPASSRLS` and `SELECT, INSERT` only on append-only tables); run `set_config('app.org_id', ?, true)` inside a transaction for HTTP requests and `TenantAware` jobs; run Pest tests against PostgreSQL 17 + PostGIS under `pai_app` (never SQLite or superuser).
- **`docs/security/SECURITY.md` & `THREAT-MODEL.md` (Stories 1.10, 1.11, 2.3, 3.5, 3.9, 3.12, 4.3, 6.1, 6.2):** Enforce `finfo` magic-byte upload validation (`10 MB` max, `jpeg`/`png`/`webp`/`pdf` allowlist, `Content-Disposition: attachment` on non-images), 10-minute signed URLs gated by tenant + `client_scope`, 4-tier `RateLimiter` (`429 Retry-After`), pessimistic `lockForUpdate()` on work-order/inspection transitions, sanitized `DomainException` error envelopes, and the 5-vector tenant-isolation test matrix.
- **`docs/security/ENV.md` & `DATA-PROTECTION.md` (Stories 2.3, 4.3, 5.5, 5.6, 6.3, 6.4, 6.6):** Keep `.env.example` placeholder-only; never expose secrets in `VITE_*` or logs; use isolated credentials (`BACKUP_R2_*`) for nightly `pg_dump` backups; enforce NDPA data minimisation (no IDs/biometrics, no residential tenant data, non-blocking optional GPS, outcome-only NIPOST postcode with mandatory disclaimer); and verify DSR/breach logging and retention settings before pilot onboarding.

### UX Design Requirements

From the UX contract (DESIGN.md + EXPERIENCE.md, both status: final; 4 screen specs; docs/design/design-context.md):

UX-DR1: Implement the Cloud Crafters Editorial Intelligence token set in Tailwind 4 `@theme` — full color palette (canvas, navy/blue/teal ramps, 5 badge semantic triples, 3 border tiers), type scale (Georgia editorial body + Arial UI), 4px spacing grid, 0–3px radius (pills banned), 3 elevation levels, 150/200ms motion, breakpoints (≤760px mobile, 761–1023px, ≥1024px desktop 1120px `.cc-page`), z-index layers.
UX-DR2: Build the 21 canonical `.cc-*` components as re-skinned shadcn/ui primitives with the EXPERIENCE.md behavioral contracts: app-shell-header, mobile-quick-task-bar, page-header, recommendation-callout, data-table, editorial-card, button, form-field, select-native, dropdown-editorial, status-badge, tabs, filter-bar, pagination, property-map, evidence-uploader, evidence-gallery, checklist-runner, timeline-feed, async-export-banner, modal-dialog.
UX-DR3: Global chrome — `.cc-skip-link` first focusable element on every page; desktop app-shell-header with Tier-2 client account scope switcher; mobile slide-over nav + bottom quick-task-bar; one primary action per view; max 1-level overlay stacking.
UX-DR4: Implement all 11 operational states on every surface: skeleton, empty first-use, empty no-results, zero-activity report certification, focus-visible (never obscured), field validation error, domain exception alert, rate-limited 429, network-degraded/upload progress + interrupted-retry, GPS-unavailable notice, permission-denied/404-vs-403, immutable/locked record.
UX-DR5: UJ-1 property & asset register screens per `specs/screen-spec-uj1-property-asset-register.md` (mocked hero: PropertyDetail).
UX-DR6: UJ-2 mobile inspection runner per `specs/screen-spec-uj2-mobile-inspection-runner.md` (mocked hero: InspectionRunner with checklist-runner).
UX-DR7: UJ-3 mobile work-order execution per `specs/screen-spec-uj3-mobile-work-order-execution.md` (mocked hero: WorkOrderDetail).
UX-DR8: UJ-4 client report compiler per `specs/screen-spec-uj4-client-report-compiler.md` (mocked hero: ReportCompiler with async-export-banner).
UX-DR9: The 10 spine-only surfaces built directly from DESIGN/EXPERIENCE tables: ClientAccountsIndex, PropertiesIndex, AssetsIndex, AssetDetail, TemplatesIndex, InspectionsIndex, RequestsIndex, WorkOrdersIndex, MembershipsIndex, AuditEventsIndex.
UX-DR10: Voice & copy — calm operational editorial voice; sentence case; no em/en dashes; 3-part error formula; mandatory legal copy (NIPOST disclaimer, immutable-inspection notice, zero-activity certification); banned words list enforced; accountable custodian vs assigned technician terminology.
UX-DR11: Accessibility floor — WCAG 2.2 AA: contrast guarantees (incl. ≥3:1 non-text `#6E7E91` controls), 40px touch targets (36px compact mobile), 16px mobile inputs (no iOS zoom), skip link + scroll-padding, semantic tables with aria-sort, status never by color alone, live-region announcements, keyboard + screen-reader matrix (NVDA/VoiceOver/TalkBack).
UX-DR12: Nigerian mobile resilience — zero external webfonts; client-side photo downscaling ≤1920px WebP/JPEG before upload; paginated payloads (20/page); scoped Inertia partial reloads; <170 KB initial JS on field routes (MapLibre lazy); fixed aspect ratios + row-matched skeletons (CLS < 0.1).
UX-DR13: Banned patterns enforced in review — infinite scroll, optimistic transitions on compliance mutations, hover-only actions, stacked modals, decorative empty-state illustrations, color-only status, placeholder-as-label, icon-only buttons without names.

### FR Coverage Map

FR1: Epic 2 — Client account management
FR2: Epic 2 — Property registration + postcode
FR3: Epic 2 — Canonical location + history
FR4: Epic 2 — Zones
FR5: Epic 2 — Asset register
FR6: Epic 2 — Custody assignment
FR7: Epic 2 — Map display
FR8: Epic 2 — Client asset/custodian listing
FR9: Epic 3 — Inspection scheduling
FR10: Epic 3 — Template management + versioning
FR11: Epic 3 — Inspection conduct, sign-off, immutability
FR12: Epic 3 — Maintenance requests
FR13: Epic 3 — Work order create/assign + status flow
FR14: Epic 3 — Work progress logging
FR15: Epic 3 — Evidence-gated completion
FR16: Epic 3 — Asset timeline reflection
FR17: Epic 4 — Report compilation
FR18: Epic 4 — PDF/CSV export
FR19: Epic 1 (foundation) — tenant resolution; enforced everywhere
FR20: Epic 1 (role substrate) + Epic 5 (full role/scope administration)
FR21: Epic 1 (Evidence service) + Epic 3 (workflow evidence capture)
FR22: Epic 1 (audit substrate) + Epic 5 (audit review surface)
FR23: Epic 5 — NDPA data-protection operations

## Epic List

### Epic 1: Platform Foundations & Design System
A team member can sign in to a tenant-isolated, role-scoped application shell rendered in the Cloud Crafters Editorial Intelligence design system, on desktop and mobile.
**FRs covered:** FR-19, FR-20 (substrate), FR-21 (Evidence service), FR-22 (audit substrate) · NFR-1 (base), NFR-4, NFR-5 · UX-DR1–4, UX-DR10–13 · AD-1–6, AD-13, AD-16–20, AD-22

### Epic 2: Property & Asset Registration (Workflow 1)
An operations manager can onboard a client account, register properties with canonical GPS locations on the map, organise zones, register assets, and assign accountable custodians.
**FRs covered:** FR-1–FR-8 · UX-DR5, UX-DR9 (Registry) · AD-7, AD-8, AD-9

### Epic 3: Inspection & Maintenance (Workflow 2)
A site supervisor can run a checklist inspection on a phone with photo evidence and raise faults; a technician can receive, progress, and complete work orders with mandatory completion evidence.
**FRs covered:** FR-9–FR-16, FR-21 (workflow evidence) · UX-DR6, UX-DR7, UX-DR9 (Inspections + Maintenance) · AD-10, AD-11, AD-13, AD-21

### Epic 4: Client Reporting & Evidence Export (Workflow 3)
An operations manager can compile a client-scoped proof-of-work report and export it as PDF and CSV, including an explicit zero-activity certification.
**FRs covered:** FR-17, FR-18 · UX-DR8, UX-DR9 (Reporting) · AD-14, AD-15

### Epic 5: Organisation Administration, Audit & NDPA Operations
An organisation owner can manage memberships with multi-role sets and client scope, review the append-only audit trail, and execute NDPA data-protection workflows.
**FRs covered:** FR-20 (full), FR-22 (full), FR-23 · UX-DR9 (Tenancy + Audit) · AD-12, AD-18

### Epic 6: Pilot Hardening & Compliance Gate
The platform passes the full tenant-isolation matrix, accessibility audit, backups/restore drill, observability setup, and NDPA pilot-readiness checklist — the gate before the first pilot customer.
**FRs covered:** NFR-1 (full), NFR-2, NFR-3, NFR-6, NFR-7, NFR-8 · AD-19, AD-20, AD-22 (full)

<!-- Repeat for each epic in epics_list (N = 1, 2, 3...) -->

## Epic 1: Platform Foundations & Design System

A team member can sign in to a tenant-isolated, role-scoped application shell rendered in the Cloud Crafters Editorial Intelligence design system, on desktop and mobile.

### Story 1.1: Greenfield scaffold — Laravel React starter kit with typed contract tooling

As a developer,
I want the official Laravel React starter kit scaffolded with Inertia 3, React 19, TypeScript 5, Tailwind 4, Sanctum 4, Pest, Spatie Data + typescript-transformer,
So that every later story builds on a typed, tested, conventional base.

**Acceptance Criteria:**

**Given** an empty repository
**When** the scaffold is complete
**Then** the app boots locally via docker-compose with the postgis/postgis:17 container and a pinned S3-compatible object-storage container, and `php artisan serve` renders the default Inertia page
**And** Pest runs green with a sample feature test, and `spatie/typescript-transformer` generates `resources/js/types/generated` from a sample Data class
**And** PHP 8.4, Laravel 13, Tailwind 4 (@tailwindcss/vite), and shadcn/ui CLI are pinned and committed with lock files
**And** object storage is reached through Laravel's standard `s3` disk via `AWS_*` environment variables only, with tests proving upload, retrieval, deletion, pre-signed URL access and denial of anonymous access to private objects

### Story 1.2: Tenant context and RLS isolation infrastructure

As a platform owner,
I want tenant context resolved only from the authenticated session and enforced by PostgreSQL RLS on every tenant table,
So that no organisation can ever read or write another organisation's rows (FR-19, AD-3/4/5).

**Acceptance Criteria:**

**Given** two organisations A and B with memberships
**When** a user of A authenticates
**Then** middleware resolves a `TenantContext` (organisation_id, user_id, role, client_scope) from the membership and issues `SET LOCAL app.org_id` inside the request transaction
**And** a request body or query containing a forged `organisation_id` is ignored, and a model `creating` hook stamps the context value and aborts on conflict
**And** all 17 tenant tables carry `ENABLE` + `FORCE ROW LEVEL SECURITY` with the `NULLIF(current_setting('app.org_id', true), '')::uuid` policy, the runtime role is a non-owner without `BYPASSRLS`, and a raw SQL cross-tenant query returns zero rows
**And** ordered UUID primary keys (HasUuids) are used on every table (AD-6)

### Story 1.3: Authentication with Sanctum and membership resolution

As a team member,
I want to sign in with email and password and have my organisation and role resolved from my membership,
So that I land inside my organisation's isolated workspace (FR-19, FR-20 substrate).

**Acceptance Criteria:**

**Given** a user with an active membership in an organisation
**When** they submit valid credentials
**Then** a Sanctum SPA session is established and the next request resolves their tenant context
**And** invalid credentials return the 3-part editorial error without revealing which field failed
**And** auth endpoints are rate-limited to 5/min per IP+email with HTTP 429 + `Retry-After` (AD-22)
**And** a suspended/inactive membership cannot authenticate and sees a clear access message

### Story 1.4: Role-ability policy foundation

As an organisation owner,
I want the six Phase 1 roles and client_scope enforced by central service-layer policies,
So that permissions follow the role and one person can hold multiple roles (FR-20 substrate, AD-12).

**Acceptance Criteria:**

**Given** the six roles (Platform admin, Organisation owner, Operations manager, Site supervisor, Technician, Contractor)
**When** any application service method executes
**Then** a central policy verifies the actor's abilities and client_scope before business logic runs, and multi-role memberships inherit the union of permissions
**And** an unauthorised mutation attempt throws a domain exception with a permanent snake_case code mapped to a 403
**And** a record outside the actor's tenant or client_scope resolves as 404 (no UUID existence probing)

### Story 1.5: Design tokens in Tailwind @theme and base typography

As a developer,
I want the Cloud Crafters Editorial Intelligence token set mapped into Tailwind 4 `@theme` with Georgia/Arial system typography,
So that every UI story builds with tokens only and zero raw values (UX-DR1).

**Acceptance Criteria:**

**Given** DESIGN.md's token tables and `docs/cloud_crafters_design_system/cloud-crafters-design-system.css`
**When** the theme is built
**Then** all color, type, spacing (4px grid), radius (0–3px, pills banned), elevation, motion, breakpoint, and z-index tokens exist as Tailwind theme tokens, and no component ships a raw hex or pixel literal
**And** zero external webfonts load (system Arial + Georgia only)
**And** `prefers-reduced-motion: reduce` disables transitions globally

### Story 1.6: Core component library I — layout and content primitives

As a developer,
I want the first batch of canonical components built as reskinned shadcn/ui primitives with their EXPERIENCE.md behavioral contracts,
So that screens assemble from the system instead of ad-hoc markup (UX-DR2).

**Acceptance Criteria:**

**Given** the DESIGN.md visual specs and EXPERIENCE.md contracts
**When** batch I is complete
**Then** these 10 components exist with stories/tests: app-shell-header, page-header, recommendation-callout, data-table, editorial-card, status-badge, tabs, pagination, timeline-feed, modal-dialog
**And** each renders every applicable state (skeleton, empty, error, focus) and passes a keyboard-only pass and axe scan
**And** tabular numerals apply to numeric columns and data-table keeps a sticky first column with scroll shadows on ≤760px

### Story 1.7: Core component library II — interactive and field primitives

As a developer,
I want the second batch of canonical components — the field and workflow primitives — built to contract,
So that inspection, work-order, and reporting screens can be composed without new invention (UX-DR2).

**Acceptance Criteria:**

**Given** the DESIGN.md visual specs and EXPERIENCE.md contracts
**When** batch II is complete
**Then** these 11 components exist with stories/tests: button, form-field, select-native, dropdown-editorial, filter-bar, property-map (MapLibre wrapper with coordinate fallback), evidence-uploader (client-side ≤1920px compression + progressbar + interrupted-retry), evidence-gallery (signed-URL refresh), checklist-runner (40px radiogroups + sync badge), async-export-banner (usePoll), mobile-quick-task-bar
**And** mobile inputs render at 16px (no iOS auto-zoom), touch targets are ≥40px, and each component passes a keyboard-only pass and axe scan

### Story 1.8: Application shell — desktop header, mobile navigation, quick-task bar

As a team member,
I want a consistent application shell with skip link, client account scope switcher on desktop, and slide-over nav plus quick-task bar on mobile,
So that I can navigate any module with one primary action per view (UX-DR3, UX-DR13).

**Acceptance Criteria:**

**Given** an authenticated user
**When** any page loads
**Then** `.cc-skip-link` is the first focusable element, the desktop app-shell-header (>760px) shows organisation, Tier-2 client account scope switcher, module links, and role badge, and switching scope reloads page props with `preserveState: false`
**And** on ≤760px the compact header, slide-over navigation sheet, and bottom mobile-quick-task-bar render with ≥40px targets, hiding the bar when the virtual keyboard opens
**And** navigation links outside the user's role abilities are omitted, and overlays never stack beyond 1 level

### Story 1.9: Operational states and editorial copy system

As a developer,
I want a shared states-and-copy layer implementing all 11 operational states and the editorial voice rules,
So that every screen inherits consistent skeletons, errors, and legal copy (UX-DR4, UX-DR10).

**Acceptance Criteria:**

**Given** the EXPERIENCE.md state table and voice rules
**When** the layer is complete
**Then** all 11 states (skeleton, empty first-use, empty no-results, zero-activity certification, focus-visible, validation error, domain exception alert, rate-limited, network-degraded/interrupted-retry, GPS-unavailable, permission-denied, immutable/locked) exist as reusable patterns with the specified treatments
**And** the 3-part error formula, sentence case, no-em/en-dash rule, banned-words list, and the three legal copy blocks (NIPOST disclaimer, immutable-inspection notice, zero-activity certification) are codified as shared copy components/constants
**And** live-region announcements (role="status"/"alert") fire for async state changes

### Story 1.10: Evidence service and signed-URL gateway

As a site supervisor,
I want photo evidence uploaded to private storage and served only through short-lived signed URLs,
So that proof of work is durable and never publicly accessible (FR-21 substrate, AD-13, AD-22).

**Acceptance Criteria:**

**Given** an authenticated user in scope
**When** they upload a file
**Then** EvidenceService validates magic bytes against the allowlist (jpeg/png/webp/pdf ≤10 MB), writes to R2 at `organisations/{org}/clients/{client}/{owner_type}/{owner_id}/{uuid}.{ext}`, and persists provenance (uploaded_by, uploaded_at, client_account_id, polymorphic owner, optional coordinates)
**And** read access is only via 10-minute signed URLs issued after tenant + client_scope verification, with no public or guessable URL
**And** a signed-URL request for another tenant's or client's attachment is denied, and non-image URLs set `Content-Disposition: attachment`

### Story 1.11: Audit service and structured error envelope

As a platform owner,
I want sensitive operations recorded atomically in an append-only audit trail and all errors returned in one structured envelope,
So that the platform is reviewable and never leaks raw errors (FR-22 substrate, AD-17/18).

**Acceptance Criteria:**

**Given** a sensitive operation (auth event, export, role/scope change, deletion, NDPA request)
**When** it executes
**Then** `AuditService::record()` writes the event (actor, action, target, metadata, timestamp) inside the same transaction, and the runtime role has no UPDATE/DELETE on `audit_events`
**And** domain exceptions carry permanent snake_case codes rendered as `{ error: { code, message } }` on REST and mapped form errors on Inertia, with raw database exceptions sanitized to Sentry
**And** structured JSON logs include org_id, user_id, request_id and never personal data or credentials

## Epic 2: Property & Asset Registration (Workflow 1)

An operations manager can onboard a client account, register properties with canonical GPS locations on the map, organise zones, register assets, and assign accountable custodians.

### Story 2.1: Client account service, migration, and REST endpoints

As an operations manager,
I want client accounts stored with full contact details and managed through a service layer,
So that customer records are consistent across web and API (FR-1).

**Acceptance Criteria:**

**Given** an authenticated operations manager
**When** they create a client account with company name, primary contact, email/phone, address, status, and internal notes
**Then** `ClientAccountService` persists it with tenant stamping, and `GET/POST /api/v1/...` equivalents return the `{ data, meta }` envelope
**And** no invoicing, contract, CRM, or client-login fields exist
**And** a forged `organisation_id` in any payload is ignored, and a direct service-layer test proves cross-tenant writes fail

### Story 2.2: ClientAccountsIndex screen

As an operations manager,
I want a client accounts index with search, status filter, and create/edit modals,
So that I can manage customers from one editorial table (FR-1, UX-DR9).

**Acceptance Criteria:**

**Given** Story 2.1's endpoints and the DESIGN/EXPERIENCE spine tables
**When** the screen renders
**Then** it shows a filter-bar (debounced search + status select), paginated data-table (20/page, tabular numerals), and a single primary `New client account` action opening modal-dialog with form-field validation (3-part errors, input preserved)
**And** skeleton, empty first-use, empty no-results, validation-error, and permission-denied states all render per spec, with copy from the shared copy system
**And** the screen passes a keyboard-only pass, axe scan, and mobile ≤760px check with sticky first column

### Story 2.3: Property and location service with canonical geometry

As an operations manager,
I want properties registered with a canonical PostGIS geometry and append-only location history,
So that coordinates never drift and re-surveys preserve history (FR-2, FR-3, AD-7/8).

**Acceptance Criteria:**

**Given** the PostGIS spike has confirmed `CREATE EXTENSION postgis` on the Neon plan (OQ-1 closed) 
**When** a property is created with address, site code, optional postcode, and coordinates
**Then** `LocationService::setLocation()` writes `ST_SetSRID(ST_MakePoint(lng, lat), 4326)` and appends a `locations` row (accuracy, source, captured_by, captured_at) in one transaction
**And** lat/lng serialize only via `ST_Y(geom)`/`ST_X(geom)`, `properties.geom` is guarded from mass assignment, and the postcode stores entered string + status only — server-side, feature-flagged, outcome-only, never blocking save
**And** distances compute only via `Geo::distanceMetres()`/`withinMetres()` using the geography cast (metres)

### Story 2.4: PropertiesIndex screen — map and table split view

As an operations manager,
I want a properties index combining a map of pins with a paginated table,
So that I can locate any site visually or by search (FR-2, FR-7, UX-DR9).

**Acceptance Criteria:**

**Given** Story 2.3's endpoints
**When** the screen renders
**Then** `<PropertyMap>` shows scoped property pins (clicking a pin highlights its row and opens a compact summary card) beside a paginated data-table with filter-bar
**And** if map tiles fail on a weak connection, the surface degrades to a structured coordinate/accuracy table without blocking navigation
**And** MapLibre is lazy-loaded only on this route (initial JS budget holds), and all index states render per spec

### Story 2.5: PropertyDetail screen — dossier with map, postcode badge, and location history (UJ-1 hero)

As an operations manager,
I want a property dossier showing the canonical pin, NIPOST badge with disclaimer, location history, zones, and linked records,
So that a site's full registration state is visible in one place (FR-2/3/4/7, UX-DR5, `specs/screen-spec-uj1-property-asset-register.md`).

**Acceptance Criteria:**

**Given** the UJ-1 screen spec and mock
**When** the screen renders
**Then** it matches the spec's layout: header with `Postcode verified (NIPOST)` badge + mandatory non-title disclaimer, `<PropertyMap>` pin, Location history timeline-feed (append-only, newest first, full provenance), and tabs for Site zones, Assets, Inspections, Work orders
**And** `Update GPS location` opens modal-dialog with pin drag + explicit lat/lng inputs + `Use current device GPS` (SC 2.5.7), and saving prepends a history row without overwriting prior readings
**And** every state in the spec's matrix is reachable, copy matches the copy deck, and the screen passes the dev DoD (keyboard, axe, 320px–1440px)

### Story 2.6: Zones and assets services with uniqueness constraints

As an operations manager,
I want zones nested under properties and assets registered with per-property unique tags,
So that the register stays organised and tag collisions are impossible (FR-4, FR-5, AD-21).

**Acceptance Criteria:**

**Given** a registered property
**When** zones and assets are created via `ZoneService`/`AssetService`
**Then** zones nest under exactly one property, and assets carry tag, category, condition, status, optional make/model/serial, and optional current zone
**And** a composite unique index on `(organisation_id, property_id, tag)` rejects duplicate asset tags with a domain exception
**And** all writes verify client_scope, and service-layer tests cover the cross-client denial

### Story 2.7: Custody service with append-only handover

As an operations manager,
I want asset custody assigned and handed over as append-only history with scope checks,
So that accountability is auditable and the custodian is never confused with the technician (FR-5, FR-6, FR-8, AD-8).

**Acceptance Criteria:**

**Given** a registered asset
**When** a custodian is assigned or handed over
**Then** a new `asset_custody` row appends (latest = current custodian), the handover reason is recorded, and assigning a user whose client_scope excludes the asset's client account is rejected with a 422 explaining the rule
**And** custody rows are immutable to the runtime role (no UPDATE/DELETE)
**And** the scoped client asset/custodian listing query returns only the selected Client Account's rows (FR-8)

### Story 2.8: AssetsIndex screen and custody handover modal

As an operations manager,
I want an asset register with custody assignment and handover flows,
So that every asset shows its accountable custodian and I can rotate custody cleanly (FR-5/6/8, UX-DR9).

**Acceptance Criteria:**

**Given** Stories 2.6–2.7's endpoints
**When** the screen renders
**Then** it shows the scoped register (tag, category, condition, status, zone, accountable custodian) with filter-bar and pagination, and `Register asset` opens a form with Tier-1 selects plus Tier-2 custodian dropdown-editorial
**And** `Handover custody` opens modal-dialog showing current custodian, new custodian combobox, reason field, and confirmation verb, then prepends the handover node to the timeline
**And** terminology is exact (`Accountable custodian`, never conflated with assigned technician), and all states render per spec

### Story 2.9: AssetDetail screen — specification sheet and dynamic timeline

As an operations manager,
I want an asset detail page with its specification and unified history timeline,
So that I can see condition, custody, and linked work in one dossier (FR-16 read path, UX-DR9).

**Acceptance Criteria:**

**Given** the timeline read model from Story 2.10 (implement 2.10 before this story)
**When** the screen renders
**Then** it shows the asset spec (editorial-card), `Handover custody` trigger, and a timeline-feed aggregating custody handovers, inspections, work orders, and evidence with semantic badges, actor + role, and WAT timestamps
**And** evidence thumbnails render via evidence-gallery with provenance modal and automatic signed-URL refresh
**And** all spine states render per spec and the screen passes the dev DoD

### Story 2.10: Property/asset timeline read model and history endpoint

As a developer,
I want the asset timeline computed as a read model across inspections, work order events, custody, and attachments,
So that history is always consistent and never duplicated (FR-16, FR-8, AD-9).

**Acceptance Criteria:**

**Given** completed work orders, custody rows, and attachments for an asset
**When** the timeline is queried
**Then** `Registry` compiles it dynamically (no `timeline_events` table), ordered descending, scoped by tenant + client_scope
**And** `GET /api/v1/properties/{id}/history` returns the same structure in the `{ data, meta }` envelope
**And** service-layer tests prove cross-tenant and cross-client timeline access returns empty/404

## Epic 3: Inspection & Maintenance (Workflow 2)

A site supervisor can run a checklist inspection on a phone with photo evidence and raise faults; a technician can receive, progress, and complete work orders with mandatory completion evidence.

### Story 3.1: Inspection template service with row versioning

As an operations manager,
I want reusable inspection templates with sections, typed questions, and automatic versioning once used,
So that historical inspections never change when templates evolve (FR-10, AD-10/21).

**Acceptance Criteria:**

**Given** an authorised user
**When** they create or edit a template
**Then** templates hold sections and items with answer types (Pass/Fail/N/A, Yes/No, notes) and optional association to property type, asset category, or inspection type
**And** editing a template referenced by any inspection creates a new row with incremented `version` under the same `template_family_id` (composite unique index org+family+version), while unused drafts update in place
**And** service-layer tests prove a v2 inspection still renders v2 after v3 exists

### Story 3.2: TemplatesIndex screen

As an operations manager,
I want a template index showing families, versions, and edit flows,
So that checklist management is self-service (FR-10, UX-DR9).

**Acceptance Criteria:**

**Given** Story 3.1's endpoints
**When** the screen renders
**Then** it shows templates with version badges and category/type, `Edit template` for structure editing, and clear indication of which version is active
**And** saving an edit to a referenced template shows the version-increment outcome (v3 created, v2 preserved) in the UI
**And** all spine states render and the screen passes the dev DoD

### Story 3.3: Inspection scheduling and lifecycle service

As an operations manager,
I want inspections scheduled against a property and pinned to an exact template version,
So that field staff always run the intended checklist (FR-9, AD-10).

**Acceptance Criteria:**

**Given** a property and an active template
**When** an inspection is scheduled with assignee and date
**Then** it permanently binds that `inspection_template_id` and is scoped to tenant + client_scope
**And** `POST /api/v1/inspections` and `GET /api/v1/inspections/{id}` mirror the web flow through the same service
**And** scheduling for a property outside the actor's scope returns 404

### Story 3.4: InspectionsIndex screen

As a site supervisor,
I want an inspections index filtered by scheduled, in-progress, and approved,
So that I can find today's work from my phone (FR-9/11, UX-DR9).

**Acceptance Criteria:**

**Given** Story 3.3's endpoints
**When** the screen renders
**Then** it shows a filter-bar and paginated table with status badges and template version, plus `Schedule inspection` (property + Tier-2 template dropdown with version + assignee + date)
**And** approved inspections display the immutable notice, and mobile rendering keeps ≥40px targets
**And** all spine states render and the screen passes the dev DoD

### Story 3.5: Inspection response capture service with immutability

As a site supervisor,
I want checklist responses, conditions, and photos captured against the pinned template, with approved inspections immutable,
So that inspection records are tamper-proof (FR-11, AD-10).

**Acceptance Criteria:**

**Given** a scheduled inspection
**When** responses and per-asset conditions are saved
**Then** they persist against the pinned template version, and supervisor sign-off stamps `approved_at` and rejects all further updates
**And** a correction creates a new draft inspection linked via `supersedes_id`, with both records showing the supersede chain
**And** `POST /api/v1/inspections/{id}/complete` enforces the same rules, and approval uses a pessimistic lock (AD-21)

### Story 3.6: InspectionRunner screen — checklist execution with evidence and sign-off (UJ-2 hero)

As a site supervisor,
I want a mobile-first checklist runner with autosave, photo capture, inline fault raising, and sign-off,
So that a full site walkthrough completes on a phone over a flaky network (FR-11/12/21, UX-DR6, `specs/screen-spec-uj2-mobile-inspection-runner.md`).

**Acceptance Criteria:**

**Given** the UJ-2 screen spec and mock
**When** the screen renders
**Then** checklist-runner renders the pinned template with 40px `role="radiogroup"` Pass/Fail/N/A (or Yes/No) buttons, optional notes, autosave with live sync badge (`All N responses saved` / `Offline: tap to retry sync`), and per-item evidence-uploader (client-side compression, progressbar, interrupted-retry)
**And** marking Fail reveals the inline `Raise maintenance request` drawer pre-linked to property/asset/inspection/failed question, with Tier-1 priority select and Tier-2 technician combobox
**And** `Approve & sign off inspection` confirms via modal-dialog then locks all inputs with the immutable notice and `Create corrected version` action; GPS-denied evidence announces the polite status strip and never blocks
**And** every state in the spec's matrix is reachable and the screen passes the dev DoD at ≤390px

### Story 3.7: Maintenance request service

As a site supervisor,
I want maintenance requests raised from inspections or directly with full linkage,
So that faults are tracked from discovery to work order (FR-12, AD-2).

**Acceptance Criteria:**

**Given** a failed checklist item or a standalone request
**When** the request is created
**Then** `MaintenanceService` records the fault description, optional priority, and link to the originating inspection, via the owning module's service (Inspections never writes `maintenance_requests` directly)
**And** requests carry tenant + client_account stamping and are scope-checked on read

### Story 3.8: RequestsIndex screen — triage and convert

As an operations manager,
I want a requests triage list where I can convert requests into assigned work orders,
So that nothing raised in the field dies in a queue (FR-12/13, UX-DR9).

**Acceptance Criteria:**

**Given** Story 3.7's endpoints
**When** the screen renders
**Then** it lists requests with origin badges (inspection-linked vs direct) and status, and `Convert to work order` opens the assignment flow (technician/contractor Tier-2 combobox, priority)
**And** all spine states render and the screen passes the dev DoD

### Story 3.9: Work order service and state machine

As an operations manager,
I want work orders created from requests or directly and transitioned only through a guarded state machine,
So that status is always truthful (FR-13/14/15, AD-11/21).

**Acceptance Criteria:**

**Given** a work order targeting a property (optionally an asset)
**When** transitions occur
**Then** they execute only via `WorkOrderService::transition()` along Open → Assigned → In Progress → Completed plus On Hold/Cancelled (with On Hold → In Progress), each appending a `work_order_events` row under a pessimistic lock
**And** progress logging captures work_performed, outstanding_work, and delay_reason without completing; completion strictly requires ≥1 timestamped photo, while missing GPS never blocks
**And** only the assigned technician or an authorised supervisor completes (domain exception otherwise), and `POST /api/v1/work-orders/{id}/assign|complete` mirror the rules

### Story 3.10: WorkOrdersIndex screen

As a technician,
I want my assigned work orders filterable by status,
So that I always know my queue on site (FR-13, UX-DR9).

**Acceptance Criteria:**

**Given** Story 3.9's endpoints
**When** the screen renders
**Then** it shows a status-filtered paginated table with property/zone/asset context and priority, technicians and contractors seeing only their assigned work within client_scope
**And** mobile `My work orders` entry renders the same surface with ≥40px targets
**And** all spine states render and the screen passes the dev DoD

### Story 3.11: WorkOrderDetail screen — progress logging and evidence-gated completion (UJ-3 hero)

As a technician,
I want a mobile work-order dossier with honest partial-progress logging and photo-gated completion,
So that I never have to falsely close a job (FR-13–16/21, UX-DR7, `specs/screen-spec-uj3-mobile-work-order-execution.md`).

**Acceptance Criteria:**

**Given** the UJ-3 screen spec and mock
**When** the screen renders
**Then** it shows fault origin link, property/zone/asset, assignment, evidence-gallery of origin photos (signed URLs with provenance), the progress form (status select + work performed + outstanding work + delay reason), and `work_order_events` timeline-feed
**And** `Record progress / place on hold` and `Complete work order` are visually and behaviorally distinct; completion with zero photos highlights evidence-uploader with the 3-part alert and blocks until upload succeeds, and GPS timeout announces `GPS unavailable · recorded without coordinates` without blocking
**And** a wrong-assignee view disables completion with the policy explanation, and a completed order locks with the immutable treatment
**And** every state in the spec's matrix is reachable and the screen passes the dev DoD at ≤390px

### Story 3.12: Workflow evidence integration with post-completion immutability

As a platform owner,
I want evidence captured in inspections and work orders to flow through the evidence service and become immutable after approval/completion,
So that proof of work cannot be altered after the fact (FR-21, AD-11/13).

**Acceptance Criteria:**

**Given** the evidence service from Story 1.10
**When** photos are uploaded inside InspectionRunner or WorkOrderDetail
**Then** they persist with provenance (uploader, timestamp, owner entity, optional coordinates), and upload endpoints are rate-limited 30/min per user (AD-22)
**And** once the parent inspection is approved or the work order completed, delete actions are removed and server-side deletion is rejected
**And** integration tests cover capture in both workflows plus the immutability gate

## Epic 4: Client Reporting & Evidence Export (Workflow 3)

An operations manager can compile a client-scoped proof-of-work report and export it as PDF and CSV, including an explicit zero-activity certification.

### Story 4.1: Report read model — canonical ReportData aggregation service

As an operations manager,
I want one canonical report data structure aggregating a client's period activity,
So that preview, PDF, and CSV always tell the same story (FR-17, AD-15).

**Acceptance Criteria:**

**Given** a client account and a reporting period
**When** `ReportService::compile($clientAccount, $period)` runs
**Then** it returns a single `ReportData` object containing client + period, properties covered, inspections/visits completed, work orders grouped by status, outstanding maintenance issues, asset condition summary, and evidence references — strictly scoped to that client account
**And** a zero-activity period returns an explicit certification payload (0 inspections, 0 work orders, current property/asset counts), never an empty structure
**And** service-layer tests prove another client account's records never appear, and `GET /api/v1/reports` mirrors the same read model

### Story 4.2: ReportCompiler screen — period selection and editorial preview (UJ-4 hero)

As an operations manager,
I want to select a client and period and review a compiled editorial report preview on screen,
So that I can verify the proof of work before exporting (FR-17, UX-DR8, `specs/screen-spec-uj4-client-report-compiler.md`).

**Acceptance Criteria:**

**Given** the UJ-4 screen spec and mock
**When** the screen renders
**Then** client account + date range selection compiles the preview synchronously, rendering executive summary cards, properties covered, completed inspections table, work orders by status, asset condition summary, and embedded evidence-gallery thumbnails — on the 1120px editorial canvas
**And** a zero-activity period renders the formal `.cc-callout` certification block naming the client, period, and current register counts (never a blank page)
**And** every state in the spec's matrix is reachable, copy matches the copy deck, and the screen passes the dev DoD

### Story 4.3: Async export pipeline — PDF and CSV renderers with signed URLs

As an operations manager,
I want report exports generated asynchronously and delivered via short-lived signed links,
So that large reports never time out and files stay private (FR-18, AD-14/15/21/22).

**Acceptance Criteria:**

**Given** a compiled ReportData
**When** the user triggers export
**Then** a unique TenantAware job (ShouldBeUnique keyed by org+client+period) re-establishes tenant context, renders the PDF via spatie/laravel-pdf + Chromium and the CSV from the same ReportData, writes both to R2, and records a `report.exported` audit event
**And** the PDF worker runs at concurrency = 1, exports are rate-limited to 5/min per organisation with 429 + Retry-After, and downloads are 10-minute signed URLs
**And** job failure lands in `failed_jobs` with a Sentry alert and a retry path, and unscoped job execution is fatal

### Story 4.4: ReportCompiler export UX — async banner and recent exports

As an operations manager,
I want live feedback while exports compile and a list of recent exports to re-download,
So that I never wonder whether the export worked (FR-18, UX-DR8).

**Acceptance Criteria:**

**Given** the export pipeline from Story 4.3
**When** `Export PDF & CSV` is clicked
**Then** async-export-banner shows Queued/Compiling (live region), polls via `usePoll(3000)`, then transitions to Ready with `Download PDF` / `Download CSV` signed-link buttons or Failed with retry
**And** the Recent exports table prepends each completed export with timestamp and links
**And** the 429 rate-limit state renders the warning banner with cooldown, and polling stops on Ready/Failed

### Story 4.5: PDF report template — editorial print layout with evidence images

As a client,
I want the exported PDF to read as a clean editorial dossier with readable evidence images,
So that the document itself is proof of the service I'm paying for (FR-18, UX-DR10).

**Acceptance Criteria:**

**Given** the canonical ReportData
**When** the PDF renders
**Then** it uses the design-system print aesthetic (Georgia/Arial, navy/teal palette, sentence case, WAT timestamps, ₦ and metre formatting) and embeds evidence images at readable sizes with provenance captions
**And** the CSV export contains structured records and evidence references rather than a visual reproduction
**And** the zero-activity PDF carries the same certification statement as the screen, and the NIPOST disclaimer appears wherever a verified postcode badge is shown

## Epic 5: Organisation Administration, Audit & NDPA Operations

An organisation owner can manage memberships with multi-role sets and client scope, review the append-only audit trail, and execute NDPA data-protection workflows.

### Story 5.1: Membership administration service — roles, client scope, last-owner guard

As an organisation owner,
I want to assign staff multi-role sets and bind their client_scope,
So that each person sees exactly the clients they serve (FR-19/20, AD-12).

**Acceptance Criteria:**

**Given** an organisation owner
**When** they edit a membership
**Then** role sets (any combination of the six roles) and client_scope (unrestricted null or specific Client Account UUIDs) persist through `MembershipService`, with permission union taking effect immediately
**And** removing the last active Organisation owner role is rejected with domain code `cannot_remove_last_organisation_owner`
**And** every role/scope change writes an atomic audit event, and scoped-out users receive 404 on out-of-scope records

### Story 5.2: MembershipsIndex screen

As an organisation owner,
I want a members table with role and scope editing,
So that access management is transparent (FR-20, UX-DR9).

**Acceptance Criteria:**

**Given** Story 5.1's endpoints
**When** the screen renders
**Then** it lists memberships with roles and scope badges, and `Edit membership` opens modal-dialog with multi-role selection and Tier-2 client-scope binding
**And** the last-owner rejection renders the State 6 domain alert preserving the change
**And** all spine states render and the screen passes the dev DoD

### Story 5.3: Audit query service

As an organisation owner,
I want the append-only audit trail queryable by actor, action, and date range,
So that sensitive operations are reviewable (FR-22, AD-18).

**Acceptance Criteria:**

**Given** recorded audit events
**When** the owner queries by actor, action type, or Africa/Lagos date range
**Then** results return paginated from `audit_events` with full metadata, scoped to the organisation
**And** the runtime role cannot update or delete any audit row (enforced at the database)

### Story 5.4: AuditEventsIndex screen

As an organisation owner,
I want a filterable audit log view,
So that compliance review is self-service (FR-22, UX-DR9).

**Acceptance Criteria:**

**Given** Story 5.3's endpoints
**When** the screen renders
**Then** it shows filter-bar (actor, action, date range) over a paginated data-table with WAT timestamps and metadata detail
**And** all spine states render and the screen passes the dev DoD

### Story 5.5: NDPA data-protection service

As an organisation owner,
I want data-subject requests, breach notifications, and retention actions recorded and executable,
So that the platform meets its NDPA processor obligations (FR-23, AD-8/14).

**Acceptance Criteria:**

**Given** a data-subject request or breach event
**When** it is logged
**Then** the service records the request type (access/rectification/deletion), subject, dates, and resolution, plus the 72-hour breach notification contact record — each with an atomic audit event
**And** retention/deletion execution runs only through the audited tenant-aware administrative job (elevated privileges, never the runtime role)
**And** retention duration config reads from a single documented setting (value pending OQ-7)

### Story 5.6: Audit NDPA panel — DSR and breach logging workflows

As an organisation owner,
I want NDPA logging workflows inside the audit surface,
So that compliance actions take minutes, not projects (FR-23, UX-DR9).

**Acceptance Criteria:**

**Given** Story 5.5's endpoints
**When** the panel renders
**Then** `Log NDPA data-subject request` opens modal-dialog capturing request type, subject, and notes, and breach notification entries record the contact and timeline
**And** a retention policy summary displays the configured periods (flagged pending until OQ-7 closes)
**And** all spine states render and the screen passes the dev DoD

## Epic 6: Pilot Hardening & Compliance Gate

The platform passes the full tenant-isolation matrix, accessibility audit, backups/restore drill, observability setup, and NDPA pilot-readiness checklist — the gate before the first pilot customer.

### Story 6.1: Tenant-isolation test matrix as a CI gate

As a platform owner,
I want the 5-vector isolation matrix for every core entity running in CI,
So that no merge or deploy can ship a cross-tenant leak (NFR-1, AD-19).

**Acceptance Criteria:**

**Given** all core entities (client_accounts, properties, locations, site_zones, assets, asset_custody, inspection_templates, inspections, maintenance_requests, work_orders, contractors, attachments, audit_events)
**When** the matrix runs
**Then** each entity is verified across HTTP requests, direct service calls, raw SQL, queued jobs, and signed URL generation — cross-tenant access is rejected and forged `organisation_id` headers/payloads are ignored
**And** tests run against real PostgreSQL 17 + PostGIS as the non-owner role (never SQLite, never superuser), and the suite blocks merge/deploy on failure

### Story 6.2: Security hardening pass

As a platform owner,
I want headers, rate limits, and upload hardening verified end to end,
So that the pilot starts from a hardened baseline (NFR-1, AD-22).

**Acceptance Criteria:**

**Given** the deployed application
**When** the pass executes
**Then** HSTS, nosniff, X-Frame-Options DENY, Referrer-Policy, and the CSP (self + R2 + OpenFreeMap origins) are verified on responses
**And** all four rate-limit tiers (auth 5/min, uploads 30/min, exports 5/min/org, API 120/min) demonstrably return 429 + Retry-After, and magic-byte upload verification rejects spoofed extensions and oversized files
**And** a secrets audit confirms no key exists in client code or git history (any finding rotated)

### Story 6.3: Backups, restore drill, and retention configuration

As a platform owner,
I want automated backups with a tested restore and configured retention,
So that data loss and NDPA retention are handled before customers arrive (NFR-3, NFR-7, AD-20).

**Acceptance Criteria:**

**Given** production data
**When** the drill runs
**Then** Neon PITR plus nightly portable `pg_dump` to the isolated R2 backup bucket are both verified, and a restore to staging completes successfully within the documented RTO/RPO
**And** retention periods are configured from the single documented setting (OQ-7 value), with the administrative pruning job audited and tenant-aware
**And** the restore procedure is documented and dated

### Story 6.4: Observability setup

As a platform owner,
I want error tracking, uptime monitoring, and an alert runbook live,
So that incidents are seen and answered before clients notice (NFR-5).

**Acceptance Criteria:**

**Given** the production deployment
**When** observability is configured
**Then** Sentry captures frontend and backend errors with org_id/user_id/request_id context (never personal data or credentials), uptime monitoring with alerts is live, and structured JSON logs flow to the log store
**And** an alert runbook documents who gets paged, first actions, and escalation, and a test alert has fired end to end

### Story 6.5: Accessibility audit across all surfaces

As a platform owner,
I want a WCAG 2.2 AA audit of every shipped surface with fixes verified,
So that accessibility ships as the hard requirement it is (NFR-4).

**Acceptance Criteria:**

**Given** all built screens
**When** the audit runs per the enterprise-ui-ux REVIEW mode and design-review-rubric
**Then** every surface passes contrast (text and non-text), keyboard-only flows, focus management, screen-reader spot checks (NVDA + VoiceOver + TalkBack at 320px and 200% zoom), and axe/Lighthouse scans
**And** any Blocker or Major finding fails the story until fixed and re-verified
**And** the audit report is committed with the sign-off date

### Story 6.6: Pilot-readiness checklist

As a platform owner,
I want the NDPA and operational checklist completed and evidenced,
So that the first pilot onboards on solid ground (NFR-2/6/7/8).

**Acceptance Criteria:**

**Given** the near-complete platform
**When** the checklist executes
**Then** the controller/processor DPA is signed, data minimisation and purpose/retention documentation is published, the breach-notification path is tested, the transfer basis is documented, and the data-subject request process is walkthrough-tested
**And** performance verification confirms LCP < 2.5s and p95 API < 500ms targets on reference devices/networks, billing/usage alerts are configured, and the monthly cost report is within the guardrail
**And** open questions OQ-1 (PostGIS), OQ-2 (map licensing), OQ-4 (NDPC registration), and OQ-6 (Cloud Policy) each have a recorded resolution or owner + date
