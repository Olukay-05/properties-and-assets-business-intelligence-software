---
title: 'Tenant context and RLS isolation infrastructure'
type: 'feature'
created: '2026-11-05'
status: 'done'
route: 'dispatch'
baseline_commit: '6606b2c583e61fcea683418a1377ef334bdd6c79'
review_loop_iteration: 0
context:
  - '{project-root}/docs/security/RLS-POLICIES.md'
  - '{project-root}/docs/security/AUTH.md'
  - '{project-root}/docs/security/ENV.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** No tenant isolation exists. There is no `organisations` or `memberships` table, no runtime database role separate from the container superuser, and no RLS anywhere. Every later story (auth, registry, inspections, evidence, audit) is blocked, and any tenant table added before this lands would be permanently unprotected.

**Approach:** Introduce `organisations`, `memberships`, and a request-scoped `TenantContext` resolved only from the authenticated session, backed by a two-role PostgreSQL setup (`pai_owner` for DDL, `pai_app` as a `NOBYPASSRLS` non-owner runtime role). Migrate `users.id` and `sessions.user_id` to UUID, apply `ENABLE`+`FORCE` RLS with the fail-closed `NULLIF(current_setting('app.org_id', true), '')::uuid` policy to the tenant tables this story creates, and move the test suite onto real PostgreSQL + PostGIS.

## Boundaries & Constraints

**Always:**
- Tenant identity comes only from the authenticated user's active membership. `organisation_id` never appears in `$fillable`, Spatie `Data`, FormRequests, or query parameters.
- Every request transaction binds tenant state via `set_config('app.org_id', ?, true)`; the binding is transaction-scoped for Neon transaction pooling.
- The runtime role owns nothing, holds `NOBYPASSRLS`, and appends-only on append-only tables.
- Fail-closed: an unset or empty `app.org_id` returns zero rows rather than erroring.
- Isolation is proven at HTTP, service layer, and raw SQL under the non-owner role, plus forged-payload negative tests.

**Never:**
- No authentication UI, login flow, or Sanctum token wiring — that is Story 1.3. This story resolves context for an already-authenticated user.
- No `client_accounts`, `properties`, or other client-owned tables — those are Epic 2. `ClientScope` is not built here.
- No role-ability matrix or Policies — Story 1.4. Membership `roles` are stored and read, not authorised against.
- No `stancl/tenancy` or any tenancy package; no `withoutGlobalScopes()` in production paths.
- No switching tests to SQLite or a superuser connection to make them pass.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| HAPPY_PATH | User of organisation A with active membership authenticates | `TenantContext` binds A; queries see A's rows only | N/A |
| NO_MEMBERSHIP | Authenticated user with zero memberships | Request rejected before any tenant query | Clear access-denied response, no tenant rows touched |
| INACTIVE_MEMBERSHIP | Membership status `suspended` or `inactive` | Context not bound | Rejected; no partial query execution |
| FORGED_ORG_ID | Body or query carries `organisation_id` of B while session is A | Value ignored; record stamped with A | No error leakage; forged key never reaches the model |
| CONFLICTING_STAMP | Model carries a conflicting `organisation_id` at create | Creation aborted | Domain exception, never a silent overwrite; recorded as deliberate |

**Resolved decisions (human, 2026-11-05):**
- **Membership bootstrap:** membership lookup before a tenant is bound goes through a narrow `SECURITY DEFINER` function owned by `pai_owner`, returning only membership id, organisation id, roles, client scope and status for a given user id. No `app.user_id` policy is added to `memberships`; its policy stays strictly tenant-scoped.
- **RLS scope:** this story delivers the isolation mechanism (roles, helper, context, middleware, scopes) and applies RLS to the tenant tables it creates — `organisations` and `memberships`. The remaining 15 tenant tables belong to Epics 2–5 and must use the same helper, never hand-written policy SQL. Privilege ACs are scoped to tables this story creates.
- **UUID migration:** the original `users`/`sessions` migration is rewritten in place to UUID keys. Nothing is deployed and the local database is disposable, so no data-preserving migration is needed; `migrate:fresh` is expected locally.
- **Test database:** tests run against a dedicated `property_asset_intelligence_test` database created by a setup script, under `pai_app`. The development database is never used by the test suite.

</frozen-after-approval>

## Code Map

- `bootstrap/app.php:17-25` — `withMiddleware` closure; only `encryptCookies` and one `web(append: ...)` call. Add the tenancy middleware alias here, appended after Inertia so the authenticated session is available.
- `app/Models/User.php:14,30,35` — `#[Fillable]` / `#[Hidden]` use Laravel 13 attribute syntax (grep for `$fillable` misses them). Integer `id`; no `HasUuids`. `PasskeyAuthenticatable` sits outside any chisel fence.
- `config/fortify.php:170-172,184-188` — `Features::registration()` and `Features::passkeys()` both enabled inside chisel fences; AUTH.md requires both removed. Related: `:122-124`, `:140-156`, `FortifyServiceProvider.php:109-115`, `database/migrations/2024_01_01_000000_create_passkeys_table.php`.
- `config/database.php:87-100` — `pgsql` block has `url`, `search_path`, `sslmode`, no `options`. `DB_URL` is global across all connections and pinned empty at `phpunit.xml:28`. Add a separate connection rather than mutating this block.
- `config/session.php:76` — `SESSION_CONNECTION` is unset, so sessions use the default connection; the `sessions` table must stay RLS-exempt.
- `database/migrations/0001_01_01_000000_create_users_table.php:15,32` — `users.id` `$table->id()`, `sessions.user_id` `foreignId()->nullable()`; both bigint.
- `app/Providers/AppServiceProvider.php:17-23,28-31` — `register()` holds the only singleton; `boot()` has no DB listeners. Bind the context singleton here.
- `tests/Pest.php:18-19` — `Isolation` already bound to `Tests\TestCase`, but `phpunit.xml:7-14` declares only `Unit` and `Feature`. The directory holds only `.gitkeep`.
- `phpunit.xml:26-28` — `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `DB_URL=""`.
- `docker-compose.yml:28-44` — `pai-postgres` on host 5433; only one role (`pai`, the superuser) exists. `storage` (RustFS) at `:46-63`.
- `tests/Feature/StorageContractTest.php:23-37` — skips on missing config but hard-fails if RustFS is down.
- `app/Infrastructure/Tenancy/` — `.gitkeep` only. `app/Domain/Tenancy/{Data,Enums,Exceptions,Models,Policies,Services}` — `.gitkeep` only.
- `config/typescript-transformer.php:24-32` — auto-discovers `app_path('Domain')`; any `TenantContextData` will appear in `resources/js/types/generated/types.d.ts`.
- `.github/workflows/tests.yml` — no Postgres service, PHP 8.3 while `composer.json` requires `^8.4`.

## Tasks & Acceptance

**Execution:**
- [x] `docker/postgres/init/` + `docker-compose.yml` -- provision `pai_owner` (DDL) and `pai_app` (`NOBYPASSRLS`) roles on first boot -- runtime must never be the superuser; compose is the only place roles can be created before migrations.
- [x] `database/migrations/*_create_organisations_and_memberships_table.php` -- `organisations` and `memberships` with UUID keys, `status`, `roles`, `client_scope jsonb` -- the two tables this story owns; `memberships` is the first RLS-protected tenant table.
- [x] `database/migrations/*` -- `SECURITY DEFINER` membership lookup function, `SET search_path`, returning membership id, organisation id, roles, client scope and status -- the only pre-tenant read path.
- [x] `database/migrations/*_convert_users_and_sessions_to_uuid.php` (or rewrite the base migration) -- `users.id`, `sessions.user_id`, `passkeys.user_id` to uuid -- AD-6; bigint breaks the `::uuid` RLS cast.
- [x] `app/Infrastructure/Tenancy/` -- `TenantContext` (immutable, request-scoped), `TenantScope`, `HasTenant` trait with the conflict-aborting `creating` hook, `ResolveTenantContext` middleware, `TenantContextService` for the membership bootstrap -- all isolation logic in one place, callable from services.
- [x] `database/migrations/*` helper class -- reusable `enableTenantIsolation(string $table)` applying `ENABLE`+`FORCE` RLS and the policy -- later stories must not hand-write policy SQL.
- [x] `app/Providers/TenancyServiceProvider.php` + `AppServiceProvider` -- bind the context singleton, register the middleware alias -- single composition point.
- [x] `phpunit.xml` + `tests/Isolation/` -- PostgreSQL connection, `Isolation` testsuite entry, isolation matrix tests for the three vectors plus forged-input negatives -- AGENTS.md §6 gate.
- [x] `config/fortify.php`, `app/Models/User.php`, `app/Providers/FortifyServiceProvider.php`, passkeys migration -- remove passkeys and public registration -- AUTH.md decision approved 2026-11-05.
- [x] `.env.example`, `docs/security/RLS-POLICIES.md`, `docs/security/ENV.md`, `docs/security/AUTH.md` -- update status from Outstanding to Implemented/Verified for what this story delivers -- standing rule 5.
- [x] `.github/workflows/tests.yml` -- add a postgis service container and align PHP to 8.4 -- CI gate must run the same database as production.

**Acceptance Criteria:**
- Given two organisations A and B with members, when a user of A authenticates, then `TenantContext` reports A's organisation id, user id, roles and client scope, resolved from the membership and never from request input.
- Given a request containing a forged `organisation_id` of B, when a record is created, then the row is stamped with A's organisation id and the forged value is discarded.
- Given a model loaded with a conflicting `organisation_id`, when it is saved, then creation aborts with a domain exception rather than overwriting.
- Given a raw SQL query on a tenant table under the `pai_app` connection, when `app.org_id` is set to A, then zero rows belonging to B are returned; when `app.org_id` is unset or empty, then zero rows are returned for every tenant.
- Given the database after migrations, when RLS state and role privileges are inspected, then every tenant table created by this story has `relrowsecurity` and `relforcerowsecurity` true, `pai_app` has `rolbypassrls` false and does not own those tables, and `pai_app` lacks `UPDATE`/`DELETE` on append-only tables.
- Given a user with no membership or a non-active membership, when they make a request, then no tenant context is bound and no tenant query executes.
- Given the full test suite, when run under the non-owner role against PostgreSQL 17 + PostGIS, then it passes and no test falls back to SQLite or a superuser connection.

## Implementation Notes

### Divergences from the Code Map (and why)

- **A fourth role, `pai_bootstrap`.** The frozen decision said the lookup function would be owned by `pai_owner`. In practice PostgreSQL rejects that: `FORCE ROW LEVEL SECURITY` binds the table owner too, `SET row_security = off` is itself refused inside the function (`query would be affected by row-level security policy`), and making `pai_owner` a `BYPASSRLS` role would defeat the purpose of `FORCE`. The function is therefore owned by `pai_bootstrap`: `NOLOGIN`, `NOSUPERUSER`, the only `BYPASSRLS` role in the cluster, holding `SELECT` on `memberships` and `organisations` only. `pai_owner` gains membership in it solely so `ALTER FUNCTION ... OWNER TO` succeeds. This keeps `pai_owner` and `pai_app` strict `NOBYPASSRLS`.
- **Roles are provisioned outside migrations.** `pai_owner` deliberately has no `CREATEROLE` (a role that can mint roles is an escalation path), so migrations cannot create `pai_bootstrap`; they assert it exists and fail with an actionable message if it does not. Provisioning lives in `docker/postgres/init/01-create-roles.sh` (first boot) and `tools/create-test-database.php` (test database, idempotent).
- **`enableTenantIsolation(string $table)` is called after `Schema::create()`**, not inside the blueprint callback: the table does not exist yet when the callback runs. Both run in the migration's transaction, so there is still no window in which the table is queryable without RLS.
- **The migrator is pinned to the `migrations` connection** in `TenancyServiceProvider`, and `RefreshDatabase` passes `--database=migrations` (`tests/TestCase.php`). The runtime role cannot execute DDL, so this is mandatory rather than convenient.
- **`actingAs()` combined with an appended `web` middleware** means every authenticated feature test now needs a real membership. `UserFactory::withTenant()` provides one; `tests/Isolation/` seeds two tenants explicitly.
- **Self-service account deletion was removed.** `ProfileController::destroy()` now aborts with `403`: user rows are never hard-deleted (AUTH.md §7) and `pai_app` holds no `DELETE` on `users` (RLS-POLICIES.md §4.1). The starter-kit's two delete tests were replaced with one that asserts the refusal.
- **`ProfileValidationRules` takes `?string $userId`**, not `?int`: user ids are UUIDs now, and the starter kit's `int` type-hint produced a 500 on profile update.
- **Passkeys package retained.** `laravel/passkeys` is a hard dependency of `laravel/fortify`; the *feature* is removed from config, the model, the migration, the routes and the frontend, but the package stays installed.
- **Chisel defaults updated** so a future `chisel` run cannot re-enable registration or passkeys.

### Known gaps

- PostGIS was **not available** on the verification machine (PostgreSQL 18, no PostGIS extension); nothing this story writes uses geometry, but the suite has not yet run against PostGIS. CI uses `postgis/postgis:17-3.5`.
- Pint could not be executed here (see Verification).

## Spec Change Log

- 2026-11-05 — Story 1.2 implemented. Frozen decisions honoured; the one substantive divergence (a dedicated `pai_bootstrap` role instead of `pai_owner` owning the lookup function) is recorded above and forced by PostgreSQL semantics, not by preference. Human sign-off on that divergence is still outstanding.

## Review Triage Log

Three review lenses (blind hunter, edge-case hunter, verification gap) plus independent verification by the orchestrating agent against `postgis/postgis:17-3.5`. Reproduced defects were confirmed by running the suite and by direct SQL as `pai_app`.

### Patch round — applied and re-verified 2026-11-05

Final independent state on the real container: full suite **81 passed / 208 assertions**, `--testsuite=Isolation` **31 passed / 58 assertions**, Pint clean across 89 files. Re-verified by the orchestrating agent, not taken from the implementation agent's report.

### high — all resolved

- `pai_app` held `UPDATE` on the shared `users` table (no RLS, no tenant key); an arbitrary user's `email` could be rewritten — an account-takeover path. **Resolved** with column-level grants: `users` grants `SELECT, INSERT` plus column-level `UPDATE` on `name`, `password`, `email_verified_at`, `remember_token` and the two-factor columns; `email` is withheld. Re-verified: `has_column_privilege('pai_app','users','email','UPDATE')` = `f` and a live `UPDATE users SET email=…` returns `permission denied for table users`.
- `phpunit.xml` pinned `DB_PORT=5433` while CI publishes Postgres on `5432`. **Resolved** by documenting 5433 as the local default and CI's env as authoritative, with an instruction that the two change together.
- The HTTP vector was not proven: every HTTP test hit `dashboard`, which runs no tenant query. **Resolved** with a test-only probe route read through the tenant layer, then **mutation-tested by the orchestrating agent**: removing `ResolveTenantContext` from the web group makes 3 HTTP tests fail. It cannot pass vacuously.
- Deleting the `/register` test left no guard. **Resolved** with `RegistrationDisabledTest` asserting the feature is disabled and `GET`/`POST /register` return 404 with no user created.

### medium — resolved

- `IsolationTestCase::asSchemaOwner()` was `return $callback();`, contradicting its name. **Resolved**: renamed to `asRuntimeRole()` with an honest docblock (a real connection switch is incompatible with `RefreshDatabase`'s transaction); a genuine owner helper remains for the one case needing it.
- The "app.org_id unset" and "empty" tests were duplicates. **Resolved**: the unset case now uses a fresh connection where the variable was never set, exercising the `missing_ok`→`NULL` branch; the empty case still covers `NULLIF`.
- Multi-membership resolution had no test. **Resolved** with three service-layer cases (ambiguous, forged/stale selection, valid selection).
- No index supported `resolve_user_memberships`. **Resolved** with `memberships_user_id_index` plus a test asserting it exists.
- `pai_app` could update any `organisations` row. **Resolved**: `organisations` grants `SELECT, INSERT` only; re-verified `has_table_privilege(...,'UPDATE')` = `f`.

### medium — carried forward (deferred)

- CI enforced nothing because `phpstan analyse` fails on 11 pre-existing chisel errors from Story 1.1. **Out of this story's intent.** Escalated to new Story 1.12 in `planning-artifacts/sprint-change-proposal-2026-11-05.md`. Correction to the implementation agent's report: PHPStan still reports **11 errors, not 0**.
- `MembershipNotResolvable` is swallowed into a generic 403 with no logging. Deferred to the audit service (Story 1.11).
- `terminate()` is not guaranteed on `abort(403)`/exception. No Octane is deployed; deferred until a long-lived worker exists.
- `migrations` connection falls back to `DB_USERNAME` when `DB_MIGRATION_*` is unset. Deferred to the deployment configuration work.

### low — resolved

- Stale docblock claiming `pai_owner` owns the lookup function. Fixed.
- Role-provisioning log messages naming roles wrongly. Fixed.
- `tools/phpshim/php.bat` is git-ignored, so the `PHPRC` fix is local-only. Documented in the README.

### false

- **`composer.json` is missing a `test` script and `ci:check` would die on `@test`.** Disproved: `composer.json:84` defines `test`, and `ci:check:79` references it.
- **The suite runs on PostgreSQL 18 without PostGIS and that is unverified.** Disproved by independent re-verification on `postgis/postgis:17-3.5` with a freshly initialised volume.
- **`down()` never disables RLS and would strand the bootstrap function.** Disproved: dropping the tables drops their policies; `DROP FUNCTION IF EXISTS` precedes table drops.

### maybe-false

- **`StoreTenantContext` opens a transaction spanning response rendering, exhausting Neon pool slots.** Not measured. What would settle it: a load test against a Neon transaction-pooled branch.

### Behaviour change accepted

- Blocking column-level `users.email` UPDATE means the profile form can no longer change an address; `ProfileUpdateRequest` now rejects a changed email with a validation message rather than a 500. This matches AUTH.md §7 (address changes are administrative). Self-service email changes would need an audited owner-connection path, deferred.

### Resolved during verification

- `tools/create-test-database.php` did not grant `pai_owner` `USAGE, CREATE` on the test database's `public` schema, so every migration failed `42501` and all 72 tests failed. Fixed after reproduction against a real container; re-verified green.

## Design Notes

The middleware order matters: `ResolveTenantContext` must run after `StartSession` and after authentication, so it is appended to the `web` group rather than prepended. The context is bound as an immutable singleton in the container and reset between requests and queued jobs; a long-running worker must never inherit the previous job's tenant.

The RLS helper exists so that the policy text is written exactly once. The fail-closed `NULLIF(..., '')::uuid` form is the invariant; a table added in Epic 2 that raises its own policy by hand is a defect, not a variation.

## Verification

**Commands:**
- `docker compose up -d` -- expected: `pai-postgres` and `pai-storage` report healthy.
- `php artisan migrate:fresh --seed` -- expected: migrations run as `pai_owner`; `organisations` and `memberships` exist with uuid keys.
- `php artisan test` -- expected: green, running against PostgreSQL under `pai_app`.
- `php artisan test --testsuite=Isolation` -- expected: green; includes raw SQL and forged-input negatives.
- `psql -c "\d+"` on each tenant table -- expected: `Row security: enabled, forced`.
- `php artisan types:transform` -- expected: regenerates `types.d.ts` without error.

### Verified on 2026-11-05 — real PostGIS 17 container

The implementation agent's own run used a throwaway PostgreSQL 18 cluster (no Docker, no PostGIS). The orchestrating agent then re-ran everything against the real target: `docker compose down -v && docker compose up -d`, which executed `docker/postgres/init/01-create-roles.sh` on a fresh volume for the first time.

| Check | Result |
| --- | --- |
| `01-create-roles.sh` on a fresh volume | **Pass** — "pai_owner and pai_app roles provisioned." |
| `artisan migrate:fresh --database=migrations` | **Pass** — all 6 migrations as `pai_owner`; every table owned by `pai_owner` |
| Full suite (`vendor/bin/pest`) | **Pass** — 81 tests, 208 assertions |
| `--testsuite=Isolation` | **Pass** — 31 tests, 58 assertions |
| `pg_class` RLS state | `memberships` enabled + forced; `organisations` neither (by design) |
| `pai_app` role | `rolsuper = f`, `rolbypassrls = f`, owns no table |
| `pai_app` privileges | `memberships`: SELECT/INSERT/UPDATE/DELETE; `users`: SELECT/INSERT + column-level UPDATE on name/password/etc. (**`email` withheld**); `organisations`: SELECT/INSERT only |
| Live cross-tenant probe | `SET ROLE pai_app; UPDATE users SET email=…` → **`permission denied for table users`** |
| Live fail-closed probe | 0 rows unbound, 0 rows bound, 0 rows after commit |
| HTTP vector mutation test | Removing `ResolveTenantContext` from the web group → **3 HTTP tests fail** (vector is not vacuous) |
| UUID keys | `users.id`, `sessions.user_id`, `memberships.user_id` all `uuid` |
| `artisan route:list` | no `register` or `passkey` routes |
| Pint | **Pass** — 89 files clean |
| PHPStan (level 7) | **11 pre-existing `InstallFeaturesCommand` errors remain** (chisel is not a dependency; Story 1.1 debris). No new errors from this story. Story 1.12 owns the fix. |
| CI workflow | **Still unexecuted** — it needs a push to GitHub. Wiring was reviewed, not run. |

**Still outstanding:** the CI workflow has never run. Story 1.12 in the Sprint Change Proposal makes it green and asserts the isolation suite is non-empty.
