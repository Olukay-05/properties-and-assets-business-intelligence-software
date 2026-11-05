# Application Security Standards (`SECURITY.md`)

**Status:** Active Security Foundation  
**Last Updated:** 2026-11-05  
**Scope:** Cloud Crafters Property & Asset Intelligence (All Contributors & AI Coding Agents)  
**Primary References:** `AGENTS.md`, `ARCHITECTURE-SPINE.md` (`AD-1`–`AD-22`), `prd.md` (`NFR-5.1`, `NFR-5.3`, `NFR-5.5`), `epics.md` (Stories 1.1–1.11, 6.1–6.4)

---

## 1. Purpose & Status Legend

This document defines the mandatory application security standards for developers and AI coding agents building, modifying, testing, and deploying the Cloud Crafters Property & Asset Intelligence platform.

Every control is tracked across five states:
- **Requirement:** Mandatory security rule for the platform.
- **Decision:** Architectural mechanism chosen in `ARCHITECTURE-SPINE.md`.
- **Implemented:** Present in the current codebase (Story 1.1 scaffold).
- **Verified:** Proven by automated tests or inspection.
- **Outstanding:** Scheduled for implementation and verification in upcoming stories.

---

## 2. Explicit Prohibitions (Non-Negotiable)

All human developers and AI coding agents must obey these seven prohibitions without exception:

1. **Never hardcode credentials, API keys, passwords, or secrets** in PHP, TypeScript/React, configuration files, migrations, tests, Dockerfiles, or documentation.
2. **Never expose private customer information, personal data, or credentials in application logs or user-facing error messages.**
3. **Never rely exclusively on frontend checks (React UI conditionals or disabled buttons) for permissions or business rules.**
4. **Never trust `organisation_id` (or `client_account_id` scope overrides) supplied by users in request bodies, query parameters, or headers** without validating the authorised session context.
5. **Never expose private storage objects (inspection photos, work-order completion evidence, or PDF/CSV report exports) through public or guessable URLs.**
6. **Never bypass established application authorisation (`Policies` / `Service` checks) or database isolation (`TenantScope`, `ClientScope`, PostgreSQL `FORCE RLS`).**
7. **Never weaken security controls (such as switching tests to SQLite/superuser, disabling CSRF, relaxing MIME checks, or using `withoutGlobalScopes()`) simply to make a feature easier to implement.**

---

## 3. Mandatory Rules for AI Coding Agents

AI coding agents operating in this repository must follow these direct instructions on every task:

1. **Enforce the Service Layer Boundary (`AD-1`, `AD-2`):**
   - Put all business rules, role policy checks (`Gate::authorize`), state transitions, tenant stamping, and `AuditService::record()` calls inside `app/Domain/<Module>/Services/*`.
   - Keep Inertia controllers (`app/Http/Controllers/Web/*`) and REST controllers (`app/Http/Controllers/Api/V1/*`) as thin adapters that only validate input shape, invoke one service method, and return the response.
   - Never write to another module's database tables directly or via cross-module Eloquent relationships (`AD-2`); always call the owning module's service.
2. **Protect Tenant and Client Context (`AD-3`, `AD-4`, `AD-5`):**
   - Resolve `organisation_id`, `user_id`, `role`, and `client_scope` exclusively from `TenantContext` (populated from the authenticated session).
   - Never add `organisation_id` to `$fillable` arrays, Spatie `Data` request DTOs, FormRequests, or URL query parameters.
   - Ensure every new tenant-owned model uses `HasUuids` (`AD-6`), applies `TenantScope` (and `ClientScope` for client-owned models), and has a corresponding PostgreSQL migration with `ENABLE ROW LEVEL SECURITY` and `FORCE ROW LEVEL SECURITY`.
3. **Enforce Append-Only & Immutability Contracts (`AD-7`, `AD-8`, `AD-10`, `AD-11`):**
   - Never issue `UPDATE` or `DELETE` queries against `locations`, `asset_custody`, `work_order_events`, or `audit_events`.
   - Guard `properties.geom` from mass assignment; route all coordinate writes through `LocationService::setLocation()`.
   - Reject edits to approved inspections (`approved_at != null`) and reject deletion of attachments once their parent inspection is approved or work order is `completed`.
4. **Write Real Security & Isolation Tests (`AD-19`):**
   - No security-sensitive feature is complete without automated Pest tests covering positive authorisation, negative role/scope denial (`403`/`404`), and cross-tenant isolation across the 5 vectors (HTTP, service layer, raw SQL under non-owner DB role, queued jobs, and signed URLs).
5. **Report Gaps Transparently:**
   - Never claim a control is "Implemented" or "Verified" until the code and passing test exist in the repository. Surface any security assumption or gap in your task completion report.

---

## 4. Application Security Standards by Control Area

### 4.1 Server-Side Input Validation & Mass-Assignment Protection
- **Requirement:** Validate all incoming data server-side before reaching domain logic. Prevent mass-assignment of sensitive columns (`organisation_id`, `client_account_id`, `approved_at`, `approved_by`, `geom`, `role`, `status`).
- **Decision (`AD-3`, `AD-7`, `AD-16`):**
  - Use strongly-typed Spatie `Data` DTOs (`spatie/laravel-data`) or Laravel FormRequests at the adapter boundary.
  - Explicitly define allowed fields on models (`#[Fillable([...])]`) and strictly exclude `organisation_id`, `properties.geom`, and workflow state/approval timestamps from mass assignment.
- **Implemented:** `spatie/laravel-data ^4.0` is installed; `App\Models\User` uses explicit `#[Fillable(['name', 'email', 'password'])]` and `#[Hidden([...])]`.
- **Outstanding:** Enforce DTO validation and model guarding across all domain models created in Epics 1–5.

### 4.2 Output Escaping & Cross-Site Scripting (XSS) Prevention
- **Requirement:** Prevent stored, reflected, and DOM-based XSS across all web views, map popups, and generated PDF reports.
- **Decision:**
  - React 19 + Inertia 3 escapes JSX expressions by default. Never use `dangerouslySetInnerHTML` with user-supplied data (notes, addresses, asset descriptions, fault descriptions).
  - MapLibre GL JS popups and markers must bind text via DOM text nodes or React components, never unescaped HTML string concatenation.
  - Blade templates used for `spatie/laravel-pdf` report rendering (`Story 4.5`) must use escaped `{{ $variable }}` syntax, never `{!! $variable !!}` on user input.
  - Uploaded files are restricted to a strict non-HTML/non-SVG allowlist (`image/jpeg`, `image/png`, `image/webp`, `application/pdf`), and non-image signed URLs set `Content-Disposition: attachment` (`AD-22`).
- **Implemented:** React 19 / Inertia 3 scaffold uses standard JSX escaping.
- **Outstanding:** Verify PDF Blade templates (`Story 4.5`), MapLibre popups (`Story 2.4`), and CSP header enforcement (`Story 1.2` / `Story 6.2`).

### 4.3 SQL Injection Prevention & Safe Spatial Queries
- **Requirement:** Parameterize all database queries. Never interpolate raw user strings into SQL statements or PostGIS expressions.
- **Decision (`AD-7`):**
  - Use Eloquent / Query Builder parameter bindings for all queries.
  - Spatial writes and distance calculations are encapsulated solely inside `LocationService::setLocation()` and `Geo::distanceMetres()` / `Geo::withinMetres()` using bound numeric parameters (`?::double precision`) and `ST_SetSRID(ST_MakePoint(?, ?), 4326)`.
  - Tenant session binding (`SET LOCAL app.org_id = ?`) validates that the value is a valid UUID before binding.
- **Outstanding:** Implement `Geo` helper (`Story 2.3`) and `SET LOCAL` middleware (`Story 1.2`) with strict UUID validation and parameter binding.

### 4.4 Cross-Site Request Forgery (CSRF) Protection
- **Requirement:** Protect all state-changing cookie-authenticated requests (`POST`, `PUT`, `PATCH`, `DELETE`) against CSRF.
- **Decision:** Laravel's `web` middleware group enforces CSRF token verification (`VerifyCsrfToken` / `ValidateCsrfToken`) automatically with Inertia.js (`X-XSRF-TOKEN` cookie/header) and Sanctum stateful SPA requests.
- **Implemented & Verified:** Enabled by default in `bootstrap/app.php` (`web` middleware group).

### 4.5 Authentication, Authorisation & Insecure Direct Object Reference (IDOR) Prevention
- **Requirement:** Prevent any user from accessing or mutating another Organisation's or unauthorized Client Account's records by guessing or substituting IDs in URLs or payloads.
- **Decision (`AD-3`, `AD-4`, `AD-5`, `AD-6`, `AD-12`):**
  - All primary and foreign keys are ordered UUIDs (`HasUuids`), eliminating predictable sequential integer IDs (`AD-6`).
  - Every query is scoped by `TenantScope` (`organisation_id`), `ClientScope` (`client_account_id`), and PostgreSQL `FORCE ROW LEVEL SECURITY`.
  - Every service method executes `Gate::authorize()` against central role policies (`AD-12`).
  - Records outside the user's tenant or `client_scope` resolve as `404 Not Found` (`Story 1.4`).
- **Outstanding:** Implement in Stories 1.2, 1.4, and verify across all entities in Story 6.1.

### 4.6 API Security & Tiered Rate Limiting
- **Requirement:** Protect authentication, file uploads, expensive PDF exports, and API endpoints against brute-force, credential stuffing, and resource exhaustion (`NFR-5.1`, `NFR-5.3`).
- **Decision (`AD-22`):** Configure four named `RateLimiter` tiers returning HTTP `429 Too Many Requests` with a `Retry-After` header:
  1. **Authentication (`auth` / `login`):** `5/min` per IP + email.
  2. **Evidence Uploads (`uploads`):** `30/min` per authenticated user.
  3. **Async Report Exports (`exports`):** `5/min` per Organisation (paired with `ShouldBeUnique` job locking and `concurrency = 1` on the PDF worker per `AD-15` & `AD-21`).
  4. **General REST API (`api`):** `120/min` per authenticated user on `/api/v1/*`.
- **Implemented & Verified:** `login` (5/min per email + IP), `two-factor` (5/min), and `passkeys` (10/min) rate limiters are implemented in `App\Providers\FortifyServiceProvider`.
- **Outstanding:** Implement `uploads` (30/min), `exports` (5/min/org), and `api` (120/min) rate limiters in Stories 1.10, 4.3, and 6.2.

### 4.7 File Upload Validation & Private File Access
- **Requirement:** Reject malicious, executable, or oversized files and ensure uploaded evidence is never publicly accessible (`FR-21`).
- **Decision (`AD-13`, `AD-22`):**
  - **Client-Side Pre-Processing (`UX-DR12`):** Downscale camera photos to max `1920px` (WebP/JPEG) before upload to save bandwidth.
  - **Server-Side Magic-Byte Verification (`EvidenceService`):** Inspect actual file contents via PHP `finfo` (never trust client `Content-Type` or file extension). Enforce strict allowlist: `image/jpeg`, `image/png`, `image/webp`, `application/pdf` (SVG and HTML are banned to prevent stored XSS). Maximum file size: **10 MB**.
  - **Storage Key Isolation:** Generate a fresh server-side UUID filename (`organisations/{org_id}/clients/{client_id}/{owner_type}/{owner_id}/{uuid}.{ext}`). Never use user-supplied filenames in storage keys (preventing path traversal).
  - **Private Bucket & Signed URLs:** Store in a private S3-compatible bucket (RustFS locally, Cloudflare R2 in production). Serve exclusively via **10-minute pre-signed GET URLs** generated by `EvidenceService` after verifying the requester's `organisation_id` and `client_scope`. Non-image downloads (`application/pdf`) must set `Content-Disposition: attachment`.
- **Implemented & Verified:** Private bucket configuration (`FILESYSTEM_DISK=s3`, `EVIDENCE_SIGNED_URL_TTL_MINUTES=10`) and `tests/Feature/StorageContractTest.php` (verifying upload, 10-min pre-signed URL read, expired URL rejection, and `403` anonymous access denial on RustFS).
- **Outstanding:** Implement `EvidenceService` (`finfo` magic-byte check, 10 MB cap, key namespacing, provenance persistence, and authorised signed-URL endpoint) in Story 1.10.

### 4.8 Concurrency & State-Machine Integrity
- **Requirement:** Prevent race conditions on concurrent status changes, duplicate asset tags, template version collisions, and duplicate report jobs.
- **Decision (`AD-11`, `AD-21`):**
  - Acquire pessimistic row locks (`lockForUpdate()`) inside the active transaction in `WorkOrderService::transition()` and `InspectionService::approve()`.
  - Enforce composite unique database indexes on `(organisation_id, property_id, tag)` for `assets` and `(organisation_id, template_family_id, version)` for `inspection_templates`.
  - Implement `ShouldBeUnique` on queued jobs with explicit `$tries`, `$backoff`, and `$timeout`.
- **Outstanding:** Implement across Stories 2.6, 3.1, 3.5, 3.9, and 4.3.

### 4.9 Secure Error Handling, Safe Logging, and Audit Events
- **Requirement:** Never leak stack traces, SQL queries, table names, or credentials to clients. Maintain an immutable audit trail of sensitive actions (`FR-22`, `NFR-5.3`, `NFR-5.5`).
- **Decision (`AD-8`, `AD-17`, `AD-18`):**
  - Application services throw `DomainException` subclasses carrying permanent `snake_case` error codes (e.g., `work_order_invalid_transition`).
  - Central exception handling (`bootstrap/app.php`) renders `{ "error": { "code", "message" } }` on `/api/v1/*` and mapped validation/flash errors on Inertia, while sanitizing uncaught `QueryException` / `PDOException` errors and reporting them to Sentry.
  - Application logs use structured JSON including `org_id`, `user_id`, and `request_id`, and strip passwords, tokens, and personal data.
  - `AuditService::record($action, $target, $metadata)` writes atomically inside the active transaction to `audit_events` (where the runtime DB role has no `UPDATE` or `DELETE` privilege).
- **Implemented:** Basic JSON exception rendering toggle in `bootstrap/app.php`; `DB::prohibitDestructiveCommands(app()->isProduction())` in `AppServiceProvider`.
- **Outstanding:** Implement `DomainException` handler, structured JSON logging context, and `AuditService` in Story 1.11, and Sentry integration in Story 6.4.

### 4.10 Dependency, Package, and Secret Management
- **Requirement:** Pin dependencies with committed lockfiles, monitor for vulnerabilities, and keep secrets out of version control.
- **Implemented & Verified:** `composer.lock` and `pnpm-lock.yaml` are committed; `.gitignore` excludes `.env`, `.env.backup`, `.env.production`, `/storage/*.key`, and `/auth.json`; `.github/dependabot.yml` monitors GitHub Actions.
- **Outstanding:** Add `composer` and `npm` ecosystems to `.github/dependabot.yml` and run a secrets audit in Story 6.2.

### 4.11 Secure HTTP Headers & Production Configuration
- **Requirement:** Enforce HTTPS everywhere and set defensive HTTP response headers (`NFR-5.1`, `AD-22`).
- **Decision (`AD-22`):**
  - `APP_DEBUG=false` and `APP_ENV=production` in production.
  - Enforce HTTP security headers via middleware / reverse proxy:
    - `Strict-Transport-Security: max-age=31536000; includeSubDomains` (HSTS)
    - `X-Content-Type-Options: nosniff`
    - `X-Frame-Options: DENY`
    - `Referrer-Policy: strict-origin-when-cross-origin`
    - `Content-Security-Policy` scoped to `'self'`, Cloudflare R2 signed URL origins, and OpenFreeMap tile origins.
- **Outstanding:** Implement security headers middleware and verify in Story 6.2.

---

## 5. Security Testing Requirements (CI Gates)

Before any feature or pull request is considered complete, automated Pest tests must verify:

1. **5-Vector Tenant Isolation Matrix (`tests/Isolation/`, `AD-19`, Story 6.1):**
   - Verified across **HTTP requests**, **direct service calls**, **raw SQL queries**, **queued jobs**, and **signed URL generation** on real PostgreSQL 17 + PostGIS under the non-owner runtime DB role.
2. **Negative Payload Tampering Tests (`AGENTS.md` §6, Story 1.2):**
   - Supplying a forged `organisation_id` in a request body or query string is ignored/rejected.
3. **Role & Client-Scope Boundary Tests (`Story 1.4`):**
   - Each role is tested for allowed actions and denied actions (`403`), and out-of-scope `client_account_id` UUIDs return `404`.
4. **Upload & Rate-Limit Hardening Tests (`Story 1.10`, `Story 6.2`):**
   - Spoofed file extensions (e.g., an HTML or PHP file renamed to `.jpg`) and files >10 MB are rejected by `finfo` magic-byte checks; rate limiters return `429` with `Retry-After`.

