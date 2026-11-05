# Addendum — Property & Asset Intelligence (technical depth)

This file preserves the *how* that belongs in a downstream architecture document, so the PRD stays capability-first. It is the record of confirmed technical decisions and rejected alternatives, drawn from the approved `docs/development-plan-review.md` (Revision 2) and `docs/knowledge-base/`. The PRD references these by capability; here is the mechanism.

## Confirmed stack (summary)

- **Backend:** Laravel (modular monolith — no microservices).
- **Frontend:** React + TypeScript + Inertia; Tailwind + shadcn/ui re-skinned to the design-system tokens (`docs/cloud_crafters_design_system/` is the visual source of truth).
- **Database:** PostgreSQL + PostGIS on Neon, EU Central (Frankfurt).
- **Object storage:** Cloudflare R2, private bucket + short-lived signed URLs.
- **Queues:** Laravel Queues, database driver for the MVP (Redis only if needed later).
- **Auth:** Laravel Sanctum (first-party SPA session + personal-access tokens).
- **Map:** MapLibre GL JS + OpenFreeMap (licensing to verify — D5).
- **Docs:** Docusaurus (separate deploy, reads Markdown from Git).
- **Hosting:** Hetzner (Germany) for the app; Neon EU Central for the DB.

## Architecture decisions (ADRs) — from `docs/knowledge-base/architecture-decisions.md`

| ID | Decision |
| --- | --- |
| D1 | Neon + PostGIS — week-1 spike verifies `CREATE EXTENSION postgis` in a dedicated schema before the schema is locked. |
| D2 | Coordinate storage — `geometry(Point, 4326)` canonical; lat/lng derived (`ST_Y`/`ST_X`), no independent columns. |
| D3 | Auth — Sanctum for first-party now; Passport/OAuth 2.1 deferred to Phase 3. |
| D4 | Design system is the visual SoT; tokens → Tailwind theme; shadcn/ui re-skinned. |
| D5 | Map provider — MapLibre GL JS + OpenFreeMap (verify licensing). |
| D6 | Queue driver — database driver for MVP. |
| D7 | Hosting/region — Hetzner (Germany) + Neon EU Central (Frankfurt). |
| D8 | File storage — Cloudflare R2, private bucket + signed URLs. |
| D9 | Repository — one monorepo: main app + Docusaurus docs. |
| D10 | Tenant model — single schema + `organisation_id` + RLS. |

## Tenant-isolation mechanism (the single most important anti-leak rule)

- Every tenant-owned row carries `organisation_id`; the tenant is resolved **only** from the authenticated user's membership, never from request input.
- A tenant-scoped middleware binds a `TenantContext` (org id, user, role, client scope) and applies a model global scope so Eloquent auto-filters by `organisation_id`.
- RLS backstop on every tenant table: `ENABLE ROW LEVEL SECURITY` + `FORCE ROW LEVEL SECURITY` + `USING (organisation_id = current_setting('app.org_id')::uuid)`.
- At the start of each request/job the app issues `SET LOCAL app.org_id = '<id>'` inside a transaction (per-transaction, so pooled connections are safe).
- Queued jobs serialise `organisation_id` + acting `user_id` into the payload and re-establish tenant context in the worker; cron jobs iterate tenants and re-scope per tenant.
- **Defense in depth:** app scoping *and* RLS must both hold; tests verify both.
- **Security & Data Protection Documentation Foundation (`docs/security/`):** Operational and implementation rules for `FR-19`–`FR-23`, `NFR-5.1`, and `§6.1`–`§6.2` are governed by the six-document security suite: `docs/security/AUTH.md` (authentication, onboarding, 6-role union permissions, `ClientScope`), `docs/security/SECURITY.md` (secure coding standards and mandatory agent rules), `docs/security/RLS-POLICIES.md` (PostgreSQL `FORCE RLS` policies, `pai_owner` vs `pai_app` DB roles, 17-table isolation matrix), `docs/security/ENV.md` (environment variables and secrets management), `docs/security/THREAT-MODEL.md` (threat scenarios and verification tests), and `docs/security/DATA-PROTECTION.md` (NDPA 2023 controller/processor rules, data minimisation, retention, and 72-hour breach logging).

## Geospatial mechanism

- `properties.geom geometry(Point, 4326)` with a GiST index is the single source of truth.
- Distances are computed in **metres** via the `::geography` cast, only through `Geo::distanceMetres()` / `Geo::withinMetres()`. No other code path computes distance.
- `ST_MakePoint(longitude, latitude)` — x is longitude, y is latitude; encoded once in the setter.
- A single mutator `setLocation(lat, lng)` writes `ST_SetSRID(ST_MakePoint(lng, lat), 4326)` and appends a `locations` history row (`accuracy_m`, `source`, `captured_by`, `captured_at`). The current location is the latest row.
- Latitude/longitude are exposed in serializers as `ST_Y(geom)`/`ST_X(geom)`; no independent columns to drift.

## Phase 1 entity set — from `docs/knowledge-base/data-model.md`

`organisations` · `users` (+ Sanctum `personal_access_tokens`) · `memberships` (org_id, user_id, role, client_scope, status) · `client_accounts` · `properties` (with `geom`, `postcode_value`, `postcode_status`) · `locations` (coordinate history) · `site_zones` · `assets` · `asset_custody` (handover history) · `inspection_templates` (+ `_items`) · `inspections` (+ `responses`) · `maintenance_requests` · `work_orders` (+ `_events`) · `contractors` · `attachments` · `audit_events`.

Supervisor sign-off is a field on work orders/inspections, **not** a separate `approvals` table.

## REST API — from `docs/knowledge-base/api-conventions.md`

First-party only; ~15 endpoints mirroring the three workflows; `/api/v1`; OpenAPI generated from route definitions.

| Area | Endpoints |
| --- | --- |
| Identity | `GET /api/v1/me` |
| Workflow 1 | `GET/POST /api/v1/properties`, `GET /api/v1/properties/{id}`, `GET/POST /api/v1/properties/{id}/assets`, `GET/PATCH /api/v1/assets/{id}` |
| Workflow 2 | `POST /api/v1/inspections`, `GET /api/v1/inspections/{id}`, `POST /api/v1/inspections/{id}/complete`, `GET/POST /api/v1/work-orders`, `GET /api/v1/work-orders/{id}`, `POST /api/v1/work-orders/{id}/assign`, `POST /api/v1/work-orders/{id}/complete` |
| Workflow 3 | `GET /api/v1/reports`, `GET /api/v1/properties/{id}/history` |

Conventions: resource nouns plural; nested sub-resources; envelope `{ "data": …, "meta": … }`; error shape `{ "error": { "code", "message" } }`. `organisation_id` is never accepted from the client.

## Rejected alternatives (rationale preserved)

- **Schema-per-tenant / database-per-tenant** — rejected: multiplies migrations, complicates pooling, adds cost without security benefit at MVP scale (D10).
- **`geography` column instead of `geometry`** — rejected for storage: `geometry(Point,4326)` is the canonical store; the geography *cast* is used for metre-accurate distance, so the unit choice cannot drift (D2).
- **Independent `latitude`/`longitude` columns** — rejected: two copies drift; coordinates are derived from `geom` (D2).
- **`api_clients` table in Phase 1** — deferred to Phase 3: the MVP API is first-party only (D3).
- **Client read-only access / `client_share_links`** — deferred: client value in the MVP is the exported report.
- **Separate `approvals` table** — rejected: supervisor sign-off is a field on work orders/inspections.
- **`asset_locations` relocation history** — deferred: a simple current site/zone FK on `assets` suffices in Phase 1.
- **Redis queue driver** — deferred: database driver is sufficient for MVP (D6).

## PRD-review refinements → data-model implications

Captured during PRD review (user-approved). These are requirements-level refinements to the Phase 1 entity set and should be reflected in `docs/knowledge-base/data-model.md` during the architecture phase:

- `client_accounts` — add company name, primary contact person, contact email + phone, address, status (active/inactive), and internal notes. No invoicing/contracts/CRM fields.
- `inspection_templates` — versioned; an `inspection` records the template **version** it used, so later template edits do not rewrite historical inspections. Supervisor sign-off is a field on `inspections` (as on `work_orders`), not a separate entity.
- `work_orders` — status enum: `open` / `assigned` / `in_progress` / `completed` / `on_hold` / `cancelled` (transitions recorded in `work_order_events`).
- `attachments` — provenance: `uploaded_by`, `uploaded_at`, polymorphic owner (`inspection` / `asset` / `work_order`), and optional location.
- FR-23 (data-protection operations) maps to the NDPA checklist in `docs/development-plan-review.md` §11 and requires no new Phase 1 tables; retention periods are Open Question #7 in the PRD.

Terminology note: the glossary term **Zone** maps to the `site_zones` table; **site code** is a field on `properties`, not a synonym for Property.
