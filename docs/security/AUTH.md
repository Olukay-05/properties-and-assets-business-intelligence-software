# Authentication and Access Management (`AUTH.md`)

**Status:** Active Security Foundation  
**Last Updated:** 2026-11-05  
**Scope:** Cloud Crafters Property & Asset Intelligence (Phase 1 MVP & Paid Pilot)  
**Primary References:** `prd.md` (`FR-19`, `FR-20`, `NFR-5.1`), `ARCHITECTURE-SPINE.md` (`AD-3`, `AD-5`, `AD-12`, `AD-22`), `docs/knowledge-base/api-conventions.md`, `epics.md` (Stories 1.2, 1.3, 1.4, 5.1)

---

## 1. Status Legend & Implementation Truthfulness

Every control in this document is labelled using these five states:

- **Requirement:** What the platform must do to satisfy product and security obligations.
- **Decision:** What has been agreed architecturally in `ARCHITECTURE-SPINE.md` and `docs/development-plan-review.md`.
- **Implemented:** What currently exists in the repository code (Story 1.1 starter-kit scaffold).
- **Verified:** What has been proven by automated tests or direct inspection in the repository.
- **Outstanding:** What still needs to be built, reconfigured, tested, or approved in upcoming stories.

---

## 2. Authentication vs. Authorisation

The platform strictly separates **authentication** (who the actor is) from **authorisation** (which tenant, client accounts, and actions that actor is permitted to access):

| Dimension | Question Answered | Mechanism | Enforcement Layer |
| --- | --- | --- | --- |
| **Authentication** | *Who is making this request?* | Laravel Fortify + Laravel Sanctum first-party SPA cookie sessions (and Sanctum personal access tokens for first-party operator tooling). | HTTP Middleware (`auth`, `auth:sanctum`) before controller or service execution. |
| **Tenant Resolution** | *Which FM Organisation does this session belong to?* | `TenantContext` resolved strictly from the authenticated user's active `memberships` record (`AD-3`), paired with PostgreSQL `SET LOCAL app.org_id` (`AD-4`). | Tenancy Middleware + Eloquent Global Scope + PostgreSQL Row-Level Security (`FORCE RLS`). |
| **Client Scoping** | *Which Client Accounts inside that Organisation may this user see?* | `memberships.client_scope` (`null` for unrestricted organisation-wide access, or an array of permitted `client_account_id` UUIDs) enforced via global `ClientScope` (`AD-5`). | Eloquent Global Scope (`ClientScope`) + Application Service checks. |
| **Role Authorisation** | *Is this user allowed to perform this specific operation on this entity?* | Six Phase 1 roles evaluated as a permission union via central Laravel Policies inside Application Services (`AD-12`). | Application Service layer (`app/Domain/<Module>/Services`) before any state mutation or data return. |

---

## 3. Current Implementation Status Summary (Story 1.1 Scaffold)

| Area | Status | Reality in Codebase Today |
| --- | --- | --- |
| **Starter-kit Auth (`laravel/fortify`)** | **Implemented / Verified** | Installed (`laravel/fortify ^1.37.2`). Login, logout, password reset, email verification, 2FA, and passkey routes/tests ship from the starter kit (`tests/Feature/Auth/*` passes). |
| **UUID Primary Keys on `users`** | **Implemented (Story 1.2)** | `users.id` is a `uuid` primary key (`HasUuids`), and `sessions.user_id` / the tenant tables' `user_id` references are `uuid` (AD-6). Verified by the suite running against PostgreSQL. |
| **Public Self-Registration** | **Implemented: disabled (Story 1.2)** | `Features::registration()` is removed from `config/fortify.php`, along with the register view, the `CreateNewUser` wiring and the registration test; the register link is gone from the welcome and login pages. Uninvited visitors cannot create a `users` row. |
| **WebAuthn Passkeys** | **Implemented: removed (Story 1.2)** | `Features::passkeys()`, `PasskeyAuthenticatable`, the `passkeys` migration, the passkey components and the rate limiter are gone, per the approved decision in §5.4. TOTP 2FA remains. |
| **Sanctum SPA & API Wiring** | **Outstanding** | `laravel/sanctum ^4.0` is installed in `composer.json`, but `statefulApi()`, `HasApiTokens`, and `/api/v1` routes are not wired yet (Story 1.3). |
| **Organisations & Memberships** | **Implemented (Story 1.2)** | `organisations` and `memberships` tables (UUID keys), models, factories and `TenantContext` resolution exist. A user's active membership is resolved on every authenticated request and binds `app.org_id` for PostgreSQL RLS. A user with no active membership is refused with `403`. |
| **6-Role Policies & `ClientScope`** | **Outstanding** | `memberships.roles` stores and validates the six Phase 1 roles (`MembershipRole`), and `client_scope` is stored on the membership and exposed on `TenantContext`, but role abilities and `ClientScope` enforcement are scheduled for Story 1.4 and Story 5.1. |

---

## 4. User Registration, Account Creation, and Onboarding Model

### 4.1 Intended Onboarding Model (No Open Public Tenant Sign-Up in Phase 1)
- **Requirement:** Do not allow arbitrary anonymous internet users to self-register into the application without an organisation context. Every user in the platform must belong to at least one FM `Organisation` via an explicit `memberships` record (`FR-19`, `FR-20`).
- **Decision:** Phase 1 targets high-touch paid pilots (`docs/development-plan-review.md` §9–10). There is no self-service billing or public tenant sign-up portal in Phase 1.
  1. **Tenant & Owner Provisioning:** Initial `organisations` records and the initial `Organisation owner` (and `Platform admin`) user/membership are provisioned via an audited administrative Artisan command or seeder during pilot onboarding.
  2. **Team Member Onboarding:** Within an Organisation, the `Organisation owner` (or `Platform admin`) adds staff and contractors (`Operations manager`, `Site supervisor`, `Technician`, `Contractor`) via the Membership Administration service (`MembershipService`, Story 5.1 / `MembershipsIndex`, Story 5.2), assigning their role set and `client_scope`.
- **Implemented:** The starter kit currently has `Features::registration()` enabled in `config/fortify.php` and `App\Actions\Fortify\CreateNewUser`, which creates a standalone `users` row with no `memberships` record.
- **Outstanding (Story 1.3 & Story 5.1):**
  - Remove or feature-gate public `Features::registration()` in `config/fortify.php` so uninvited visitors cannot create orphan `users` accounts.
  - Decide and implement the exact staff credential bootstrap flow in Story 1.3 / Story 5.1 (either admin-created user with a forced password-reset / signed invitation link, or token-gated invitation onboarding).

---

## 5. Login, Logout, Password Hashing, and Password Reset

### 5.1 Login and Credential Error Handling
- **Requirement:** Users authenticate with email and password (`Story 1.3`). Failed login attempts must never reveal whether the email address exists or the password was wrong.
- **Decision:**
  - Usernames are normalized to lowercase (`'lowercase_usernames' => true` in `config/fortify.php`).
  - Authentication endpoints are rate-limited to **5 attempts per minute per IP + email** (`AD-22`), returning HTTP `429 Too Many Requests` with a `Retry-After` header.
  - Failed login attempts must return the 3-part editorial error message (`EXPERIENCE.md` / Story 1.3) and record an authentication audit event (`AD-18`).
- **Implemented & Verified:**
  - `FortifyServiceProvider` registers `RateLimiter::for('login')` at 5 requests per minute keyed by `Str::lower($request->input('email')).'|'.$request->ip()`.
  - Login and logout functional tests exist and pass in `tests/Feature/Auth/AuthenticationTest.php`.
- **Outstanding (Story 1.3 & Story 1.11):**
  - Update login failure copy to match the 3-part editorial error formula (`Story 1.3`).
  - Block authentication when the user's membership status is `suspended` or `inactive` and display a clear access notice (`Story 1.3`).
  - Record login success, login failure, and logout events via `AuditService::record()` (`Story 1.11`).

### 5.2 Password Hashing and Complexity Rules
- **Requirement:** Passwords must be hashed using a strong one-way adaptive hashing algorithm (bcrypt or Argon2id) and never logged or exposed in responses (`SECURITY.md`).
- **Decision:** Use Laravel's native `hashed` cast with `BCRYPT_ROUNDS=12` in production/local (`.env.example`).
- **Implemented & Verified:**
  - `App\Models\User` casts `'password' => 'hashed'` and hides `'password'`, `'two_factor_secret'`, `'two_factor_recovery_codes'`, and `'remember_token'`.
  - `App\Providers\AppServiceProvider` configures `Password::defaults()` for production: minimum 12 characters, mixed case, letters, numbers, symbols, and `uncompromised()` (HaveIBeenPwned k-anonymity check).
- **Outstanding:**
  - Enforce a consistent baseline minimum password length (e.g. `Password::min(12)->mixedCase()->numbers()`) in `local` and `staging` environments as well (keeping `uncompromised()` restricted to environments with outbound internet access) so password rules are tested identically across environments.

### 5.3 Password Reset and Account Recovery
- **Requirement:** Users must be able to reset forgotten passwords securely via short-lived, single-use tokens sent to their registered email.
- **Implemented & Verified:**
  - Configured in `config/auth.php` (`password_reset_tokens` table, `expire` = 60 minutes, `throttle` = 60 seconds) and `App\Actions\Fortify\ResetUserPassword`.
  - Verified by `tests/Feature/Auth/PasswordResetTest.php`.
- **Outstanding:**
  - Ensure password reset events are logged in `audit_events` (`Story 1.11`) and invalidate other active sessions upon password change.

### 5.4 Account Verification, Two-Factor Authentication (2FA), and Passkeys
- **Implemented:** The starter kit includes email verification (`MustVerifyEmail`), TOTP two-factor authentication (`TwoFactorAuthenticatable`), and WebAuthn passkeys (`PasskeyAuthenticatable` + `passkeys` migration).
- **Decision (Approved 2026-11-05):**
  - **Keep TOTP 2FA (`Features::twoFactorAuthentication()`):** Retain TOTP 2FA as an optional account security hardening feature (and recommended for `Organisation owner` and `Platform admin` accounts per `docs/updates.md` §14). Its columns (`two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`) live directly on `users` with zero extra table overhead.
  - **Disable WebAuthn Passkeys (`Features::passkeys()`) for Phase 1 — done in Story 1.2:** `Features::passkeys()` was removed from `config/fortify.php`, `PasskeyAuthenticatable` / `PasskeyUser` were removed from `App\Models\User`, `2024_01_01_000000_create_passkeys_table.php` was dropped, and the passkey components, actions and `well-known` route were removed. The `laravel/passkeys` package remains a hard dependency of `laravel/fortify`; it is simply not wired up. This avoids RP-ID/device friction on the shared and mid-range Android devices field technicians use during the MVP.

---

## 6. Session Management and Expiration

- **Requirement:** Browser sessions must be stored server-side, bound to HTTP-only cookies, protected against session fixation and CSRF, and invalidated on logout or membership suspension.
- **Decision:**
  - Session driver: `database` (`sessions` table) in local, staging, and production (`.env.example`, `config/session.php`).
  - Session lifetime: `120` minutes idle timeout (`SESSION_LIFETIME=120`).
  - Cookie attributes: `http_only = true`, `same_site = 'lax'`, `secure = true` in staging and production (`SESSION_SECURE_COOKIE=true`), and `serialization = 'json'` (preventing PHP object unserialization gadget-chain attacks).
- **Implemented:** `config/session.php` sets `http_only => true`, `same_site => 'lax'`, `serialization => 'json'`, and `lifetime => 120`.
- **Implemented (Story 1.2):** `sessions.user_id` is a `foreignUuid` column (AD-6). The `sessions` table remains a framework infrastructure table with no RLS policy, so session reads during authentication never depend on a bound tenant.
- **Outstanding:**
  - Set `SESSION_ENCRYPT=true` in staging and production environments (`ENV.md`).

---

## 7. Account Suspension, Deactivation, and Membership Lifecycle

- **Requirement:** An Organisation Owner must be able to suspend or deactivate a team member's access immediately without destroying historical audit trails, inspections, custody records, or work orders (`AD-8`, `Story 1.3`, `Story 5.1`).
- **Decision:**
  - User records (`users`) are never hard-deleted when staff leave an organisation, because historical `locations`, `asset_custody`, `inspections`, `work_order_events`, `attachments`, and `audit_events` reference the acting `user_id` (`AD-8`).
  - Access is controlled via `memberships.status` (e.g. `active`, `suspended`, `inactive`).
  - A user whose `memberships.status` is not `active` cannot authenticate into that Organisation (`Story 1.3`), and any active session or Sanctum token must be rejected by the Tenancy Middleware on the next request.
  - **Last-Owner Guard (`Story 5.1`):** Removing or suspending the last active `Organisation owner` membership in an Organisation is rejected with domain exception code `cannot_remove_last_organisation_owner`.
- **Implemented (Story 1.2):** the tenancy middleware refuses any membership whose `status` is not `active`, and also refuses memberships in a suspended organisation, before any tenant query runs.
- **Outstanding:** last-owner guard in `MembershipService` (`Story 5.1`).

---

## 8. Organisation Membership & Active Organisation Context

- **Requirement:** Every authenticated request and background job must operate within a single, unambiguous `Organisation` context (`FR-19`).
- **Decision (`AD-3`, `AD-4`):**
  - `memberships` links `user_id` to `organisation_id` and stores the member's `role` (or role set), `client_scope`, and `status`.
  - On every request, Tenancy Middleware resolves the user's active membership from the authenticated session, initializes the immutable `TenantContext` singleton (`organisation_id`, `user_id`, `roles`, `client_scope`), opens a database transaction, and executes `SET LOCAL app.org_id = '<organisation_id>'`.
  - **Hard Rule:** `organisation_id` is never accepted from request bodies, query strings, headers, or route parameters to select or switch tenant ownership.
  - **Multi-Organisation Users:** In Phase 1, each FM company is an independent tenant. If a user (such as a `Platform admin` performing audited support per `AD-4`) holds memberships in more than one Organisation, the active membership ID is stored in the server-side session and verified against `memberships` (`WHERE user_id = :auth_user_id AND status = 'active'`) before binding `TenantContext`.
- **Implemented (Story 1.2):** `App\Infrastructure\Tenancy\ResolveTenantContext` (middleware alias `tenant`, appended to the `web` group) calls `TenantContextService::resolveFor()`, binds the immutable `TenantContext`, and wraps the request in a transaction bound to that tenant. Users with more than one active membership must have an active membership selected in the session (`tenancy.active_membership_id`), which is re-verified against the resolved membership list on every request — a stale or forged value is refused rather than falling back to another tenant.
- **Outstanding:** membership selection UI for multi-organisation users (Platform Admin support flow) belongs to Story 5.1.

---

## 9. Application Roles, Permissions, and Client Scope

### 9.1 The Six Phase 1 Roles (`FR-20`, `AD-12`)
Roles are **not** 1:1 with employees; in smaller FM firms, one user may hold multiple roles (`prd.md` §2.1). A membership holding multiple roles inherits the **union** of their permitted abilities (`AD-12`, `Story 1.4`).

| Role | Scope Level | Permitted Capabilities (Phase 1 Summary) |
| --- | --- | --- |
| **Platform admin** | Audited target Organisation membership (`AD-4`) | Platform support and tenant onboarding. Never bypasses PostgreSQL RLS at runtime; acts only through an explicit, audited membership in the target Organisation. |
| **Organisation owner** | Organisation-wide (`client_scope = null`) | Full administrative and operational control within the Organisation: manage `memberships` (roles, `client_scope`, status), view `audit_events`, execute NDPA data-protection workflows (`FR-23`), plus all Operations Manager capabilities. |
| **Operations manager** | Organisation-wide or restricted `client_scope` | Manage `client_accounts` (`FR-1`), register/update `properties`, `locations`, `site_zones`, `assets`, and `asset_custody` (`FR-2`–`FR-8`), manage `inspection_templates` (`FR-10`), schedule/run/approve `inspections` (`FR-9`, `FR-11`), triage `maintenance_requests` and create/assign/manage `work_orders` (`FR-12`–`FR-15`), compile and export client `reports` (`FR-17`, `FR-18`). |
| **Site supervisor** | Assigned `client_scope` (or Organisation-wide) | View scoped properties, zones, assets, and custody; schedule, conduct, and **approve/sign off** `inspections` (`FR-9`, `FR-11`); raise `maintenance_requests` (`FR-12`); create, assign, progress, complete, and sign off `work_orders` (`FR-13`–`FR-15`); upload and view scoped evidence (`FR-21`). Cannot manage `client_accounts`, `memberships`, or `audit_events`. |
| **Technician** | Assigned `client_scope` | Read-only view of scoped `properties`, `site_zones`, and `assets` (`EXPERIENCE.md` §Surface Inventory); view `work_orders` assigned to them within their `client_scope`; record partial progress (`in_progress`, `on_hold`) and complete assigned `work_orders` with mandatory photo evidence (`FR-14`, `FR-15`, `FR-21`). Cannot complete a work order assigned to another technician (`FR-15`), approve inspections, or export client reports. |
| **Contractor** | Assigned `client_scope` + assigned `work_orders` only (minimal role) | View and progress/complete only the specific `work_orders` assigned to them within their `client_scope`, including uploading completion evidence (`FR-14`, `FR-15`, `FR-21`). No access to organisation settings, other technicians' work orders, client account management, or report compilation. |

*Note: Client read-only portal access is explicitly **deferred** in Phase 1 (`mvp-scope.md`). Property owners receive exported PDF/CSV reports.*

### 9.2 Client Scope (`AD-5`) and 404 vs. 403 Semantics (`EXPERIENCE.md` State 10)
- **Requirement:** Users restricted to specific Client Accounts (`memberships.client_scope != null`) must never see or mutate records belonging to other Client Accounts within the same Organisation (`FR-8`, `FR-20`).
- **Decision (`AD-5`, `Story 1.4`):**
  - Every client-owned table (`properties`, `site_zones`, `assets`, `inspections`, `maintenance_requests`, `work_orders`, `attachments`) carries a denormalised, immutable `client_account_id` stamped from its parent at creation.
  - A global Eloquent `ClientScope` filters all queries by `whereIn('client_account_id', $clientScope)` whenever `TenantContext::clientScope()` is non-null.
  - **Anti-Probing Rule:** Direct URL or API access to a UUID outside the actor's `organisation_id` or `client_scope` must return **`404 Not Found`** (never `403`), preventing cross-tenant and cross-client UUID existence probing.
  - Attempting an unauthorised action on a visible, in-scope record (e.g., a Technician attempting to complete a Work Order assigned to a colleague) throws a `DomainException` mapped to **`403 Forbidden`** with the 3-part explanation.
- **Outstanding:** Implement central role Policies and `ClientScope` in Story 1.4, and role/scope administration in Story 5.1.

---

## 10. Authentication Requirements for API Endpoints & Future Integrations

### 10.1 Phase 1 REST API (`/api/v1`)
- **Requirement:** All `/api/v1/*` endpoints must require authentication, enforce the exact same `TenantContext`, `ClientScope`, and Service-Layer Policies as Inertia web routes, and never accept `organisation_id` from client input (`docs/knowledge-base/api-conventions.md`).
- **Decision (`D3`, `AD-1`, `AD-22`):**
  - **Audience:** First-party only (the platform's own React/Inertia frontend and first-party operator tooling).
  - **Mechanism:** Laravel **Sanctum** (`auth:sanctum`) supporting stateful SPA cookie sessions (with CSRF protection) and first-party personal access tokens (`personal_access_tokens`).
  - **Rate Limiting:** General `/api/v1` endpoints are rate-limited to **120 requests per minute per user** (`AD-22`).
- **Outstanding:** Wire `routes/api.php` under `/api/v1` with `auth:sanctum` and Tenancy Middleware in Story 1.2 / Story 1.3.

### 10.2 Future External Integrations & AI Agents (Deferred to Phase 3)
- **Decision (`D3`, `docs/development-plan-review.md` §5.2 & §12):**
  - External integrators, third-party webhooks, OAuth 2.1 (`laravel/passport`), `api_clients`, and Model Context Protocol (`MCP`) agent tools are **strictly deferred to Phase 3**.
  - Do **not** create `api_clients`, `mcp_tools`, `tool_invocations`, or `approvals` tables in Phase 1.
  - When introduced in Phase 3, external agents and integrators will authenticate as principal-bound service accounts via OAuth 2.1 (`Passport`) with explicit tenant and client scopes, invoking the exact same Application Services and obeying the same RLS policies (`AGENTS.md` Rules 1–3).

