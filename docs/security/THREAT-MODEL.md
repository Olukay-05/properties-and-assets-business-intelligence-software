# Security Threat Model (`THREAT-MODEL.md`)

**Status:** Active Security Foundation  
**Last Updated:** 2026-11-05  
**Scope:** Cloud Crafters Property & Asset Intelligence (Phase 1 MVP & Paid Pilot)  
**Primary References:** `ARCHITECTURE-SPINE.md` (`AD-1`–`AD-22`), `docs/development-plan-review.md`, `prd.md`, `docs/security/RLS-POLICIES.md`, `docs/security/SECURITY.md`

---

## 1. Purpose & Truthfulness Rule

This document identifies the assets the platform must protect, the threat actors who might target them, and the concrete threat scenarios that could occur.

**Truthfulness Rule:** Only controls that exist in the current repository and have been inspected or tested are listed under **Existing Verified Controls**. All architectural designs scheduled for upcoming stories are listed under **Required Additional Controls** and **Outstanding Work**.

---

## 2. Assets to Protect

| Asset ID | Asset Category | Description & Sensitivity |
| --- | --- | --- |
| **A-1** | **Customer & Organisation Records** | FM provider (`organisations`) and managed property owner (`client_accounts`) records: company names, primary contact names, email addresses, phone numbers, physical addresses, and internal operational notes (`FR-1`). **High business & commercial confidentiality.** |
| **A-2** | **Property & Asset Information** | Physical building addresses, site codes, NIPOST postcode status, `site_zones`, and `assets` (tags, categories, makes/models/serial numbers, conditions, statuses, and `asset_custody` history) (`FR-2`–`FR-8`). **High operational & physical-security sensitivity** (reveals critical infrastructure such as generators, transformers, and access points at commercial premises). |
| **A-3** | **Inspection Records** | `inspection_templates`, scheduled and completed `inspections`, `inspection_responses`, asset condition ratings, and supervisor sign-offs (`FR-9`–`FR-11`). **High evidentiary integrity** (used to prove SLA compliance and billing to clients; must be immutable once approved). |
| **A-4** | **Maintenance Requests & Work Orders** | Fault descriptions (`maintenance_requests`), `work_orders`, assignments, progress notes, completion records, and `work_order_events` timelines (`FR-12`–`FR-16`). **High operational integrity.** |
| **A-5** | **Technician & Employee Information** | Staff and contractor names, email addresses, phone numbers, organisation memberships, roles, and assigned work orders (`users`, `memberships`, `contractors`). **Personal data under NDPA 2023.** |
| **A-6** | **Photographs & Uploaded Documents** | Site/asset inspection photos, work-order completion photos, and exported PDF/CSV client reports stored in RustFS (local) / Cloudflare R2 (production) (`attachments`, `FR-18`, `FR-21`). **High confidentiality & evidentiary integrity** (may show interior security layouts, equipment faults, or personnel). |
| **A-7** | **Location Information** | Canonical property coordinates (`properties.geom`), historical site coordinates (`locations`), and optional device GPS coordinates captured on evidence (`attachments`) (`FR-3`, `FR-15`, `FR-21`). **Physical security & personal privacy sensitivity** (when tied to a technician's timestamped photo upload). |
| **A-8** | **Authentication Credentials & Sessions** | Password hashes (`users.password`), 2FA secrets/recovery codes, passkey credentials, password reset tokens, and active session payloads (`sessions`). **Critical access-control asset.** |
| **A-9** | **API & Infrastructure Credentials** | `APP_KEY`, database credentials (`DB_PASSWORD`), S3/R2 keys (`AWS_SECRET_ACCESS_KEY`, `BACKUP_R2_*`), Sanctum `personal_access_tokens`, Sentry DSNs, and optional `NIPOST_API_KEY`. **Critical system-wide asset.** |
| **A-10** | **Database & Audit Records** | Complete PostgreSQL + PostGIS dataset and the append-only `audit_events` compliance log (`FR-22`). **Critical integrity & non-repudiation asset.** |
| **A-11** | **Backups & Report Exports** | Neon point-in-time recovery (PITR) snapshots, nightly portable `pg_dump` archives in the R2 backup bucket, and compiled PDF/CSV client report files (`FR-18`, `NFR-5.3`). **Critical bulk-data asset** (a single leaked backup exposes all tenants). |

---

## 3. Potential Threat Actors

| Actor ID | Threat Actor | Motivation & Capability |
| --- | --- | --- |
| **TA-1** | **Unauthorised External Users** | Opportunistic attackers, bots, or competitors probing public web/API endpoints, storage URLs, or login forms without valid credentials. |
| **TA-2** | **Malicious or Compromised Authenticated Users** | An authenticated user in Tenant A (or a threat actor who phished a staff member's session) attempting to read or tamper with Tenant B's records via URL/API manipulation. |
| **TA-3** | **Employees Accessing Information Beyond Their Permissions** | A Technician, Contractor, or restricted Supervisor within an FM Organisation attempting to view or modify Client Accounts outside their `client_scope`, complete another technician's work order, or alter an already-approved inspection. |
| **TA-4** | **Compromised Administrator Accounts** | An attacker who compromises an `Organisation owner` or `Platform admin` account, gaining broad access to memberships, exports, or NDPA deletion workflows. |
| **TA-5** | **Malicious Insiders** | A disgruntled FM staff member or contractor attempting to falsify work-order completion without visiting a site, delete incriminating fault history, or bulk-export client portfolios before leaving. |
| **TA-6** | **Attackers Exploiting Application Vulnerabilities** | Skilled attackers targeting file upload parsers, headless Chromium PDF rendering (SSRF/local file read), SQL/PostGIS query construction, or race conditions. |
| **TA-7** | **Compromised Third-Party Services or Credentials** | Leaked `.env` secrets, compromised CI/CD tokens, or an outage/compromise in an external dependency. |

---

## 4. Prioritised Threat Scenarios Matrix

Threats are prioritised by **Risk Level** (**Critical** → **High** → **Medium**) based on **Impact** (severity of business, legal, or safety harm) and **Likelihood** (ease of occurrence if controls are missing or misconfigured).

| Priority | Threat ID | Threat Scenario | Likelihood (Unmitigated) | Impact | Overall Risk |
| --- | --- | --- | --- | --- | --- |
| **P0 (Critical)** | **T-01** | Cross-tenant data exposure (HTTP, API, raw SQL, or pooled connection leak) | High | Critical | **CRITICAL** |
| **P0 (Critical)** | **T-02** | Private document or photograph exposure (direct bucket access or signed-URL bypass) | High | Critical | **CRITICAL** |
| **P0 (Critical)** | **T-03** | Intra-tenant privilege escalation or `client_scope` bypass (IDOR / role bypass / tampering with immutable records) | High | High | **CRITICAL** |
| **P0 (Critical)** | **T-04** | Insecure background jobs & cross-tenant report export leakage | Medium | Critical | **CRITICAL** |
| **P1 (High)** | **T-05** | Account takeover via credential stuffing, stolen/reused passwords, or session hijacking | High | High | **HIGH** |
| **P1 (High)** | **T-06** | Malicious file uploads & SSRF / memory exhaustion in headless Chromium PDF rendering | Medium | High | **HIGH** |
| **P1 (High)** | **T-07** | Exposed infrastructure secrets (`APP_KEY`, DB, R2 keys) in code, logs, or frontend bundles | Medium | Critical | **HIGH** |
| **P1 (High)** | **T-08** | Backup compromise or destructive wipe of database & backups | Low–Med | Critical | **HIGH** |
| **P2 (Medium)** | **T-09** | Insecure API access, mass assignment, or forged `organisation_id` injection | Medium | High | **MEDIUM–HIGH** |
| **P2 (Medium)** | **T-10** | SQL injection (including raw PostGIS geometry expressions) & XSS | Low–Med | High | **MEDIUM–HIGH** |
| **P2 (Medium)** | **T-11** | Unauthorised access to or misuse of technician location information | Medium | Medium | **MEDIUM** |

---

## 5. Detailed Threat Scenario Analysis

### T-01: Cross-Tenant Data Exposure (`Priority: CRITICAL`)
- **What could happen:** An authenticated user in FM Organisation A views, modifies, or exports client accounts, properties, assets, inspections, or work orders belonging to FM Organisation B, or a pooled Neon database connection retains Organisation A's session variable for a request made by Organisation B.
- **Assets affected:** `A-1`, `A-2`, `A-3`, `A-4`, `A-6`, `A-10`.
- **Likely attack path:** (1) Changing a resource UUID in a web route or `/api/v1` endpoint when a developer forgets a `.where('organisation_id', ...)` clause; (2) Passing `"organisation_id": "<Org-B-UUID>"` in a POST/PATCH payload; (3) Using session-level `SET app.org_id` instead of transaction-local `SET LOCAL app.org_id` over Neon PgBouncer transaction pooling; or (4) Running the app under a PostgreSQL table-owner/superuser role that bypasses RLS.
- **Potential business impact:** Catastrophic loss of customer trust, immediate pilot termination, contractual breach under the DPA, and NDPA 2023 regulatory reportable data breach.
- **Existing controls (Verified):** None in the codebase yet (`database/migrations/` currently contains only starter-kit user/session/cache/job tables; `docker-compose.yml` currently connects as superuser `pai`).
- **Required additional controls (`AD-3`, `AD-4`, `AD-6`, `AD-19`):**
  1. Resolve `TenantContext` strictly from the authenticated user's `memberships` row (`AD-3`).
  2. Enforce `TenantScope` on all 17 tenant models and stamp `organisation_id` via a `creating` hook that aborts on conflict (`AD-3`).
  3. Enable `ENABLE ROW LEVEL SECURITY` and `FORCE ROW LEVEL SECURITY` with `USING (organisation_id = NULLIF(current_setting('app.org_id', true), '')::uuid)` on all 17 tenant tables (`AD-4`).
  4. Connect at runtime as a non-owner PostgreSQL role (`pai_app`) with `NOBYPASSRLS` (`AD-4`).
  5. Wrap every request in a database transaction whose first statement is `set_config('app.org_id', ?, true)` (`SET LOCAL`) (`AD-4`).
- **How the control should be tested:** Automated 5-vector isolation matrix (`tests/Isolation/`) running on real PostgreSQL 17 + PostGIS as `pai_app`, asserting cross-tenant denial across HTTP, service layer, raw SQL, queued jobs, and signed URLs, plus negative tests with forged `organisation_id` payloads.
- **Current status & outstanding work:** **Outstanding** — Scheduled for **Story 1.2** (infrastructure) and **Story 6.1** (full CI gate). Also requires updating `phpunit.xml` and `.github/workflows/tests.yml` from SQLite to PostgreSQL 17 + PostGIS.

---

### T-02: Private Document or Photograph Exposure (`Priority: CRITICAL`)
- **What could happen:** Inspection photos, work-order completion photos, or exported client PDF/CSV reports are accessed by unauthenticated users or by users from another Organisation or unpermitted Client Account.
- **Assets affected:** `A-6`, `A-7`, `A-11`.
- **Likely attack path:** (1) Misconfiguring the S3/R2 bucket as public; (2) Storing predictable object paths without signed URLs; or (3) Generating a pre-signed URL in a controller without first verifying that the `Attachment` or `Report` belongs to the caller's `organisation_id` and `client_scope`.
- **Potential business impact:** Exposure of confidential client building interiors, security vulnerabilities at managed sites, and breach of customer confidentiality.
- **Existing controls (Verified):**
  - `FILESYSTEM_DISK=s3` with private bucket creation (`EnsureStorageBucketCommand`) and `EVIDENCE_SIGNED_URL_TTL_MINUTES=10` in `.env.example`.
  - **Verified** in `tests/Feature/StorageContractTest.php` against live RustFS: direct unauthenticated GET returns HTTP `403 AccessDenied`, valid 10-minute pre-signed URL succeeds, and expired pre-signed URL returns HTTP `4xx`.
- **Required additional controls (`AD-13`):**
  1. Namespace all R2 keys as `organisations/{org_id}/clients/{client_id}/{owner_type}/{owner_id}/{uuid}.{ext}`.
  2. Issue 10-minute signed URLs exclusively through `EvidenceService` (and `ReportService`) after resolving the record through `TenantScope`, `ClientScope`, and PostgreSQL RLS.
- **How the control should be tested:** Feature and isolation tests verifying that User A cannot obtain a signed URL for Organisation B's attachment or for an attachment in an out-of-scope Client Account (`returns 404`).
- **Current status & outstanding work:** Bucket-level privacy is **Implemented & Verified** (`Story 1.1`). `EvidenceService` tenant/client authorisation gateway is **Outstanding** (**Story 1.10**).

---

### T-03: Intra-Tenant Privilege Escalation, `client_scope` Bypass & Tampering with Immutable Evidence (`Priority: CRITICAL`)
- **What could happen:** (1) A user restricted to Client Account 1 views or mutates Properties, Assets, Inspections, or Work Orders belonging to Client Account 2 within the same FM Organisation; (2) A Technician completes a Work Order assigned to another Technician or approves their own Inspection; or (3) A user edits an already-approved Inspection or deletes photo evidence after a Work Order is marked `completed` (falsifying proof of work).
- **Assets affected:** `A-1`, `A-2`, `A-3`, `A-4`, `A-6`, `A-10`.
- **Likely attack path:** Calling REST `/api/v1` or Inertia endpoints directly with another Client Account's entity UUID; sending update/delete requests against locked inspections/attachments; or mutating `locations`, `asset_custody`, `work_order_events`, or `audit_events`.
- **Potential business impact:** Destroys the core value proposition of the platform ("defensible proof of work"), enabling internal fraud and billing disputes with property owners.
- **Existing controls (Verified):** None in the codebase yet.
- **Required additional controls (`AD-5`, `AD-8`, `AD-10`, `AD-11`, `AD-12`):**
  1. Denormalize immutable `client_account_id` onto all 7 client-owned tables and enforce global `ClientScope` (`AD-5`), returning `404` on out-of-scope access (`Story 1.4`).
  2. Enforce central 6-role Laravel Policies inside every Application Service method (`AD-12`, `Story 1.4`).
  3. Enforce `InspectionService` immutability once `approved_at != null` (`AD-10`, `Story 3.5`) and `WorkOrderService::transition()` guards requiring ≥1 timestamped photo and assignee/supervisor check (`AD-11`, `Story 3.9`).
  4. Block attachment deletion once the parent Inspection is approved or Work Order is `completed` (`AD-11`, `AD-13`, `Story 3.12`).
  5. Revoke PostgreSQL `UPDATE` and `DELETE` privileges from runtime role `pai_app` on `locations`, `asset_custody`, `work_order_events`, and `audit_events` (`AD-8`, `Story 1.2`).
- **How the control should be tested:** Role-matrix unit/feature tests (`Story 1.4`), cross-client scope tests (`Story 2.6`, `Story 2.10`), post-approval/post-completion mutation rejection tests (`Story 3.5`, `Story 3.12`), and DB privilege tests asserting `UPDATE`/`DELETE` on append-only tables fail at PostgreSQL level (`Story 1.2`).
- **Current status & outstanding work:** **Outstanding** — Scheduled across **Stories 1.2, 1.4, 3.5, 3.9, 3.12, and 5.1**.

---

### T-04: Insecure Background Jobs & Cross-Tenant Report Export Leakage (`Priority: CRITICAL`)
- **What could happen:** A queued report export job (`ReportService`) or scheduled task runs without `TenantContext` / `SET LOCAL app.org_id`, or retains the previous job's tenant context in the worker process, compiling another client's or organisation's data into a downloadable PDF/CSV report.
- **Assets affected:** `A-1`, `A-2`, `A-3`, `A-4`, `A-6`, `A-11`.
- **Likely attack path:** Dispatching a Laravel queued job that queries Eloquent models without serializing `organisation_id` and `user_id` or without resetting `TenantContext` between jobs on a long-running worker daemon.
- **Potential business impact:** Direct delivery of one customer's confidential portfolio and maintenance history to another customer.
- **Existing controls (Verified):** None in the codebase yet.
- **Required additional controls (`AD-14`, `AD-15`, `AD-21`):**
  1. Require all queued jobs to implement `TenantAware` (serializing `organisation_id` and `user_id`).
  2. Enforce a Queue Job Middleware that binds `TenantContext`, runs `handle()` inside a DB transaction with `set_config('app.org_id', ?, true)`, and clears `TenantContext` in `finally` (`AD-14`).
  3. Make unscoped tenant model queries throw a fatal exception (`AD-14`).
  4. Scope `ReportService::compile($clientAccount, $period)` strictly to a single verified `ClientAccount` (`AD-15`).
- **How the control should be tested:** Automated queued-job isolation vector in `tests/Isolation/` (`Story 1.2` / `Story 6.1`) and report compilation cross-client tests (`Story 4.1`, `Story 4.3`).
- **Current status & outstanding work:** **Outstanding** — Scheduled for **Story 1.2**, **Story 4.1**, and **Story 4.3**.

---

### T-05: Account Takeover, Stolen/Reused Passwords & Session Hijacking (`Priority: HIGH`)
- **What could happen:** An attacker compromises a staff member's or administrator's account via brute-force/credential stuffing, weak/breached passwords, or intercepting session cookies over insecure networks.
- **Assets affected:** `A-8`, and all assets accessible to that user (`A-1`–`A-7`).
- **Likely attack path:** Automated login stuffing against `/login`; use of passwords leaked in third-party breaches; or session cookie theft if `Secure`/`HttpOnly` flags or HTTPS are missing.
- **Potential business impact:** Unauthorised access to tenant operations, fraudulent work-order closures, or administrative tampering.
- **Existing controls (Verified):**
  - Login rate limiting (`5/min` per lowercase email + IP) and 2FA rate limiting (`5/min`) implemented in `App\Providers\FortifyServiceProvider` and verified in `tests/Feature/Auth/AuthenticationTest.php`.
  - Password hashing (`bcrypt` 12 rounds) and production password rules (`min(12)->mixedCase()->letters()->numbers()->symbols()->uncompromised()`) implemented in `App\Models\User` and `App\Providers\AppServiceProvider`.
  - Session cookies default to `http_only = true`, `same_site = 'lax'`, and `serialization = 'json'` (`config/session.php`).
- **Required additional controls (`AD-18`, `AD-22`, `AUTH.md`):**
  1. Disable open public self-registration (`Features::registration()`) so attackers cannot self-create accounts (`Story 1.3`).
  2. Enforce `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, and HSTS in staging and production (`ENV.md`, `Story 6.2`).
  3. Enforce suspended/inactive membership rejection on login and active requests (`Story 1.3`).
  4. Record authentication events in `audit_events` (`Story 1.11`).
- **How the control should be tested:** Rate-limit tests (`429 Retry-After`), suspended-membership login denial tests (`Story 1.3`), and production security header verification (`Story 6.2`).
- **Current status & outstanding work:** Partially **Implemented & Verified** (`Fortify` rate limiters, password hashing, session defaults). Public registration removal, membership status check, and audit logging are **Outstanding** (**Story 1.3**, **Story 1.11**).

---

### T-06: Malicious File Uploads & Headless Chromium PDF Rendering Exploits (`Priority: HIGH`)
- **What could happen:** (1) An attacker uploads an HTML, SVG, or PHP script disguised with a `.jpg` extension to execute stored XSS or server-side code; (2) An attacker uploads an HTML/script payload or injects HTML into a property/work-order field that executes inside headless Chromium (`spatie/laravel-pdf`) during PDF report generation, causing Server-Side Request Forgery (SSRF) or local file disclosure (`file:///etc/...`); or (3) Concurrent PDF exports exhaust the 4 GB VPS memory (`OOM` crash).
- **Assets affected:** `A-6`, `A-9`, `A-11`, and application availability.
- **Likely attack path:** Uploading spoofed MIME files to evidence endpoints; injecting `<script>` or `<iframe src="http://169.254.169.254/...">` into text fields rendered in PDF reports; or spamming the `Export PDF` button.
- **Potential business impact:** Stored XSS against supervisors/managers, SSRF exposing server metadata/files, or complete VPS outage during business hours.
- **Existing controls (Verified):** Files are stored on an external S3-compatible disk (`RustFS` / `R2`), never in Laravel's local `public/` webroot, preventing direct PHP script execution.
- **Required additional controls (`AD-13`, `AD-15`, `AD-21`, `AD-22`, `F-307`):**
  1. Server-side `finfo` magic-byte inspection in `EvidenceService`, enforcing a strict allowlist (`image/jpeg`, `image/png`, `image/webp`, `application/pdf` — no SVG, no HTML) and a `10 MB` cap (`AD-22`).
  2. Set `Content-Disposition: attachment` on non-image signed URLs (`AD-22`).
  3. Strict Blade auto-escaping (`{{ ... }}`) in PDF templates (`Story 4.5`) and restricting Chromium network access in `Pdf` wrapper to only the application's own assets and R2 signed thumbnail URLs.
  4. Rate-limit uploads (`30/min` per user) and report exports (`5/min` per org), enforce `ShouldBeUnique` on export jobs, and isolate PDF rendering to a dedicated `--queue=pdf` worker with `concurrency = 1` (`AD-15`, `AD-21`, `AD-22`).
- **How the control should be tested:** Upload tests with spoofed magic bytes (HTML renamed to `.jpg`, oversized files) asserting `422` rejection (`Story 1.10`, `Story 6.2`); rate-limit tests (`Story 4.3`, `Story 6.2`).
- **Current status & outstanding work:** **Outstanding** — Scheduled for **Story 1.10**, **Story 4.3**, **Story 4.5**, and **Story 6.2**.

---

### T-07: Exposed Infrastructure Secrets in Code, Logs, or Frontend Bundles (`Priority: HIGH`)
- **What could happen:** Database passwords, `APP_KEY`, Cloudflare R2 secret keys, or customer PII are accidentally committed to Git, bundled into Vite frontend JS via `VITE_*`, or written into application error logs/stack traces.
- **Assets affected:** `A-8`, `A-9`, `A-10`, `A-11`.
- **Likely attack path:** Leaving `APP_DEBUG=true` in staging/production; logging full `$request->all()` payloads; prefixing a secret with `VITE_`; or committing a `.env` file.
- **Potential business impact:** Full compromise of database and object storage across all tenants.
- **Existing controls (Verified):**
  - `.gitignore` excludes `.env`, `.env.backup`, `.env.production`, `/storage/*.key`, and `/auth.json`.
  - `.env.example` contains only local development placeholders.
  - `App\Models\User` marks `password`, `two_factor_secret`, `two_factor_recovery_codes`, and `remember_token` as `#[Hidden]`.
- **Required additional controls (`AD-17`, `ENV.md`, `Story 6.2`):**
  1. Sanitize uncaught database/PDO exceptions via the central exception handler so raw SQL and binding values are never returned to clients (`AD-17`, `Story 1.11`).
  2. Configure structured JSON logging that strips sensitive fields (`Story 1.11`).
  3. Run an automated secrets audit across Git history and built frontend assets (`public/build`) before pilot launch (`Story 6.2`).
- **How the control should be tested:** Exception-handler unit/feature tests verifying `QueryException` returns a sanitized `500` error envelope (`Story 1.11`), and secrets scan in `Story 6.2`.
- **Current status & outstanding work:** `.gitignore` and `.env.example` hygiene are **Implemented & Verified**. Exception sanitization and secrets scan are **Outstanding** (**Story 1.11**, **Story 6.2**).

---

### T-08: Backup Compromise or Destructive Wipe (`Priority: HIGH`)
- **What could happen:** An attacker who compromises the application container or R2 runtime credentials deletes or encrypts both the live database/evidence bucket and the backups, or backups cannot be restored during an outage.
- **Assets affected:** `A-10`, `A-11`.
- **Likely attack path:** Using the same Cloudflare R2 API token for both runtime `s3` storage and nightly `pg_dump` backups, or running destructive Artisan commands in production.
- **Potential business impact:** Permanent, unrecoverable loss of all customer records and evidence.
- **Existing controls (Verified):** `DB::prohibitDestructiveCommands(app()->isProduction())` is enabled in `App\Providers\AppServiceProvider` (blocks `migrate:fresh`, `migrate:reset`, `db:wipe` in production).
- **Required additional controls (`AD-20`, `Story 6.3`):**
  1. Enable Neon Point-in-Time Recovery (PITR) on the production database project.
  2. Write nightly portable `pg_dump` backups to a **separate, isolated Cloudflare R2 backup bucket using separate credentials** (`BACKUP_R2_*`) not accessible to the web `php-fpm` worker (`AD-20`).
  3. Execute and document a full staging restore drill before onboarding the first pilot customer (`Story 6.3`).
- **How the control should be tested:** End-to-end staging restore drill verified against RTO/RPO in **Story 6.3**.
- **Current status & outstanding work:** `prohibitDestructiveCommands` is **Implemented**. Neon PITR, isolated R2 backup bucket, and restore drill are **Outstanding** (**Story 6.3**).

---

### T-09: Insecure API Access, Mass Assignment, or Forged `organisation_id` Injection (`Priority: MEDIUM–HIGH`)
- **What could happen:** A client sends extra fields in a JSON request body (e.g., `organisation_id`, `client_account_id`, `approved_at`, `status`) to hijack ownership or bypass workflow rules on `/api/v1/*` or Inertia routes.
- **Assets affected:** `A-1`–`A-5`.
- **Likely attack path:** Tampering with JSON payloads in browser DevTools or API clients.
- **Existing controls (Verified):** `spatie/laravel-data` is installed; `User` model uses explicit `#[Fillable]`.
- **Required additional controls (`AD-1`, `AD-3`, `AD-7`, `AD-16`):**
  1. Bar `organisation_id` from all DTOs, FormRequests, and `$fillable` arrays; enforce model `creating` hook that stamps `TenantContext::organisationId()` and aborts on conflict (`AD-3`).
  2. Guard `properties.geom` (`AD-7`) and workflow status/approval columns from mass assignment.
- **How the control should be tested:** Negative tests in every domain service and HTTP controller proving forged `organisation_id` in request bodies is ignored/rejected (`Story 1.2`, `Story 2.1`, `Story 6.1`).
- **Current status & outstanding work:** **Outstanding** (**Story 1.2** onward).

---

### T-10: SQL Injection (Including Spatial Queries) & Cross-Site Scripting (XSS) (`Priority: MEDIUM–HIGH`)
- **What could happen:** Raw SQL in PostGIS coordinate/distance queries (`ST_MakePoint`, `ST_DWithin`) or unescaped user text in map popups/PDF reports allows SQL injection or script execution.
- **Assets affected:** `A-2`, `A-7`, `A-8`, `A-10`.
- **Likely attack path:** Supplying malformed coordinate inputs or injecting HTML/JS payloads into property names, addresses, or inspection notes.
- **Existing controls (Verified):** React 19 JSX auto-escaping; Eloquent prepared statements.
- **Required additional controls (`AD-7`, `AD-22`):**
  1. Centralize all spatial SQL inside `LocationService::setLocation()` and `Geo::distanceMetres()` / `Geo::withinMetres()` with strict `float` type-hinting and bound SQL parameters (`?`).
  2. Enforce Content Security Policy (CSP) headers (`AD-22`).
- **How the control should be tested:** Unit/feature tests on `Geo` helpers and `LocationService` (`Story 2.3`) and CSP header tests (`Story 6.2`).
- **Current status & outstanding work:** **Outstanding** (**Story 2.3**, **Story 6.2**).

---

### T-11: Unauthorised Access to Technician Location Information (`Priority: MEDIUM`)
- **What could happen:** Optional GPS coordinates captured when a technician uploads photo evidence (`FR-15`, `FR-21`) are exposed to unauthorized users or misused for continuous employee tracking outside work-order evidence.
- **Assets affected:** `A-5`, `A-7`.
- **Likely attack path:** Over-collecting background location data in the mobile browser, or exposing attachment metadata to users outside the permitted `Organisation` and `client_scope`.
- **Existing controls (Verified):** None in the codebase yet.
- **Required additional controls (`FR-15`, `FR-21`, `AD-5`, `AD-13`):**
  1. Capture device coordinates **only** at the moment an explicit evidence photo or property location update is submitted — never track continuous background location.
  2. Keep GPS capture strictly optional and non-blocking on evidence (`FR-15`, `FR-21`).
  3. Restrict access to attachment provenance (including coordinates) to authenticated users within the same `Organisation` and permitted `client_scope` (`AD-5`, `AD-13`).
- **How the control should be tested:** Feature tests verifying missing GPS never blocks work-order completion (`Story 3.9`, `Story 3.11`) and attachment metadata respects `TenantScope` and `ClientScope` (`Story 1.10`).
- **Current status & outstanding work:** **Outstanding** (**Story 1.10**, **Story 3.9**, **Story 3.11**).

