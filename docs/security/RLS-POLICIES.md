# PostgreSQL Row-Level Security and Tenant Isolation (`RLS-POLICIES.md`)

**Status:** Active Security Foundation  
**Last Updated:** 2026-11-05  
**Scope:** Cloud Crafters Property & Asset Intelligence (Multi-Tenant Isolation Architecture)  
**Primary References:** `docs/knowledge-base/data-model.md`, `docs/development-plan-review.md` (§1), `ARCHITECTURE-SPINE.md` (`AD-3`, `AD-4`, `AD-5`, `AD-6`, `AD-8`, `AD-14`, `AD-19`), `epics.md` (Stories 1.2, 6.1)

---

## 1. Purpose & Implementation Status Truthfulness

This document specifies how organisation-level and client-level data isolation works across PostgreSQL Row-Level Security (RLS), Laravel middleware, Eloquent global scopes, background jobs, and object storage.

**Current Implementation Status (as of Story 1.1 Scaffold):**
- **Requirement & Decision:** Fully specified and approved in `ARCHITECTURE-SPINE.md` (`AD-3`, `AD-4`, `AD-5`, `AD-8`, `AD-14`, `AD-19`) and `docs/knowledge-base/data-model.md`.
- **Implemented in Code Today:** **Not yet implemented.** Only the starter-kit migrations (`users`, `password_reset_tokens`, `sessions`, `cache`, `jobs`, `passkeys`) exist in `database/migrations/`. None of the 17 tenant tables, RLS policies, `TenantContext` middleware, or non-owner PostgreSQL runtime roles have been created yet.
- **Verified Today:** Local S3-compatible private bucket access denial (`403`) and pre-signed URL expiry are verified in `tests/Feature/StorageContractTest.php`. Database RLS and the 5-vector tenant-isolation test matrix are **Outstanding** (scheduled for **Story 1.2** and **Story 6.1**).

---

## 2. Domain Hierarchy & Two-Tier Isolation Model

The platform serves Nigerian facilities-management (FM) companies. Each paying FM provider is a **Tenant (`Organisation`)**. The commercial property owners served by that FM provider are **`Client Accounts` inside that Organisation**, not separate platform tenants.

### 2.1 Core Entity Hierarchy
```text
Organisation (Tenant boundary — enforced by App TenantScope + PostgreSQL FORCE RLS)
 └── Client Account (Intra-tenant client boundary — enforced by App ClientScope)
      └── Property / Site (Canonical PostGIS geometry + Locations history)
           ├── Site Zone (Floor, wing, plant room, car park)
           ├── Asset (Maintainable equipment + Asset Custody history)
           ├── Inspection (Bound to Inspection Template version + Inspection Responses)
           ├── Maintenance Request (Fault raised from Inspection or directly)
           └── Work Order (Assigned to Technician/Contractor + Work Order Events)
                └── Attachment / Evidence (Polymorphic to Inspection, Asset, or Work Order)
```
Organisation-wide operational records also include `memberships`, `inspection_templates` (+ `inspection_template_items`), `contractors`, and `audit_events`.

---

## 3. Application Roles vs. PostgreSQL Database Roles

**Do not confuse application-level user roles with PostgreSQL database roles.** They operate at different layers and solve different problems:

| Layer | Roles | Purpose | How Enforced |
| --- | --- | --- | --- |
| **1. Application Roles** (`memberships.role`) | `Platform admin`, `Organisation owner`, `Operations manager`, `Site supervisor`, `Technician`, `Contractor` | Control what a human user is allowed to do **within** their authorised Organisation and `client_scope` (e.g., only a Site Supervisor or Ops Manager can approve an inspection; a Technician can only complete work orders assigned to them). | Enforced in Laravel Application Services via central Laravel Policies (`AD-12`) and `ClientScope` (`AD-5`). |
| **2. PostgreSQL Database Roles** (`pg_roles`) | 1. **Migration / Owner Role** (`pai_owner`)<br>2. **Application Runtime Role** (`pai_app`)<br>3. **NDPA Maintenance Role** (`pai_maintenance`, deferred job) | Control database-engine privileges (`DDL`, `BYPASSRLS`, and table-level `SELECT`/`INSERT`/`UPDATE`/`DELETE` grants) across all database connections. | Enforced by PostgreSQL engine permissions and `FORCE ROW LEVEL SECURITY` (`AD-4`, `AD-8`). |

### Critical Security Requirement: Non-Owner Runtime Database Role
**The application runtime database role (`pai_app`) must NEVER be a PostgreSQL `SUPERUSER`, must NEVER hold the `BYPASSRLS` attribute (`NOBYPASSRLS`), and must NOT own the tables (`ALTER TABLE ... FORCE ROW LEVEL SECURITY` must also be set on every tenant table as a second safeguard).**

| PostgreSQL Role | Used By | Privileges & Constraints |
| --- | --- | --- |
| **`pai_owner`** (Admin / Migration Role) | `php artisan migrate` during CI/CD deployment only (`DB_MIGRATION_USERNAME`). | Owns schema objects; executes `CREATE TABLE`, `ALTER TABLE ... ENABLE/FORCE ROW LEVEL SECURITY`, and `CREATE POLICY`. Never used by web, API, or queue workers at runtime. |
| **`pai_app`** (Application Runtime Role) | Web (`php-fpm`), REST API, Queue Workers, Scheduler, and Pest test suite (`DB_USERNAME`). | `NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS`. Granted `SELECT, INSERT, UPDATE, DELETE` on standard tables, and **granted `SELECT, INSERT` ONLY (no `UPDATE` or `DELETE`)** on append-only history tables (`locations`, `asset_custody`, `work_order_events`, `audit_events`) per `AD-8`. |

---

## 4. Table Classification & Entity Ownership Matrix

Every table in the database belongs to one of three classifications (`AD-4`, `docs/knowledge-base/data-model.md`):

1. **System Identity Tables (No RLS):** Queried during authentication and initial membership lookup before a tenant session is bound.
2. **Tenant Tables (RLS Mandatory — 17 Tables):** Carry `organisation_id uuid NOT NULL` and enforce `ENABLE ROW LEVEL SECURITY` + `FORCE ROW LEVEL SECURITY`.
3. **Framework Infrastructure & Reference Tables (No RLS):** Laravel internal tables (`sessions`, `password_reset_tokens`, `cache`, `jobs`, `job_batches`, `failed_jobs`, `migrations`) and PostGIS reference table (`spatial_ref_sys`).

*Note on Conflict Resolution (`epics.md` 16 vs `ARCHITECTURE-SPINE.md` 17 tables):* `ARCHITECTURE-SPINE.md` (`AD-4`) and `docs/knowledge-base/data-model.md` list **17** tenant tables (`epics.md` line 74 says "16" by miscounting). All **17** tables below are mandatory tenant tables and must carry `organisation_id` and `FORCE RLS`.

### 4.1 Complete Entity Isolation Matrix

| Table Name | Owning Module (`AD-2`) | Classification | `organisation_id` | `client_account_id` (`AD-5`) | RLS Enforced | Runtime `pai_app` Grants (`AD-8`) | Access & Isolation Considerations |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `organisations` | Tenancy & Identity | System Identity | Primary Key (`id`) | No | No | `SELECT, INSERT, UPDATE` | Queried only for active membership resolution and org profile. |
| `users` | Tenancy & Identity | System Identity | No (linked via `memberships`) | No | No | `SELECT, INSERT, UPDATE` | Global user identity (`HasUuids`). Never queryable across tenants directly by end users. |
| `personal_access_tokens` | Tenancy & Identity | System Identity | No (scoped to `tokenable_id`) | No | No | `SELECT, INSERT, UPDATE, DELETE` | Sanctum first-party tokens (`D3`). Tenant resolved via user's active membership after auth. |
| `memberships` | Tenancy & Identity | **Tenant (1/17)** | **Yes (`NOT NULL`)** | Optional (`client_scope` JSONB array) | **`FORCE RLS`** | `SELECT, INSERT, UPDATE, DELETE` | Initial lookup during auth uses a security-definer helper or pre-RLS user lookup; all management queries run under `app.org_id` RLS. |
| `client_accounts` | Registry | **Tenant (2/17)** | **Yes (`NOT NULL`)** | Primary Key (`id`) | **`FORCE RLS`** | `SELECT, INSERT, UPDATE` | Managed property owners inside the FM Organisation. Filtered by `id IN client_scope` when restricted. |
| `properties` | Registry | **Tenant (3/17)** | **Yes (`NOT NULL`)** | **Yes (`NOT NULL`, immutable)** | **`FORCE RLS`** | `SELECT, INSERT, UPDATE` | Canonical `geom geometry(Point,4326)` guarded from mass-assignment (`AD-7`). Enforces `TenantScope` + `ClientScope`. |
| `locations` | Registry | **Tenant (4/17)** | **Yes (`NOT NULL`)** | Via `property_id` (or denormalised) | **`FORCE RLS`** | **`SELECT, INSERT` ONLY** | Append-only coordinate history (`AD-7`, `AD-8`). No `UPDATE` or `DELETE` for `pai_app`. |
| `site_zones` | Registry | **Tenant (5/17)** | **Yes (`NOT NULL`)** | **Yes (`NOT NULL`, immutable)** | **`FORCE RLS`** | `SELECT, INSERT, UPDATE, DELETE` | Sub-areas within a Property. Enforces `TenantScope` + `ClientScope`. |
| `assets` | Registry | **Tenant (6/17)** | **Yes (`NOT NULL`)** | **Yes (`NOT NULL`, immutable)** | **`FORCE RLS`** | `SELECT, INSERT, UPDATE` | Unique `(organisation_id, property_id, tag)` (`AD-21`). Enforces `TenantScope` + `ClientScope`. |
| `asset_custody` | Registry | **Tenant (7/17)** | **Yes (`NOT NULL`)** | Via `asset_id` | **`FORCE RLS`** | **`SELECT, INSERT` ONLY** | Append-only custodian handover history (`AD-8`). Latest row = current accountable custodian. |
| `inspection_templates` | Inspections | **Tenant (8/17)** | **Yes (`NOT NULL`)** | No (Organisation-wide) | **`FORCE RLS`** | `SELECT, INSERT, UPDATE` | Row-versioned via `(organisation_id, template_family_id, version)` (`AD-10`, `AD-21`). |
| `inspection_template_items` | Inspections | **Tenant (9/17)** | **Yes (`NOT NULL`)** | No (Organisation-wide) | **`FORCE RLS`** | `SELECT, INSERT, UPDATE, DELETE` | Checklist sections/questions belonging to an `inspection_template`. |
| `inspections` | Inspections | **Tenant (10/17)** | **Yes (`NOT NULL`)** | **Yes (`NOT NULL`, immutable)** | **`FORCE RLS`** | `SELECT, INSERT, UPDATE` | Immutable once `approved_at` is set; corrections create new row via `supersedes_id` (`AD-10`). |
| `inspection_responses` | Inspections | **Tenant (11/17)** | **Yes (`NOT NULL`)** | Via `inspection_id` | **`FORCE RLS`** | `SELECT, INSERT, UPDATE` | Locked against updates in `InspectionService` once parent `inspection.approved_at != null`. |
| `maintenance_requests` | Maintenance | **Tenant (12/17)** | **Yes (`NOT NULL`)** | **Yes (`NOT NULL`, immutable)** | **`FORCE RLS`** | `SELECT, INSERT, UPDATE` | Fault requests linked to Property/Asset/Inspection. Enforces `TenantScope` + `ClientScope`. |
| `work_orders` | Maintenance | **Tenant (13/17)** | **Yes (`NOT NULL`)** | **Yes (`NOT NULL`, immutable)** | **`FORCE RLS`** | `SELECT, INSERT, UPDATE` | Status transitions only via `WorkOrderService::transition()` with `lockForUpdate()` (`AD-11`, `AD-21`). |
| `work_order_events` | Maintenance | **Tenant (14/17)** | **Yes (`NOT NULL`)** | Via `work_order_id` | **`FORCE RLS`** | **`SELECT, INSERT` ONLY** | Append-only state transition & progress history (`AD-8`, `AD-11`). |
| `contractors` | Maintenance | **Tenant (15/17)** | **Yes (`NOT NULL`)** | No (Organisation-wide) | **`FORCE RLS`** | `SELECT, INSERT, UPDATE` | External maintenance providers registered by the Organisation. |
| `attachments` | Evidence | **Tenant (16/17)** | **Yes (`NOT NULL`)** | **Yes (`NOT NULL`, immutable)** | **`FORCE RLS`** | `SELECT, INSERT, DELETE` | Immutable (deletion blocked) once parent Inspection is approved or Work Order is completed (`AD-11`, `AD-13`). |
| `audit_events` | Audit | **Tenant (17/17)** | **Yes (`NOT NULL`)** | Optional metadata | **`FORCE RLS`** | **`SELECT, INSERT` ONLY** | Append-only compliance log (`AD-8`, `AD-18`). No `UPDATE` or `DELETE` for `pai_app`. |

---

## 5. PostgreSQL RLS Policy Design (`SELECT`, `INSERT`, `UPDATE`, `DELETE`)

### 5.1 Safe Session-Variable Resolution
- **Requirement:** If a query ever executes on a tenant table when `app.org_id` has not been set in the current transaction, PostgreSQL must safely deny access (returning zero rows on `SELECT` and rejecting `INSERT`/`UPDATE`/`DELETE`) rather than leaking data or crashing on an empty-string UUID cast.
- **Decision (`AD-4` refined for empty-string safety):**
  Use `NULLIF(current_setting('app.org_id', true), '')::uuid` in every RLS policy:
  - The second argument `true` (`missing_ok`) ensures `current_setting` returns `NULL` instead of throwing an error when `app.org_id` has never been set on the connection.
  - `NULLIF(..., '')` converts an empty string `''` (which occurs if `RESET app.org_id` or `SET LOCAL app.org_id = ''` is executed) into `NULL` before casting to `::uuid`.
  - In SQL, `organisation_id = NULL` evaluates to `UNKNOWN` (falsy), guaranteeing **fail-closed** behaviour.

### 5.2 Standard Policy SQL Template for All 17 Tenant Tables
Every tenant table migration must execute:

```sql
ALTER TABLE <table_name> ENABLE ROW LEVEL SECURITY;
ALTER TABLE <table_name> FORCE ROW LEVEL SECURITY;

CREATE POLICY <table_name>_tenant_isolation_policy ON <table_name>
    AS PERMISSIVE
    FOR ALL
    TO PUBLIC
    USING (organisation_id = NULLIF(current_setting('app.org_id', true), '')::uuid)
    WITH CHECK (organisation_id = NULLIF(current_setting('app.org_id', true), '')::uuid);
```

- **`SELECT` (`USING`):** Only rows where `organisation_id` matches the active transaction's `app.org_id` are visible.
- **`INSERT` (`WITH CHECK`):** Inserting a row with a `NULL` or mismatched `organisation_id` is rejected by PostgreSQL with an RLS policy violation error (`SQLSTATE 42501`).
- **`UPDATE` (`USING` + `WITH CHECK`):** Only current-tenant rows can be targeted (`USING`), and `organisation_id` cannot be mutated to another tenant's UUID (`WITH CHECK`).
- **`DELETE` (`USING`):** Only current-tenant rows can be deleted (and on the 4 append-only tables `locations`, `asset_custody`, `work_order_events`, and `audit_events`, `DELETE` and `UPDATE` privileges are revoked from `pai_app` entirely).

### 5.3 Resolving Initial Membership at Login (Bootstrap Path)
Because `memberships` is itself one of the 17 RLS-protected tenant tables, how does the application resolve a user's membership on login before `app.org_id` is known?
- **Decision:** Provide a narrow, read-only PostgreSQL `SECURITY DEFINER` function owned by `pai_owner` (e.g., `resolve_user_memberships(p_user_id uuid)`) with `SET search_path = public` that returns only `(membership_id, organisation_id, roles, client_scope, status)` for `WHERE user_id = p_user_id`, **or** add a specific `SELECT` policy on `memberships` allowing `user_id = NULLIF(current_setting('app.user_id', true), '')::uuid` during authentication lookup while keeping `INSERT`/`UPDATE`/`DELETE` strictly locked to `organisation_id = NULLIF(current_setting('app.org_id', true), '')::uuid`. This design must be finalized and tested in **Story 1.2**.

---

## 6. Establishing & Clearing Tenant Context Across Execution Paths

### 6.1 HTTP Requests (Inertia Web & `/api/v1` REST) & PgBouncer Compatibility
- **Decision (`AD-3`, `AD-4`):**
  - Production and staging use Neon PostgreSQL with PgBouncer in **transaction pooling** mode. Session-level `SET app.org_id` is unsafe with transaction pooling because connections are shared across transactions.
  - Therefore, Tenancy Middleware must:
    1. Resolve the authenticated user's active membership (`organisation_id`, `user_id`, `role`, `client_scope`).
    2. Initialize the request-scoped `TenantContext` singleton.
    3. Open a database transaction (`DB::beginTransaction()`).
    4. Execute `DB::statement("SELECT set_config('app.org_id', ?, true)", [$tenantContext->organisationId])` (which is equivalent to `SET LOCAL app.org_id = ?` and supports parameterized binding).
    5. Pass the request down the pipeline and commit (or roll back on exception) at the end of the request.
  - Because `set_config(..., true)` / `SET LOCAL` is strictly scoped to the current transaction, PostgreSQL automatically clears `app.org_id` the instant the transaction commits or rolls back. A pooled connection can **never** leak tenant context into a subsequent request.

### 6.2 Application-Layer Defense in Depth (`TenantScope` & `ClientScope`)
- **Decision (`AD-3`, `AD-5`):**
  - Never rely on RLS alone (`docs/development-plan-review.md` §1.2).
  - Every tenant-owned Eloquent model uses a `BelongsToTenant` trait that:
    1. Registers a global `TenantScope` (`WHERE <table>.organisation_id = TenantContext::organisationId()`).
    2. Registers a `creating` model event hook that automatically stamps `organisation_id` from `TenantContext::organisationId()` and throws a fatal exception if a conflicting `organisation_id` was set on the model or if `TenantContext` is uninitialized.
  - Every client-owned model (`properties`, `site_zones`, `assets`, `inspections`, `maintenance_requests`, `work_orders`, `attachments`) additionally registers the global `ClientScope` (`AD-5`), which applies `whereIn('client_account_id', $clientScope)` whenever `TenantContext::clientScope()` is non-null.

### 6.3 Background Jobs & Scheduled Tasks (`AD-14`)
- **Decision (`AD-14`):**
  - Every queued job must implement the `TenantAware` contract, serializing `organisation_id` and acting `user_id` into the job payload.
  - A mandatory Queue Job Middleware intercepts job execution before `handle()`, verifies the serialized `organisation_id` and `user_id`, binds `TenantContext`, starts a database transaction, and sets `set_config('app.org_id', $orgId, true)`.
  - After the job transaction finishes, the middleware clears `TenantContext` from the worker process memory (`TenantContext::clear()`) so a long-running `php artisan queue:work` daemon never retains state between jobs.
  - Unscoped job execution (a job attempting to query a tenant model without `TenantContext`) must fail immediately.
  - Scheduled tasks (`routes/console.php`) never run in a global cross-tenant query mode; they iterate active `organisations` sequentially and run each tenant's work inside an isolated `TenantContext` transaction.

### 6.4 File & Attachment Tenant Isolation (`AD-13`)
- **Decision (`AD-13`):**
  - Object storage keys in RustFS / Cloudflare R2 are strictly namespaced by tenant and client account:
    `organisations/{organisation_id}/clients/{client_account_id}/{owner_type}/{owner_id}/{uuid}.{ext}`
  - The storage bucket is private (`403 AccessDenied` on direct unauthenticated URLs).
  - `EvidenceService` generates a 10-minute pre-signed GET URL **only** after fetching the `Attachment` model through `TenantScope`, `ClientScope`, and PostgreSQL RLS, proving the requesting user is authorised for both that `Organisation` and that `Client Account`.

### 6.5 Tenant Isolation in API Endpoints and Report Exports (`AD-15`)
- **Decision:**
  - REST API endpoints (`/api/v1/*`) use the exact same `TenantContext` middleware, `TenantScope`, `ClientScope`, RLS policies, and Application Services as Inertia controllers.
  - Client reports (`ReportService::compile($clientAccount, $period)`) require an explicit `$clientAccount` resolved within the current `TenantContext` and `ClientScope`, and all underlying queries (`properties`, `inspections`, `work_orders`, `assets`, `attachments`) are double-filtered by both `organisation_id` (app + RLS) and `client_account_id` (`ClientScope` + explicit query).

---

## 7. Administrative Access & Approved Exceptions

- **Platform Admin Support (`AD-4`):** Platform administrators do **not** have a runtime "god mode" that disables RLS. When a Platform Admin assists a customer, they act via an explicit, audited `memberships` record in that target Organisation (`AuditService::record()`).
- **NDPA Data Retention / Pruning (`AD-8`, `Story 5.5`):** Because the runtime role `pai_app` has no `DELETE` privilege on append-only tables (`locations`, `asset_custody`, `work_order_events`, `audit_events`), court-ordered or NDPA offboarding/retention deletions execute exclusively through a dedicated, audited `TenantAware` administrative maintenance script/job using an explicitly scoped maintenance connection (`AD-8`).

---

## 8. Required Security & Isolation Tests (Mandatory CI Gate — `AD-19`)

No tenant-scoped entity may ship to production without passing the automated isolation suite (`tests/Isolation/`) running against **real PostgreSQL 17 + PostGIS under the non-owner `pai_app` role** (never SQLite, never superuser):

1. **Cross-Tenant Read Denial:** Organisation A cannot retrieve, list, search, or view Organisation B's records via Inertia routes, `/api/v1` endpoints, or Application Services (returns `404` or empty list).
2. **Cross-Tenant Write/Delete Denial:** Organisation A cannot create, update, transition, or delete records under Organisation B.
3. **URL / Payload ID Tampering (IDOR & Forged `organisation_id`):**
   - Substituting Organisation B's entity UUID in a URL or payload returns `404 Not Found`.
   - Supplying a forged `organisation_id` in a `POST`/`PATCH` body or query string is ignored or rejected; the record is either stamped with Organisation A's ID or fails validation.
4. **Raw SQL RLS Verification:** Executing a raw SQL query (`DB::select('SELECT * FROM properties')`) under the `pai_app` connection while `app.org_id` is set to Organisation A returns zero rows belonging to Organisation B; executing with `app.org_id` unset returns zero rows across all tenants.
5. **Background Job Isolation:** A queued `TenantAware` job dispatched by Organisation A cannot read or mutate Organisation B's records, and worker memory does not retain Organisation A's `TenantContext` when the next job runs.
6. **File Download & Signed URL Isolation:** User in Organisation A (or User in Organisation A restricted to Client Account 1) cannot request a pre-signed download URL for an `Attachment` belonging to Organisation B (or Client Account 2).
7. **Connection Pool / Transaction Cleanup:** After a request transaction commits or rolls back, a subsequent query on the same database connection outside a tenant transaction has `current_setting('app.org_id', true)` empty/null and returns zero tenant rows.

