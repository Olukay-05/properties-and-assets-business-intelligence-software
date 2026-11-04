# Property & Asset Intelligence — Development Plan & Architecture Review (Revision 2)

**Status:** revised plan for approval. **No existing document has been modified; no code, scaffold, hosting or database has been created.** The design system remains the visual source of truth (SoT), as agreed.

**Guiding principle:** build for the future, but only build what the present stage of the business needs. Secure a paying pilot, then expand on evidence.

---

## What changed in Revision 2

| # | Point addressed | What changed |
| --- | --- | --- |
| 1 | Multi-tenancy | New detailed section (§1): tenant-context resolution, RLS, pooled connections, background jobs, file storage, and a tenant-isolation test matrix. |
| 2 | Geospatial | New section (§2): `geometry` vs `geography` units, canonical coordinate storage, and how to prevent lat/lng/geom drift. |
| 3 | Hosting/DB regions | New region + latency comparison (§7); concrete recommendation; MCP hosting requirements removed from initial setup. |
| 4 | Hosting costs | Prices re-verified against current sources (§8); realistic monthly budget table. |
| 5 | MVP delivery | Reorganised around **three complete workflows** (§3); client portal deferred unless a pilot requires it. |
| 6 | REST API | Limited endpoint list + versioning/OpenAPI + first-party vs external auth split (§5). |
| 7 | Pilot validation | Concrete pilot success measures + discovery running **alongside** development (§10). |
| 8 | Timeline | 6-week vs 8-week reconciliation; realistic milestones and dependencies (§9). |
| 9 | Data protection | NDPA 2023 pilot-readiness checklist added (§11). |
| 10 | Inconsistencies | `api_clients`, client read-only, and other deferred entities resolved (§12). |

---

## 1. Multi-tenancy — how one FM organisation is isolated from another

**Model: single shared database, logical isolation.** Every tenant-owned row carries `organisation_id`; a tenant is the paying FM company (its managed property owners are `client_accounts` *inside* that tenant, not separate tenants).

### 1.1 Laravel tenant context

- The tenant is resolved **only** from the authenticated user's `memberships` row — never from request input.
- A tenant-scoped middleware reads the current membership, binds a `TenantContext` singleton (current `organisation_id`, user, role, `client_scope`), and applies a model **global scope** so Eloquent queries auto-filter by `organisation_id`.
- **Hard rule:** `organisation_id` is never accepted from a request body or query string. A client cannot "select" a tenant; the session decides it. This is the single most important anti-leak rule and is enforced in the service layer and covered by negative tests.

### 1.2 PostgreSQL Row-Level Security (the backstop)

- On every tenant table: `ALTER TABLE … ENABLE ROW LEVEL SECURITY; ALTER TABLE … FORCE ROW LEVEL SECURITY;` plus a policy `USING (organisation_id = current_setting('app.org_id')::uuid)`.
- At the start of each request/job, the app issues `SET LOCAL app.org_id = '<id>'` (and a role claim) inside a transaction. `SET LOCAL` is per-transaction, so a pooled connection never leaks a tenant into the next request.
- `FORCE RLS` ensures even a misconfigured query cannot read cross-tenant rows.

**Defense in depth:** the app scopes every query **and** RLS enforces at the database. RLS alone can fail silently if `SET LOCAL` is forgotten; app-scoping alone leaks on one missed `where`. Both must hold, and tests verify both.

### 1.3 Database connections

- One shared connection pool (single schema), not connection-per-tenant and not schema-per-tenant. Tenant identity is transaction-local (`SET LOCAL`), so pooling is safe and cheap.
- Schema-per-tenant and database-per-tenant were considered and rejected for the MVP: they multiply migrations, complicate pooling and add cost without adding security benefit at this scale.

### 1.4 Background jobs

- Queued jobs serialise the `organisation_id` (and acting `user_id`) into the payload; the worker re-establishes tenant context (`SET LOCAL app.org_id`) before running and re-scopes every query.
- Scheduled/cron jobs iterate tenants deliberately and re-scope per tenant. No job ever runs in a "super" context.

### 1.5 File storage

- Object keys are namespaced by tenant: `organisations/{org_id}/…/photo.jpg`.
- Buckets are **private**; access is only via **short-lived signed URLs** issued by the service layer after authorisation. There is no public URL and no guessable permanent link.
- Signed-URL generation checks the requesting user's membership and `client_scope`.

### 1.6 Automated tenant-isolation tests (required CI gate)

For each core entity, a matrix asserts that **Organisation B** cannot read, update, delete, list, export or download (signed URL) any **Organisation A** row. Three layers tested:

1. **HTTP** (Inertia routes + REST API) — cross-tenant requests return 403/empty.
2. **Service layer** — direct calls with a forged tenant context fail.
3. **Raw SQL** — a raw cross-tenant query returns zero rows, proving RLS itself holds.

Negative tests: tampering with `organisation_id` in a request body is ignored/rejected. This suite runs on every merge and before every deploy.

---

## 2. Geospatial implementation — units, canonical storage, consistency

### 2.1 Geographical units (the key correctness point)

- A `geometry(Point, 4326)` column is stored in **degrees**; `ST_Distance` on raw `geometry` returns degrees, which is **meaningless as metres** and a classic bug.
- `geography(Point, 4326)` computes on the spheroid and returns **metres**.
- **Rule:** store `geometry(Point, 4326)`; compute distance/thresholds via the geography cast — `ST_DWithin(a.geom::geography, b.geom::geography, 50)` — or `ST_Distance(…::geography, …::geography)`.
- Encode this in **one** service-layer helper (`Geo::distanceMetres()`, `Geo::withinMetres()`). No other code path is allowed to compute distance, so the unit choice cannot drift.

### 2.2 Canonical coordinate storage

- **Single source of truth:** `properties.geom geometry(Point, 4326)` with a GiST index.
- `latitude`/`longitude` are **derived**, not independently stored: expose them in the API/serializer as `ST_Y(geom)` / `ST_X(geom)`. This removes the drift problem entirely (there is no second copy to go stale).
- If plain SQL columns are ever needed for simple exports, use Postgres **generated columns** (`lat double precision GENERATED ALWAYS AS (ST_Y(geom)) STORED`) so they cannot be written independently.
- **Ordering pitfall:** `ST_MakePoint(longitude, latitude)` — x is longitude, y is latitude. Encode this once in the setter.

### 2.3 Write path and history

- A single mutator `setLocation(lat, lng)` writes `geom = ST_SetSRID(ST_MakePoint(lng, lat), 4326)` and appends a `locations` history row (`geom, accuracy_m, source, captured_by, captured_at`).
- The current location is the latest `locations` row; a moved/re-surveyed site never overwrites history.
- Source and verification state (device GPS / map pin / imported) are recorded per coordinate, separate from any postcode value.

### 2.4 Neon compatibility (verified)

Neon supports the `postgis` extension ([Neon PostGIS docs](https://neon.com/docs/extensions/postgis)). **Week-1 spike still required:** confirm `CREATE EXTENSION postgis` on the chosen plan, install it in a dedicated schema (Neon guidance), and verify `Point`/GiST/`ST_DWithin` behave as expected before the schema is locked.

---

## 3. Revised MVP scope — three complete workflows

The MVP is one responsive web app (React + Inertia) over a Laravel modular monolith, with a core REST API. **Nothing ships until these three workflows work end-to-end and pass tests.**

### Workflow 1 — Property & asset registration

Create organisation → create client → register property/site (address, site code, coordinates, optional postcode field) → add zones/areas → register assets (tag, category, condition, status, custodian).

- **Acceptance test:** a new site with GPS point, zones and assets can be created, found on the map, and its assets/custodians listed — by a user scoped to that client only.

### Workflow 2 — Inspection & maintenance

Schedule inspection → run inspection (checklist + photos + condition) → raise request → work order → assign technician → record completion + evidence → update asset history.

- **Acceptance test:** an inspection can raise a work order, which is assigned, completed with photo evidence, and reflected in the asset's history and timeline.

### Workflow 3 — Client reporting & evidence

Compile a client-ready report (visits completed, jobs by status, evidence, condition) → export PDF/CSV → (optionally) share manually.

- **Acceptance test:** a period report for one client is generated and exported, containing only that client's sites/records.

**Deferred (unless a pilot requires it):** client-facing portal / read-only links. The buyer's proof in the MVP is the **exported report**, shared by the FM provider themselves — not a self-service login.

Everything else (MCP, webhooks, advanced geo, offline, desktop, AI features) stays deferred to Phase 3.

---

## 4. Architecture (unchanged in principle)

```
HUMAN USERS ──► WEB APP (React + Inertia) ──► Inertia controllers (thin)
                                              │
EXTERNAL (later) ──► REST API (versioned) ────┤
                                              ▼
                              SHARED SERVICE LAYER (the rules)
                              properties · assets · inspections · work orders
                              technicians · reports · authorisation · audit
                                              │
                 ┌────────────────────────────┼───────────────────┐
                 ▼                            ▼                   ▼
        PostgreSQL + PostGIS           Object storage        Background jobs
        (Neon; portable)               (R2, S3)              (Laravel Queues)

DOCS APP (Docusaurus) — separate deploy, reads Markdown/MDX from Git, no app DB.
```

**Invariant:** controllers and API endpoints are thin adapters; business rules exist once, in the service layer.

---

## 5. REST API — limited initial scope, auth distinction

### 5.1 Initial endpoints (mirror the three workflows; nothing more)

| Area | Endpoints |
| --- | --- |
| Identity | `GET /api/v1/me` |
| Workflow 1 | `GET/POST /api/v1/properties`, `GET /api/v1/properties/{id}`, `GET/POST /api/v1/properties/{id}/assets`, `GET/PATCH /api/v1/assets/{id}` |
| Workflow 2 | `POST /api/v1/inspections`, `GET /api/v1/inspections/{id}`, `POST /api/v1/inspections/{id}/complete`, `GET/POST /api/v1/work-orders`, `GET /api/v1/work-orders/{id}`, `POST /api/v1/work-orders/{id}/assign`, `POST /api/v1/work-orders/{id}/complete` |
| Workflow 3 | `GET /api/v1/reports`, `GET /api/v1/properties/{id}/history` |

- Versioned under `/api/v1`; consistent JSON envelope and error shape.
- **OpenAPI** generated from route definitions (single source of truth); the spec is published in the docs site.
- Add an endpoint only when a workflow or pilot requires it.

### 5.2 First-party vs external authentication

| Audience | Auth mechanism | Status |
| --- | --- | --- |
| **First-party** — the platform's own React/Inertia app, and the operator's own tooling | Laravel **Sanctum** (SPA cookie session + personal-access tokens) | Built in Phase 1 |
| **External** — third-party integrators or AI agents | OAuth 2.1 via **Passport**, scoped and per-tenant | **Deferred to Phase 3** |

The MVP REST API is **first-party only**. There is no public developer portal, no client registration UI, no `api_clients` registry, no webhooks, no third-party rate-limit tiers. This is an API for the product's own surfaces, **not** an integration platform. The OAuth seam is architectural (kept in mind), not implemented.

---

## 6. Database and PostGIS (see §2 for the geospatial detail)

- Standard PostgreSQL + PostGIS on Neon; plain SQL/Laravel migrations; `organisation_id` + RLS on every tenant table (§1).
- Migrations are the source of truth; spatial queries live behind the service layer so advanced queries can be added without schema rework.
- **Phase 1 entity set** (resolved, see §12): organisations · users (+ Sanctum tokens) · memberships · client_accounts · properties (with `geom`) · locations · site_zones · assets · asset_custody · inspection_templates(+items) · inspections(+responses) · maintenance_requests · work_orders(+events) · contractors · attachments · audit_events.

---

## 7. Hosting & database regions (Nigerian users; MCP excluded)

**No African region exists at Neon.** The nearest Neon regions are on AWS (US East Virginia/Ohio, US West Oregon, **EU Central Frankfurt**, Asia Pacific Singapore) — confirm the current list at sign-up. Hetzner runs in Germany (Nuremberg/Falkenstein), Finland, US and Singapore; DigitalOcean has Frankfurt, Amsterdam and London.

**Latency reasoning for West African (Lagos/Abuja/PH) users** (typical ranges — confirm with a real test from the actual network):

| App/DB location | Typical round-trip from Lagos |
| --- | --- |
| Frankfurt / Amsterdam / London | ~110–150 ms |
| US East (Virginia) | ~180–230 ms |
| Singapore | ~250 ms+ (often routed via Europe) |

**Recommendation — keep app and DB in the same European region:**

| Component | Recommendation | Why |
| --- | --- | --- |
| Database (Neon) | **EU Central (Frankfurt)** | Nearest Neon region to West Africa. |
| Application (VPS) | **Hetzner (Nuremberg/Falkenstein, Germany)** | Very low cost, EU, ~3–5 ms to Frankfurt. Alternative: DigitalOcean Frankfurt (same city as Neon) for a small premium. |
| Object storage | Cloudflare R2 (global, zero egress) | Region-agnostic; no egress charges. |

App-to-DB latency stays single-digit ms (same country/region), so the only meaningful latency is user→app, which is unavoidable and within the ~110–150 ms range for EU hosting.

**MCP hosting requirements are intentionally excluded** — MCP is deferred; nothing in the initial hosting config depends on it.

---

## 8. Hosting costs — verified budget

Prices re-checked against current sources (confirm final quotes at sign-up; Hetzner prices exclude VAT).

| Component | Choice | Monthly |
| --- | --- | --- |
| App VPS | Hetzner "Regular Performance" starter (2–4 GB) | ≈ $7–13 (from €6.49/mo) |
| Database | Neon Free for dev/staging; paid tier (~$19–25/mo) for production backups/PITR | $0 → ~$25 |
| Object storage | Cloudflare R2 — **free tier: 10 GB-mo, 1M Class A, 10M Class B, zero egress** ([R2 pricing](https://developers.cloudflare.com/r2/pricing/)) | $0 (photos stay far under the free tier) |
| Domain + SSL | One domain, Let's Encrypt | ≈ $1–2 |
| Email/SMTP | Resend free tier (or equivalent) for reset/notifications | $0 |
| Monitoring | Sentry free tier + UptimeRobot free + log rotation | $0 |
| Backups | Neon PITR (paid tier) **plus** nightly `pg_dump` to R2 (independent + portable) | $0 |
| CI/CD | GitHub Actions free tier | $0 |

**Realistic monthly operating budget:**

- **Dev/staging:** ≈ **$8–15/mo** (Neon Free + Hetzner starter + R2 free).
- **Production (pilot + first customers):** ≈ **$35–55/mo** (≈ ₦45,000–75,000 at ₦1,300–1,400/$).

**Not included** (add when they become real): a second Neon compute, paid monitoring if error volume exceeds free tiers, outbound SMS, and any NIPOST fees (unknown — excluded by design).

**Portability:** to move to Nigerian hosting later, repoint `DB_*` at a Nigerian PostgreSQL and redeploy the same image; R2 objects migrate via any S3 tool; no code change.

---

## 9. Roadmap & timeline — 6-week vs 8-week reconciliation

The earlier "six weeks" assumed a thinner scope and full-time founder focus. This plan commits to **8 weeks** because it includes the non-negotiable foundations (auth, RLS, audit, tenant-isolation tests, CI, PostGIS spike, Docusaurus) **and** because discovery runs in parallel and consumes founder time.

A 6-week plan would require cutting to two workflows and deferring CI/docs polish — **not recommended** (it trades the safety foundations for a date).

| Week | Milestone | Depends on |
| --- | --- | --- |
| 1 | Architecture confirmed; hosting + Neon provisioned; **PostGIS spike**; repo + `AGENTS.md` + CI; design-system → Tailwind theme | D1, D7, C5 |
| 2–3 | **Workflow 1** end-to-end (orgs/clients/properties/location/zones/assets/custody) + its REST API + tests | PostGIS spike, auth, RLS |
| 4 | **Workflow 2a** — inspection templates, schedule, run inspection, photos | Workflow 1 |
| 5 | **Workflow 2b** — requests, work orders, assignment, completion, evidence, history | 2a |
| 6 | **Workflow 3** — reporting/export + audit | 2b |
| 7 | Hardening: tenant-isolation matrix, security pass, Docusaurus + knowledge base, staging deploy, backups | all |
| 8 | End-to-end pilot dry-run, bug fixes, **NDPA/DPA checklist**, first-pilot onboarding prep | all |

**Critical path:** PostGIS spike (wk 1) → schema → workflow 1 → 2 → 3 → hardening. Design-system theming runs parallel. **Discovery interviews run weeks 1–4 in parallel** and are the gate to Phase 2 (pilot), not an afterthought.

**Honest dependency:** this schedule assumes a mostly-full-time founder/lead plus AI-assisted development. If founder time is split across discovery and building, weeks 4–6 are where slippage shows; the buffer is in week 8, not in scope.

---

## 10. Pilot validation — practical measures, run alongside development

### 10.1 Discovery cadence (concurrent with build)

- Weeks 1–4: 15–20 FM-provider interviews while Phase 1 is built. Each interview ends with the paid-pilot offer.
- Findings continuously refine the three workflows — do not wait until the build is "done" to learn.

### 10.2 Paid-pilot success measures (baseline in week 1 of the pilot, re-measure at weeks 4–6)

| Measure | Target (hypothesis) |
| --- | --- |
| Work-order adoption | ≥80% of agreed pilot work orders created and closed in the app (vs WhatsApp/paper) |
| Inspection recording | ≥70% of scheduled inspections recorded in-app |
| Evidence completeness | ≥90% of completed visits have photo + timestamp + location |
| Register completeness | ≥85% of agreed assets have site + tag + category + condition + custodian |
| Time saved | Monthly client-report prep time reduced ≥50% (timed before/after) |
| Willingness to pay | Pilot signs (or states written intent to) a paid plan at a stated price; ideally refers one peer |
| Retention signal | Customer keeps using it into a second month without founder prompting |

### 10.3 Go / no-go

- **Proceed:** ≥1 paid pilot (target 2) + measured improvement in ≥2 measures + renewal intent at a paid tier.
- **Pivot or stop:** <2 paid pilots after 15–20 interviews, or no measurable improvement and price objections below the floor. Stop means the toolkit stays an internal asset, not a sunk product bet.

---

## 11. Data protection — NDPA 2023 essentials for pilot readiness

Add to the pilot-readiness checklist **before onboarding the first customer**:

1. **Controller/processor roles:** pilot customer = controller; platform = processor. Sign a DPA (or a short pilot data-processing addendum) first.
2. **Registration:** confirm with counsel/DPCO whether either party is a "controller/processor of major importance" requiring NDPC registration. Do not guess.
3. **Data minimisation:** store only what the workflows need (technician/custodian names + phones, site/asset photos). No identity documents, no tenant/occupant data.
4. **Purpose limitation & retention:** document what is stored, why, and for how long; define deletion on request/offboarding.
5. **Security measures:** tenant isolation + RLS, HTTPS, access control, audit logs — mapped explicitly to NDPA's "appropriate technical and organisational measures".
6. **Breach notification:** NDPA's 72-hour clock applies to the controller; the platform must notify the pilot customer promptly enough to meet it — put a notification clause in the DPA.
7. **Cross-border transfer:** data is hosted in the EU (Neon/VPS). Document the transfer basis (adequate safeguards via the DPA/standard clauses). NDPA permits transfer where safeguards are met.
8. **Data-subject requests:** a simple process for access/rectification/deletion.
9. **Local policy note:** confirm the National Cloud Policy's classification/residency position for the pilot's data (ordinary commercial FM data is not sovereign data, but confirm rather than assume).

These are not optional "later" items — they are part of pilot readiness.

---

## 12. Resolved inconsistencies (from the earlier plan)

| Item | Previous position | Resolved position |
| --- | --- | --- |
| `api_clients` table | "Keep it" | **Deferred to Phase 3.** The MVP API is first-party only; Sanctum's built-in `personal_access_tokens` is all that's needed now. |
| Client read-only access / `client_share_links` | "Must-have" | **Deferred** unless a pilot requires it. Client value in the MVP is the **exported report**, not a self-service portal. |
| `mcp_tools`, `tool_invocations`, `webhook_subscriptions`, `approvals` | In core schema | **Deferred to Phase 3.** No agent/integration tables in Phase 1. Supervisor sign-off is a field on work orders/inspections, not a separate `approvals` table. |
| `asset_locations` (relocation history) | In core schema | **Deferred.** A simple current site/zone foreign key on `assets` suffices; relocation history is a later refinement. |
| `subscriptions` / `usage_records` (billing) | In core schema | **Deferred to launch (Phase 2).** Pilots are invoiced manually; Paystack billing is added at commercial launch. |
| Design system vs shadcn/ui | "Reconcile" | **Design system is the SoT (as agreed).** Map its tokens into the Tailwind theme and restyle shadcn/ui components to the editorial look. |

**Result:** the Phase 1 schema is the minimal set in §6 — no unused tables, no integration scaffolding.

---

## 13. Architectural decisions to confirm before implementation

| # | Decision | Recommendation |
| --- | --- | --- |
| D1 | Neon plan + PostGIS | Week-1 spike: verify `CREATE EXTENSION postgis` on the chosen plan; dedicated schema. |
| D2 | Coordinate storage | `geometry(Point,4326)` canonical; lat/lng derived via `ST_Y`/`ST_X` (no independent columns). |
| D3 | Auth | Sanctum for first-party now; Passport/OAuth 2.1 deferred. |
| D4 | Design system | SoT; tokens → Tailwind theme; shadcn/ui re-skinned to match. |
| D5 | Map provider | MapLibre GL JS + OpenFreeMap (verify licensing); MapTiler not mandatory. |
| D6 | Queue driver | Database driver for MVP; Redis only if needed later. |
| D7 | Hosting + region | Hetzner (Germany) + Neon EU Central (Frankfurt). |
| D8 | File storage | Cloudflare R2, private bucket + signed URLs. |
| D9 | Repository | One monorepo: main app + Docusaurus docs. |
| D10 | Tenant model | Single schema + `organisation_id` + RLS. |

---

## 14. Explicitly **not** built in Phase 1

**Features:** MCP server / AI-agent tools · webhooks · Tauri desktop · offline sync · advanced geospatial analytics · AI-drafted reports · WhatsApp integration · contractor portal/SLA · preventive-maintenance rules engine · parts/inventory · IoT · rent/lease/tenant/access control · title/ownership verification · client-facing portal · NIPOST levels 2+.

**Infrastructure:** microservices · Kubernetes · separate auth server · dedicated MCP process · multi-region DB · paid documentation CMS · heavy observability stack · bulk postcode data sync · public developer portal / `api_clients` registry.

**Rule:** if an item is not required for one of the three workflows or for security/reliability, it is deferred.

---

## 15. Conflicts between existing documents and this direction

| # | Conflict | Recommended change (not yet applied) |
| --- | --- | --- |
| C1 | `updates.md` still frames agent/MCP/desktop/offline as near-term and its data model has Phase-3 tables | Revise `updates.md` to the reduced 3-workflow scope and defer those tables (per §12). |
| C2 | Design system vs Tailwind + shadcn/ui | **Resolved by you:** design system is the SoT; theme shadcn/ui to match. |
| C3 | `updates.md` says MapTiler | Switch to MapLibre + OpenFreeMap (verify licensing). |
| C4 | Strategy docs present pricing as detailed plans | Keep pricing labelled **hypothesis**; no figure is committed. |
| C5 | No `AGENTS.md` / `README.md` exists | Create them + lightweight knowledge base in Phase 1 (the "Standing rules" in `updates.md` §3 are ready to copy in). |
| C6 | Strategy doc 1 assumes Neon PostGIS works | Treat as week-1 spike/checkpoint (§2.4), not an assumption. |

---

## 16. What happens next (on your approval)

First concrete actions, in order: (1) the Neon/PostGIS spike (D1), (2) the hosting decision (D7), (3) create `AGENTS.md` + the lightweight knowledge base (C5) — all **before** any feature code.

No source document is modified until you explicitly approve the §15 changes; no code, scaffold, hosting or database is created until this plan is approved.
