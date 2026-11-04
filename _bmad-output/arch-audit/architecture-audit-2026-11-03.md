---
title: Enterprise Application Architecture Audit — Phase 3
project: Property & Asset Intelligence
date: 2026-11-03
status: final
target_spine: _bmad-output/planning-artifacts/architecture/architecture-business-intelligence-2026-11-03/ARCHITECTURE-SPINE.md
registry: _bmad-output/arch-audit/flag-registry.md
---

# Enterprise Application Architecture Audit: Property & Asset Intelligence (Phase 1 MVP)

## Executive Summary

| Dimension | Score (0–100) | One-line rationale |
| --- | --- | --- |
| **Overall architecture** | **93 / 100** | Clean modular monolith (`Adapters → Application Services → Domain → Infrastructure`) with 22 enforceable Architectural Decisions (AD-1..AD-22) and zero microservices bloat. |
| **Production readiness** | **86 / 100** | Solid operational design (Docker on Hetzner, Neon PG17+PostGIS, R2 backups, Sentry, RLS); pending Week-1 Neon PostGIS dedicated-schema spike, backup restore drill, and NDPA retention parameters. |
| **Scalability** | **84 / 100** | Well-sized for MVP/pilot (5–30 sites per FM org, 50–1,000 users); single 4GB VPS + DB queue + Chromium PDF worker (concurrency=1) is a known ceiling at 1,000+ concurrent report exports, with clean seam to Redis + horizontal workers. |
| **Security** | **95 / 100** | Defense-in-depth multi-tenancy: session-only `TenantContext`, non-owner DB role with `FORCE ROW LEVEL SECURITY`, transaction-scoped `SET LOCAL app.org_id`, automatic `ClientScope`, magic-byte upload checks, and 10-min signed R2 URLs. |
| **Cost efficiency** | **96 / 100** | Verified ~$35–55/mo production footprint ($8–15/mo dev/staging) using Hetzner Germany + Neon EU Central + Cloudflare R2 zero-egress storage + OpenFreeMap tiles. |

---

## Scope Clarification

- **Frontend scope:** Full-stack responsive web application built with **React 19 + TypeScript 5 + Inertia.js 3 + Tailwind CSS 4 + re-skinned shadcn/ui** (`docs/cloud_crafters_design_system/` as visual SoT) + **MapLibre GL JS 5** wrapping OpenFreeMap tiles. Includes client-side image compression/downscaling (max 1920px) for Nigerian mobile networks. No native mobile app, offline sync, or client self-service portal in Phase 1.
- **Backend scope:** Owned **Laravel 13 (PHP 8.4) modular monolith** serving both Inertia web routes and versioned first-party `/api/v1` REST endpoints over a shared application service layer (`Tenancy`, `Registry`, `Inspections`, `Maintenance`, `Evidence`, `Reporting`, `Audit`).
- **Third-party services handling layers:**
  - **Database:** Neon Serverless PostgreSQL 17 + PostGIS 3.5.7 (EU Central, Frankfurt) with PgBouncer transaction pooling.
  - **Object Storage & Backups:** Cloudflare R2 (private bucket for evidence/exports + isolated secondary bucket with separate credentials for nightly `pg_dump` backups).
  - **Compute / Hosting:** Hetzner Cloud VPS (Germany) running a single Docker image (`web`, `queue worker`, `scheduler`) behind Caddy TLS.
  - **Map Tiles:** OpenFreeMap vector tiles.
  - **Observability:** Sentry (FE + BE error tracking) + UptimeRobot/Better Stack uptime checks.
- **Review focus:** Full 17-layer architectural review across both `[FE]` and `[BE]` plus Cross-Cutting Concurrency and AI/Portability guardrails.

---

## Critical Risks

All Architecture-owned **P0** risks discovered during the Phase 3 review and adversarial stress-testing were resolved directly in `ARCHITECTURE-SPINE.md` (AD-1 through AD-22) before finalizing the spine. Remaining **P1** items travel forward as mandatory story-gate or pre-pilot constraints in `_bmad-output/arch-audit/flag-registry.md`.

| Flag ID | Priority | Layer Affected | Risk Description & Potential Impact | Resolution / Recommended Fix | Status |
| --- | --- | --- | --- | --- | --- |
| **F-301** | **P0** | Layer 4 & Layer 8 `[BE]` | **Unauthenticated login deadlock vs RLS & PgBouncer `SET LOCAL` leakage:** Blanket RLS on `users`/`organisations` blocks login before `app.org_id` is known; conversely, running `SET LOCAL` outside an explicit transaction on Neon's PgBouncer pooler silently drops tenant binding or leaks across pooled connections. | Explicitly split **System Identity Tables** (`users`, `organisations` — no RLS) from **Tenant Tables** (17 tables — `FORCE ROW LEVEL SECURITY` mandatory) and mandate a middleware transaction wrapper around `SET LOCAL app.org_id = ?` using a non-owner DB role (`AD-4`). | ✅ **RESOLVED** (`AD-4`) |
| **F-302** | **P0** | Layer 4 & Layer 8 `[BE]` | **Intra-tenant Client Account leakage on nested queries:** Postgres RLS scopes only by `organisation_id`. A technician or supervisor restricted via `memberships.client_scope` could access another client's properties, work orders, or inspections if a developer omits manual `whereIn` clauses. | Denormalize immutable `client_account_id` onto all 7 client-owned tables and enforce an automatic Eloquent global `ClientScope` whenever `TenantContext::clientScope()` is non-null (`AD-5`). | ✅ **RESOLVED** (`AD-5`) |
| **F-303** | **P0** | Layer 2 & Layer 8 `[BE]` | **Cross-module Eloquent relationship write bypass & post-completion evidence deletion:** Calling `$workOrder->attachments()->create()` bypasses MIME/size/path validation, and deleting an attachment after work order completion invalidates audit proof of work (FR-15). | Enforce read-only cross-module Eloquent relations (`AD-2`) and make attachments strictly immutable in `EvidenceService` once the parent work order is `completed` or inspection is `approved` (`AD-11`, `AD-13`). | ✅ **RESOLVED** (`AD-2`, `AD-11`, `AD-13`) |
| **F-304** | **P0** | Layer 3 & Layer 2 `[BE]` | **Coordinate drift & missing initial location history:** Mass-assigning `properties.geom` on creation skips writing the initial `locations` history row (breaking FR-3) or risks lat/lng degree vs metre bugs. | Guard `properties.geom` from mass assignment; force initial creation and updates through `LocationService::setLocation()` and restrict all distance math to `Geo::distanceMetres()`/`withinMetres()` using `::geography` (`AD-7`). | ✅ **RESOLVED** (`AD-7`) |
| **F-305** | **P0** | Layer 9, Layer 15 & Cross-Cutting `[BE]` | **Concurrent state transition races, duplicate PDF worker OOMs, and unthrottled endpoints:** Simultaneous requests to `WorkOrderService::transition()` can duplicate events; repeated PDF export clicks can OOM the 4GB VPS via headless Chromium; spoofed file extensions risk stored XSS. | Added `AD-21` (`lockForUpdate()` on work order transitions & inspection approvals, composite unique indexes on asset tags and template versions, `ShouldBeUnique` on queued jobs) and `AD-22` (4-tier `RateLimiter` returning `429 Retry-After`, `finfo` magic-byte MIME check, CSP/HSTS headers). | ✅ **RESOLVED** (`AD-21`, `AD-22`) |
| **F-306** | **P1** | Layer 3 & Layer 10.5 `[BE]` | **Neon PostGIS dedicated-schema extension compatibility (PRD OQ#1 / D1):** Assuming `CREATE EXTENSION postgis` works identically in a custom schema without running the Week-1 verification spike could force migration rework. | Execute Week-1 Story Gate spike (`F-401`) on Neon EU Central Postgres 17 + PostGIS 3.5.7 to verify `Point(4326)`, GiST index, and `ST_DWithin(::geography)` before locking domain migrations. | 🟡 **OPEN-P1** (Story Gate `F-401`) |
| **F-307** | **P1** | Layer 6 & Layer 11 `[BE]` | **Headless Chromium memory pressure on 4GB Hetzner VPS:** `spatie/laravel-pdf` spawns Chromium; >1 concurrent PDF job alongside PHP-FPM can trigger Linux OOM killer. | Constrain PDF queue worker to `--queue=pdf --max-jobs=50` with concurrency = 1 (`AD-15`) and rate-limit exports to `5/min` per org (`AD-22`), with `dompdf` fallback if memory exceeds 75%. | 🟡 **OPEN-P1** (Story Gate `F-407`) |
| **F-308** | **P1** | Layer 8 & Layer 14 `[BOTH]` | **NDPA 2023 pre-pilot compliance & retention parameters (PRD OQ#4, #6, #7):** Signed DPA, cross-border EU transfer clauses, NDPC registration check, and concrete retention durations must be locked before real customer PII enters production. | Architectural pruning mechanism is specified (`AD-8`, `AD-14`); User/PM must confirm retention durations and execute DPA before pilot onboarding (`F-104`, `F-206`). | 🟡 **OPEN-P1** (Pre-Pilot Gate) |

---

## Architecture Review by Layer

### Layer 1 — Frontend Engineering `[FE]`
- **Current state:** Official Laravel 13 React Starter Kit (`Inertia 3.x`, `React 19.x`, `TypeScript 5.x`, `Tailwind CSS 4.x` via `@tailwindcss/vite`, re-skinned `shadcn/ui` mapped to `docs/cloud_crafters_design_system/`). Unified `<PropertyMap>` wrapping MapLibre GL JS 5.x. Client-side image downscaling (max 1920px WebP/JPEG) before upload. WCAG 2.1 AA commitment (NFR-5.4).
- **Gaps identified:** MapLibre GL JS bundle is ~250KB gzipped; loading it eagerly on non-map pages will hurt LCP (<2.5s target) on Nigerian 3G/4G networks.
- **Risks:** Slow initial bundle load on low-end Android devices; unhandled map WebGL context loss crashing the page.
- **Recommendations:** Lazy-load `<PropertyMap>` via `React.lazy()` / dynamic import only on property detail and portfolio map routes; wrap map and form views in React Error Boundaries; use Inertia v3 partial reloads and skeleton states.
- **Implementation priority:** **High** (tracked in Story Gate `F-408`).

### Layer 2 — Backend Architecture `[BE]`
- **Current state:** Modular monolith with 7 domain modules (`Tenancy`, `Registry`, `Inspections`, `Maintenance`, `Evidence`, `Reporting`, `Audit`). Thin adapters (`AD-1`), single-writer module table ownership (`AD-2`), Spatie `Data` DTO contracts (`AD-16`), and standardized `{ error: { code, message } }` domain exceptions (`AD-17`).
- **Gaps identified:** None at spine level after tightening `AD-2` (read-only cross-module Eloquent relations) and `AD-22` (rate limiting & magic-byte validation).
- **Risks:** Developer discipline drift during implementation (putting validation/queries inside Inertia controllers).
- **Recommendations:** Enforce architectural Pest test (`arch()->expect('App\Http\Controllers')->not->toUse('Illuminate\Database\Eloquent\Builder')`) in CI.
- **Implementation priority:** **Critical**.

### Layer 3 — Database Engineering `[BE]`
- **Current state:** PostgreSQL 17 + PostGIS 3.5.7 on Neon EU Central. 19 Phase 1 tables (2 system identity + 17 tenant tables). Universal ordered UUID keys (`AD-6`), canonical `geometry(Point, 4326)` with GiST index (`AD-7`), append-only history tables with `UPDATE`/`DELETE` revoked from the runtime DB role (`AD-8`), and composite unique indexes (`AD-21`).
- **Gaps identified:** Week-1 spike required to confirm PostGIS dedicated-schema placement on Neon (`D1`).
- **Risks:** Missing composite indexes on `(organisation_id, client_account_id, status)` for `work_orders` and `inspections` as history grows.
- **Recommendations:** Include composite B-tree indexes `(organisation_id, client_account_id)` on all 7 client-owned tables and `(organisation_id, captured_at DESC)` on append-only history tables in initial migrations.
- **Implementation priority:** **Critical**.

### Layer 4 — Authentication & Authorisation `[BOTH]`
- **Current state:** Laravel Sanctum 4.x (SPA session cookies + PATs). Session-derived `TenantContext` (`AD-3`), PostgreSQL `FORCE ROW LEVEL SECURITY` with transaction-scoped `SET LOCAL app.org_id` (`AD-4`), automatic `ClientScope` (`AD-5`), and 6-role union RBAC policy checked inside application services (`AD-12`).
- **Gaps identified:** Fine-grained endpoint/action matrix for the 6 roles is deferred to the Foundations Epic (`AD-12`).
- **Risks:** IDOR if any service method forgets to invoke the module Policy before mutating state, or if a technician completes a work order assigned to another technician (FR-15).
- **Recommendations:** Lock the exhaustive 6-role × capability matrix in Foundations Epic (`F-404`) and test every role combination in the 5-vector isolation matrix (`AD-19`).
- **Implementation priority:** **Critical**.

### Layer 5 — Deployment & Staging `[BOTH]`
- **Current state:** Single Docker image running on Hetzner VPS (Germany) behind Caddy automatic HTTPS; staging environment uses a separate staging VPS/container + Neon database branch + R2 staging bucket; local uses `docker compose` with `postgis/postgis:17` and MinIO/local disk.
- **Gaps identified:** Explicit container healthcheck endpoint (`/up` verifying DB + R2 reachability) and zero-downtime/rollback deployment script need to be codified in Epic 1.
- **Risks:** Bad migration or broken container image taking down pilot users during business hours (`Africa/Lagos`).
- **Recommendations:** Tag Docker images by Git SHA in GitHub Container Registry (`ghcr.io`), gate container swap on `/up` HTTP 200 healthcheck, and document a 1-command previous-SHA rollback.
- **Implementation priority:** **High**.

### Layer 6 — Cloud & Compute Infrastructure `[BE]`
- **Current state:** Hetzner Germany VPS (2–4 GB RAM) co-located (~3–5 ms) with Neon EU Central (Frankfurt), delivering ~110–150 ms RTT to Nigeria. Single image runs `web` (PHP-FPM), `worker` (Laravel database queue, PDF concurrency=1), and `scheduler`.
- **Gaps identified:** Separate queue prioritization so fast jobs (audit/notifications/postcode verification) never wait behind slow PDF rendering jobs.
- **Risks:** A burst of 10 monthly client PDF exports blocking standard background tasks if placed on the `default` queue.
- **Recommendations:** Split worker queues into `--queue=high,default` (general worker) and `--queue=pdf` (dedicated single-concurrency worker with memory limit).
- **Implementation priority:** **High** (Story Gate `F-407`).

### Layer 7 — CI/CD & Version Control `[BOTH]`
- **Current state:** Monorepo (`app` + `docs-site/` Docusaurus + `docs/knowledge-base/`). GitHub Actions CI running lint, TypeScript typegen check, Pest functional suite, and 5-vector tenant-isolation matrix against real PostgreSQL 17 + PostGIS container (`AD-19`).
- **Gaps identified:** Dependency vulnerability scanning (`composer audit`, `npm audit`) should be an explicit CI step.
- **Risks:** Unchecked vulnerable transitive dependencies or hand-edited TypeScript types drifting from PHP Spatie Data classes.
- **Recommendations:** Add `php artisan typescript:transform --check`, `composer audit`, and `npm audit --audit-level=high` to GitHub Actions PR gate.
- **Implementation priority:** **High**.

### Layer 8 — Security & Data Protection `[BE]`
- **Current state:** Defense-in-depth RLS (`AD-4`), private R2 storage with 10-min signed URLs (`AD-13`), atomic audit trail (`AD-18`), magic-byte upload validation + HSTS/CSP/X-Frame-Options headers (`AD-22`), and NDPA data minimization (no identity docs, no tenant/occupant PII).
- **Gaps identified:** Non-technical NDPA pilot readiness items (DPA signature, NDPC registration check, retention durations — PRD OQ#4, #6, #7) remain open for User/legal sign-off before pilot launch.
- **Risks:** Onboarding a live pilot customer before DPA and retention schedule are finalized.
- **Recommendations:** Enforce pre-launch gate (`arch-auditor-prelaunch`) before first pilot data enters production.
- **Implementation priority:** **Critical**.

### Layer 9 — Rate Limiting & Cost Protection `[BE]`
- **Current state:** Tiered `RateLimiter` policies defined in `AD-22` (`auth`: 5/min, `uploads`: 30/min, `exports`: 5/min per org, `api`: 120/min) returning HTTP `429` with `Retry-After`. Cloudflare R2 has zero egress fees; OpenFreeMap has zero per-tile fees; no paid AI APIs are used in Phase 1.
- **Gaps identified:** Cloud billing alerts on Hetzner, Neon, and Cloudflare need operational configuration at provisioning time.
- **Risks:** Runaway storage or compute loop exceeding the $35–55/mo budget.
- **Recommendations:** Configure 50%, 80%, and 100% spend alerts on Neon and Cloudflare during Week-1 provisioning.
- **Implementation priority:** **Medium**.

### Layer 10 — Caching & CDN Architecture `[BOTH]`
- **Current state:** Vite content-hashed static assets served via Caddy with long-lived `Cache-Control: public, max-age=31536000, immutable`. R2 signed URLs serve evidence directly from Cloudflare's edge. Redis is intentionally deferred (`D6`).
- **Gaps identified:** None for MVP scale; database query caching is unnecessary at 5–30 sites per tenant with proper B-tree and GiST indexes.
- **Risks:** Prematurely adding Redis cache invalidation complexity for tenant-scoped data (high risk of cross-tenant cache key collisions if misconfigured).
- **Recommendations:** Keep application data uncached in Phase 1 (rely on Postgres indexes + Inertia partial reloads); if Redis is introduced in Phase 2+, mandate `{organisation_id}:{client_account_id}:` cache key prefixes.
- **Implementation priority:** **Low**.

### Layer 10.5 — Database Environment Management `[BE]`
- **Current state:** Three isolated environments (`local` Docker PostGIS 17, `staging` Neon branch, `production` Neon paid project with PITR). Plain reversible/forward-only Laravel migrations (`docs/knowledge-base/README.md`).
- **Gaps identified:** Seed data generator must use realistic synthetic Nigerian FM sites/assets and never copy production PII to staging/local.
- **Risks:** Testing migrations directly against production or using real customer contacts in staging.
- **Recommendations:** Build deterministic Laravel DatabaseSeeders for the Chidi/Amara/Tunde user journeys (`UJ-1`..`UJ-4`) and mandate migration dry-runs on the staging Neon branch in CI before production deploy.
- **Implementation priority:** **High**.

### Layer 11 — Scalability & Load Balancing `[BE]`
- **Current state:** Stateless web requests (session/tokens in DB, files in R2), Neon PgBouncer transaction pooling (`SET LOCAL` compatible per `AD-4`), async report generation (`AD-15`), client-side photo compression before upload.
- **Gaps identified:** Single-VPS vertical ceiling (~4GB RAM) when running PHP-FPM + Chromium worker simultaneously.
- **Risks:** Memory spike during large portfolio PDF compilation with many embedded high-res photos.
- **Recommendations:** Render evidence thumbnails (e.g. 800px width) in PDF reports rather than raw 10MB originals, and cap concurrency of the `pdf` queue worker to 1 (`AD-15`).
- **Implementation priority:** **High**.

### Layer 12 — Observability & Monitoring `[BOTH]`
- **Current state:** Structured JSON logging with `org_id`, `user_id`, `request_id` and strict PII redaction (`Consistency Conventions`), Sentry error tracking (FE + BE), Uptime monitor hitting Caddy/healthcheck, and database `audit_events` (`AD-18`) required before real data (NFR-5.5).
- **Gaps identified:** Queue failure alerting (when a `TenantAware` report export job lands in `failed_jobs`).
- **Risks:** Silent background job failures leaving users waiting for a PDF report that never finishes.
- **Recommendations:** Hook `Queue::failing()` in `AppServiceProvider` to report directly to Sentry and mark the export record status as `failed` so the UI displays an actionable retry state.
- **Implementation priority:** **High**.

### Layer 13 — Testing & QA `[BOTH]`
- **Current state:** Pest PHP 3.x/4.x running against real PostgreSQL 17 + PostGIS using the non-owner runtime DB role (`AD-19`). Mandatory 5-vector tenant-isolation matrix (`HTTP`, `service`, `raw SQL`, `queued job`, `signed URL`) plus negative tests for forged `organisation_id` (`AGENTS.md` §6). End-to-end acceptance tests defined for all 3 MVP workflows.
- **Gaps identified:** Automated accessibility check (axe-core / Playwright) to verify the hard WCAG 2.1 AA requirement (NFR-5.4).
- **Risks:** UI regressions breaking keyboard navigation or contrast on mobile field screens.
- **Recommendations:** Include Playwright + `@axe-core/playwright` smoke tests on the 3 core workflow screens alongside the Pest isolation matrix.
- **Implementation priority:** **Critical** (Story Gate `F-402`).

### Layer 14 — Backups & Disaster Recovery `[BE]`
- **Current state:** Dual backup strategy (`AD-20`, NFR-5.3): (1) Neon Point-in-Time Recovery (PITR) on production, and (2) nightly `pg_dump` scheduled job exporting to an isolated secondary Cloudflare R2 backup bucket using separate IAM credentials. Documented restore drill required before first pilot customer.
- **Gaps identified:** Concrete RTO and RPO targets are not explicitly numbered in the spine.
- **Risks:** Ambiguous recovery expectations during an outage.
- **Recommendations:** Set explicit pilot targets of **RPO ≤ 24 hours** (via nightly `pg_dump`; **≤ 1 hour** via Neon PITR) and **RTO ≤ 2 hours** (re-provision Hetzner VPS from Docker image + restore dump), verified in Week 7 staging restore drill (`F-408`).
- **Implementation priority:** **High**.

### Layer 15 — Event-Driven & Queue Architecture `[BE]`
- **Current state:** Laravel Database Queue driver (`D6`), `TenantAware` job contract + middleware (`AD-14`), `ShouldBeUnique` idempotency and retry/dead-letter rules (`AD-21`), and pessimistic row locking (`lockForUpdate()`) on work order state transitions (`AD-21`).
- **Gaps identified:** None for Phase 1 MVP after adding `AD-21`.
- **Risks:** Stale locks if a job crashes mid-execution.
- **Recommendations:** Set `--timeout=120` and `retry_after=180` on the `pdf` queue connection so hung Chromium processes are cleanly terminated before lock expiry.
- **Implementation priority:** **High**.

### Layer 16 — Cost Management & FinOps `[BOTH]`
- **Current state:** Verified budget of **$8–15/mo** (dev/staging) and **$35–55/mo** (production) in `docs/development-plan-review.md` §8. Client-side image downscaling + 10MB upload cap + zero-egress R2 keeps storage costs near $0 during pilot.
- **Gaps identified:** Orphaned R2 files if an upload succeeds to R2 but the database transaction rolls back, or after NDPA tenant offboarding.
- **Risks:** Gradual R2 storage bloat over 12+ months.
- **Recommendations:** Write R2 object only after validation, delete object in a `catch` block if DB insert fails, and include R2 prefix purge in the NDPA offboarding/retention command.
- **Implementation priority:** **Medium**.

### Layer 17 — Data Analytics & Product Telemetry `[BOTH]`
- **Current state:** Pilot success metrics (`SM-1` through `SM-5` + counter-metrics `SM-C1..C3` in `prd.md` §9) are directly computable from operational domain tables (`work_orders`, `inspections`, `attachments`, `assets`, `audit_events`) without third-party tracking scripts.
- **Gaps identified:** How the founder/platform admin views pilot adoption metrics (`SM-1..SM-4`) without running manual SQL queries.
- **Risks:** Inability to measure pilot go/no-go criteria at Weeks 4–6 cleanly, or adding heavy third-party analytics SDKs that create NDPA cross-border cookie consent issues.
- **Recommendations:** Provide a lightweight internal artisan command or Platform Admin summary query that computes `SM-1..SM-4` per Organisation directly from Postgres read queries — zero third-party telemetry trackers needed in v1.
- **Implementation priority:** **Medium**.

---

## Cross-Cutting Concerns

### 1. Concurrency & Race Conditions
- **Double Work Order Transitions / Status Jumping:** Mitigated by `AD-11` and `AD-21` (`WorkOrderService::transition` wraps `SELECT ... FOR UPDATE` row lock inside the request transaction before checking current state and appending `work_order_events`).
- **Template Edit vs In-Flight Field Inspection:** Mitigated by `AD-10` and `AD-21` (inspections bind immutably to a specific `inspection_template_id` version row at scheduling time; concurrent edits to a used template lock the family rows and enforce a unique `(organisation_id, template_family_id, version)` constraint).
- **Duplicate Asset Tags on Double-Submit:** Mitigated by `AD-21` composite unique index `(organisation_id, property_id, tag)`.
- **Duplicate PDF Report Queue Dispatch:** Mitigated by `AD-21` (`ShouldBeUnique` keyed on `organisation_id + client_account_id + period_start + period_end`) and `AD-22` (`5/min` rate limit).
- **PgBouncer Connection Pool Tenant Bleed:** Mitigated by `AD-4` (`SET LOCAL app.org_id` inside an explicit transaction block, which resets automatically on transaction `COMMIT`/`ROLLBACK`).

### 2. AI Architecture & Cloud Portability
- **AI Usage in Phase 1:** None (AI-drafted reports and MCP server tools are explicitly deferred to Phase 3 per `prd.md` §7 & §8.2).
- **AI & Cloud Readiness (`AGENTS.md` Rules 1–4, `AD-1`, `AD-20`):** Every business capability is exposed through typed Spatie `Data` contracts on the unified application service layer (`app/Domain/<Module>/Services`). When MCP tools or swappable LLM report summarizers are introduced in Phase 3, they act as thin adapters calling the exact same service methods without refactoring domain logic.

---

## Missing Components (Deferred or Pre-Pilot Items)

| Component | Why It Matters | Target Phase | Owner |
| --- | --- | --- | --- |
| **Week-1 Neon PostGIS Dedicated-Schema Spike (`D1`)** | Confirms `CREATE EXTENSION postgis` in dedicated schema + GiST index + `::geography` behaviour on Neon EU Central before locking migrations. | **MVP Week 1 (Story Gate `F-401`)** | Dev (`Amelia`) |
| **Exhaustive 6-Role × Endpoint Ability Matrix** | Translates the 6 Phase 1 roles (Platform admin, Org owner, Ops manager, Site supervisor, Technician, Contractor) into exact policy method checks. | **MVP Foundations Epic (Story Gate `F-404`)** | Architect / Dev (`Amelia`) |
| **NDPA Retention Durations & Signed Pilot DPA (`OQ#4, #6, #7`)** | Legal & operational prerequisite before onboarding real pilot customer PII or running the scheduled retention purge job. | **Pre-Pilot Launch Gate (Week 8)** | User / Legal |
| **Staging Backup Restore Drill & `/up` Healthcheck** | Proves RPO ≤ 24h / RTO ≤ 2h and prevents broken container deploys. | **Beta / Hardening (Week 7, Story Gate `F-408`)** | Dev (`Amelia`) |
| **Redis Queue & Cache Layer (`D6`)** | Needed only when concurrent background jobs or multi-VPS horizontal scaling exceed single-VPS database queue capacity. | **Phase 2 / Scale (Post-Pilot)** | Architect (`Winston`) |
| **OpenFreeMap Self-Hosted Tile Fallback** | Mitigates OpenFreeMap's lack of contractual SLA if tile availability degrades during commercial scale. | **Phase 2 / Scale** | Architect (`Winston`) |

---

## Recommended Tech Stack

| Concern | Recommended & Adopted in Spine | Rationale |
| --- | --- | --- |
| **Frontend framework** | **React 19.x + TypeScript 5.x + Inertia.js 3.x + Tailwind CSS 4.x + shadcn/ui** | Official Laravel 13 React Starter Kit; zero-API-boilerplate SPA UX with full-stack generated TypeScript contracts (`spatie/typescript-transformer`). |
| **Backend framework** | **Laravel 13.x (PHP 8.4)** | Mature modular monolith ergonomics, first-class queues, policies, migrations, and validation. |
| **Database** | **PostgreSQL 17 + PostGIS 3.5.7 (Neon EU Central)** | Native `FORCE ROW LEVEL SECURITY`, `geometry(Point, 4326)` + `::geography` metre math, and Serverless branching for staging. |
| **Hosting / compute** | **Docker on Hetzner Cloud VPS (Germany) + Caddy** | ~$7–13/mo, single-digit ms latency to Neon Frankfurt (~110–150ms to Nigeria), portable across any Linux host. |
| **Background jobs & Queue** | **Laravel Queue (Database Driver)** | Zero extra infrastructure cost for MVP (`D6`); supports `TenantAware` middleware, `ShouldBeUnique`, and dedicated `pdf` queue worker. |
| **Cache** | **PostgreSQL indexes + HTTP immutable asset headers (Redis deferred)** | Avoids premature cache-invalidation and tenant-key-collision risk at MVP scale. |
| **Object storage** | **Cloudflare R2 (S3-compatible API)** | Private buckets, 10-min signed URLs, zero egress fees, portable S3 abstraction (`AD-13`, `AD-20`). |
| **Auth** | **Laravel Sanctum 4.x** | First-party SPA cookie sessions + PATs without external auth vendor lock-in (`AGENTS.md` Rule 4). |
| **Monitoring / errors** | **Sentry (FE + BE) + UptimeRobot + `audit_events` table** | Full exception capture, uptime alerting, and immutable transactional compliance audit log. |
| **CI/CD** | **GitHub Actions** | Automated lint, type-drift check, Pest functional suite, and 5-vector RLS isolation matrix on real PostGIS container. |
| **Analytics** | **First-party SQL pilot metrics (`SM-1..SM-5`)** | Computes pilot adoption directly from operational tables without third-party cookie/PII trackers. |
| **AI / LLM** | **None in Phase 1 (Port-ready service layer for Phase 3)** | Complies with MVP scope and `AGENTS.md` Rule 3 (swappable AI adapters over shared services later). |

---

## Production Readiness Roadmap

### Phase 1 — MVP Foundations & Core Workflows (Weeks 1–5)
- Execute Week-1 Neon PostGIS dedicated-schema spike (`F-401`) and provision Hetzner + Neon + R2 + GitHub Actions CI.
- Implement `Tenancy` (`TenantContext`, non-owner DB role, `FORCE RLS`, transaction-scoped `SET LOCAL`, `ClientScope`, 6-role Policies) and the 5-vector tenant-isolation test harness (`F-402`, `F-404`).
- Deliver **Workflow 1** (Client Accounts, Properties + `LocationService` canonical `geom` + history, Zones, Assets + Custody, lazy-loaded `<PropertyMap>`) and **Workflow 2** (versioned Inspection Templates, immutable approved Inspections, Maintenance Requests, locked Work Order state machine, private R2 `EvidenceService` with magic-byte checks and 10-min signed URLs).

### Phase 2 — Reporting, Hardening & Pilot Beta (Weeks 6–8)
- Deliver **Workflow 3** (`ReportService` canonical `ReportData`, async `TenantAware` PDF/CSV export on concurrency=1 `pdf` worker, atomic `AuditService` logging).
- Configure 4-tier `RateLimiter`, CSP/HSTS security headers, Sentry FE+BE error tracking, uptime monitoring, and 50%/80%/100% cloud billing alerts.
- Execute full 5-vector tenant-isolation matrix, WCAG 2.1 AA axe-core audit, staging backup restore drill (Neon PITR + nightly R2 `pg_dump`), and finalize NDPA DPA + retention schedule (`arch-auditor-prelaunch` gate). Ready for **50–200 pilot users across 2–5 FM organisations**.

### Phase 3 — Commercial Production (Months 3–4)
- Add Paystack subscription/billing module (`subscriptions`, `usage_records`) once paid pilots validate pricing (`SM-6`).
- Introduce Redis for queues/rate-limiting (`D6`) and separate worker VPS or vertical resize (8GB RAM) as concurrent PDF exports grow. Ready for **1,000+ users**.

### Phase 4 — Scale & Ecosystem Surface (Month 4+)
- Introduce Phase 3 gated capabilities based on customer evidence: OAuth 2.1 (Laravel Passport) for external integrators, MCP server tools over the existing service layer, webhook outbox, read replicas, and self-hosted vector tiles if OpenFreeMap SLA becomes a bottleneck. Ready for **10,000+ users**.

---

## CTO Verdict

1. **Can this survive 100 users?**
   **Yes.** A 4GB Hetzner VPS + Neon PG17 (with PgBouncer transaction pooling) + Cloudflare R2 easily handles 100 concurrent field technicians, supervisors, and operations managers across several FM organisations with sub-200ms application response times.
2. **Can this survive 1,000 users?**
   **Conditionally.** Standard CRUD, inspections, work orders, and R2 signed-URL photo loads will comfortably handle 1,000 users. The condition is **PDF report generation**: headless Chromium (`spatie/laravel-pdf`) is constrained to concurrency = 1 on the 4GB VPS (`AD-15`), so simultaneous month-end PDF exports will queue sequentially. Moving PDF workers to an 8GB node or offloading to Redis queues at ~500–1,000 users removes this bottleneck without code changes.
3. **Can this survive 10,000 users?**
   **No (not on the initial $35–55/mo single-VPS topology, by design).** At 10,000 users, database-backed queues (`jobs` table polling), single-node PHP-FPM, and single-concurrency Chromium rendering will saturate CPU/RAM and DB connections. However, because the architecture is a **stateless modular monolith (`AD-1..AD-22`)**, scaling to 10,000 users requires only infrastructure promotion (Redis queue/cache, horizontal web/worker containers behind a load balancer, Neon autoscaling + read replicas) with zero domain rewrite.
4. **What breaks first?**
   **Headless Chromium PDF generation (`Infrastructure/Pdf` — Layer 6 / Layer 11)** during month-end reporting spikes on the 4GB VPS if multiple users trigger large image-heavy portfolio reports simultaneously. Mitigation is already baked into `AD-15` (dedicated `pdf` queue with concurrency = 1, thumbnail rendering in PDFs) and `AD-21`/`AD-22` (`ShouldBeUnique` job deduplication + `5/min` org rate limit).
5. **What must be fixed before launch? (The 5 Non-Negotiables)**
   1. **Verify Neon PostGIS in a dedicated schema (Week-1 Spike `F-401`):** Prove `CREATE EXTENSION postgis`, GiST indexing, and `ST_DWithin(::geography)` under the non-owner runtime DB role before locking migrations.
   2. **Enforce the 5-Vector Tenant & Client Isolation Gate (`AD-4`, `AD-5`, `AD-19`, `F-402`):** Pass automated CI tests proving zero cross-tenant or cross-client leakage across HTTP, service calls, raw SQL (`FORCE RLS`), queued jobs, and R2 signed URLs.
   3. **Enforce Pessimistic Locking & Immutable Evidence on Work Orders (`AD-11`, `AD-13`, `AD-21`, `F-405`):** Guarantee `lockForUpdate()` on transitions and prevent post-completion evidence deletion.
   4. **Isolate & Rate-Limit the Chromium PDF Worker (`AD-15`, `AD-21`, `AD-22`, `F-407`):** Run PDF exports on a dedicated `concurrency=1` queue worker with `ShouldBeUnique` and thumbnail image URLs so reporting never starves web requests.
   5. **Complete NDPA Pre-Pilot Checklist & Backup Restore Drill (`F-104`, `F-206`, `F-408`):** Sign pilot DPA, configure retention schedule, verify Sentry/uptime alerts, and execute a real staging restore from the nightly R2 `pg_dump`.
6. **What is the biggest hidden risk?**
   **PgBouncer Transaction Pooling vs `SET LOCAL app.org_id` (`AD-4` / `F-301`).** In PostgreSQL, `SET LOCAL` has no effect (or warns and resets immediately) if executed outside a `BEGIN ... COMMIT` transaction block. On Neon's transaction pooler, if a developer writes a route or job that runs `SET LOCAL app.org_id` as a standalone auto-commit statement before running Eloquent queries, the subsequent queries will execute on a pooled connection with `app.org_id` unset — either returning zero rows silently or failing open if RLS was misconfigured. Wrapping the entire tenant request/job pipeline inside an explicit database transaction (`AD-4`) and running CI tests against the real non-owner Postgres role (`AD-19`) is the critical safeguard that prevents this incident.

