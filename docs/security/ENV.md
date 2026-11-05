# Environment Variables and Secrets Management (`ENV.md`)

**Status:** Active Security Foundation  
**Last Updated:** 2026-11-05  
**Scope:** Cloud Crafters Property & Asset Intelligence (Local, Staging, and Production Environments)  
**Primary References:** `.env.example`, `docker-compose.yml`, `config/*.php`, `ARCHITECTURE-SPINE.md` (`AD-4`, `AD-13`, `AD-20`), `docs/operations/hosting-and-deployment.md`

---

## 1. Purpose & Implementation Status

This document catalogues all environment variables required by the platform across Local Development, Staging, and Production, and defines mandatory procedures for handling secrets.

**Security Rule:** Never place real credentials, secrets, API tokens, or production passwords in this document, in `.env.example`, in source code, or in Git history. The `.env.example` file must contain safe local placeholders only.

### Implementation Status Summary
- **Implemented & Verified in `.env.example` / `config/*.php`:** Core Laravel app, PostgreSQL (`DB_*`), database session/cache/queue, mail, S3-compatible storage (`AWS_*` pointing to local RustFS in `docker-compose.yml`), and `EVIDENCE_SIGNED_URL_TTL_MINUTES=10`.
- **Implemented (Story 1.2):** separate schema-owner credentials (`DB_MIGRATION_USERNAME` / `DB_MIGRATION_PASSWORD`) consumed by the dedicated `migrations` connection, so the runtime role `pai_app` never executes DDL; optional explicit runtime-role overrides (`DB_APP_USERNAME` / `DB_APP_PASSWORD`); `DB_SSLMODE`; and the local-only container superuser (`POSTGRES_SUPERUSER` / `POSTGRES_SUPERUSER_PASSWORD`). The three roles (`pai_owner`, `pai_app`, `pai_bootstrap`) are created by `docker/postgres/init/01-create-roles.sh` on first boot, and in the test database by `php tools/create-test-database.php`.
- **Verified (Story 1.2):** the full test suite runs against PostgreSQL as `pai_app` with no BYPASSRLS and no table ownership (`tests/Isolation/TenantIsolationTest.php`).
- **Outstanding (Proposed for Upcoming Stories):**
  - Isolated backup bucket variables (`BACKUP_R2_*`) for nightly `pg_dump` exports (`Story 6.3`, `AD-20`).
  - Sentry error tracking (`SENTRY_LARAVEL_DSN`, `VITE_SENTRY_DSN` — `Story 6.4`).
  - Map tile style URL override (`VITE_MAP_STYLE_URL` — `Story 2.4`, `AD-20`).
  - Feature-flagged NIPOST postcode lookup credentials (`NIPOST_ENABLED`, `NIPOST_API_KEY`, `NIPOST_BASE_URL` — `Story 2.3`, `FR-2`).

---

## 2. Environment Overview

| Environment | Application Compute | Database (PostgreSQL 17 + PostGIS) | Object Storage (`s3` disk) | Secret Storage Location |
| --- | --- | --- | --- | --- |
| **Local (`local`)** | Native PHP 8.4+ (`php artisan serve` + `pnpm dev`) | Local Docker container (`postgis/postgis:17-3.5` on host port `5433`) | Local Docker container (`rustfs/rustfs:1.0.4` on host port `9000`) | Developer's untracked local `.env` file |
| **Testing (`testing` / CI)** | GitHub Actions runner / Local Pest runner | Dedicated database `property_asset_intelligence_test` on PostgreSQL 17 + PostGIS, owned by `pai_owner`, queried as `pai_app` (implemented in Story 1.2; `phpunit.xml` no longer uses SQLite) | Local RustFS container (for `StorageContractTest` & `EvidenceService` tests) | GitHub Actions Secrets / CI workflow env |
| **Staging (`staging`)** | Staging Hetzner VPS (Germany) | Neon EU Central (Frankfurt) — isolated staging branch | Cloudflare R2 — private staging bucket | Server `/etc/...` or deployment `.env` (`0600` permissions) + GitHub Actions Environment Secrets |
| **Production (`production`)** | Production Hetzner VPS (Germany) | Neon EU Central (Frankfurt) — separate paid project with PITR | Cloudflare R2 — private production bucket + separate isolated R2 backup bucket | Production VPS protected `.env` (`0600` permissions, owned by deploy/runtime user) + GitHub Actions Production Secrets |

---

## 3. Complete Environment Variable Inventory

> **Frontend Exposure Warning:** Any variable prefixed with `VITE_` is compiled directly into public JavaScript bundles served to every browser. **Never** prefix a secret, API key, database credential, or private storage key with `VITE_`.

### 3.1 Core Application & Encryption (`config/app.php`)

| Variable Name | Status | Purpose | Req / Opt | Environments | Sensitive? | Where Value Should Be Stored | Safe for Frontend? |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `APP_NAME` | Implemented | Human-readable application name (`"Property & Asset Intelligence"`). | Required | All | No | `.env` / CI env | Yes (via `VITE_APP_NAME`) |
| `APP_ENV` | Implemented | Runtime environment (`local`, `testing`, `staging`, `production`). Controls security defaults such as `DB::prohibitDestructiveCommands()` and strict password rules. | Required | All | No | `.env` / CI env | No |
| `APP_KEY` | Implemented | 32-byte `base64:` AES-256-CBC master encryption key for cookies, sessions, and signed URLs. | **Required** | All | **YES (Critical)** | Local `.env` (generated via `php artisan key:generate`); GitHub Secrets / VPS `.env` in staging & prod. | **NEVER** |
| `APP_PREVIOUS_KEYS` | Implemented | Comma-separated list of previous `APP_KEY` values during zero-downtime key rotation (`config/app.php`). | Optional | Staging, Prod | **YES (Critical)** | VPS `.env` / Secrets Manager | **NEVER** |
| `APP_DEBUG` | Implemented | Enables detailed debug stack traces. **Must be `false` in `staging` and `production`.** | Required | All | No (Config-critical) | `.env` (`true` local, **`false` staging/prod**) | No |
| `APP_URL` | Implemented | Canonical base URL of the application (`http://localhost:8000` locally; `https://...` in staging/prod). Used by Fortify passkeys, mail links, and asset URLs. | Required | All | No | `.env` | Yes |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` / `APP_FAKER_LOCALE` | Implemented | Locale settings (`en`, `en`, `en_NG`). | Optional | All | No | `.env` | No |
| `VITE_APP_NAME` | Implemented | Exposes `APP_NAME` to the Vite/React frontend bundle. | Optional | All | No | `.env` | **Yes** |

### 3.2 Database & PostgreSQL RLS Configuration (`config/database.php`)

| Variable Name | Status | Purpose | Req / Opt | Environments | Sensitive? | Where Value Should Be Stored | Safe for Frontend? |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `DB_CONNECTION` | Implemented | Default database driver. Must be `pgsql` across all environments (`AD-19`). | Required | All | No | `.env` | No |
| `DB_HOST` | Implemented | PostgreSQL hostname (`127.0.0.1` locally; Neon pooled endpoint `*-pooler.eu-central-1.aws.neon.tech` in staging/prod). | Required | All | No | `.env` / GitHub Secrets | No |
| `DB_PORT` | Implemented | PostgreSQL port (`5433` locally for `docker-compose.yml`; `5432` on Neon). | Required | All | No | `.env` | No |
| `DB_DATABASE` | Implemented | Database name (`property_asset_intelligence`). | Required | All | No | `.env` | No |
| `DB_USERNAME` | **Implemented & Verified (Story 1.2)** | **Application runtime database user (`pai_app`).** Non-owner role with `NOBYPASSRLS` (AD-4) and no `UPDATE`/`DELETE` on append-only tables (AD-8). Verified by the isolation suite. | Required | All | No (Identifier) | `.env` / GitHub Secrets | **NEVER** |
| `DB_PASSWORD` | **Implemented (Story 1.2)** | Password for the runtime database user (`pai_app`). | **Required** | All | **YES (Critical)** | Local `.env`; VPS `.env` / GitHub Secrets | **NEVER** |
| `DB_APP_USERNAME` / `DB_APP_PASSWORD` | **Implemented (Story 1.2)** | Optional explicit runtime-role credentials for the `app` connection. Default to `DB_USERNAME` / `DB_PASSWORD`. Migrations read the role name from here when building `GRANT`s. | Optional | All | `DB_APP_PASSWORD`: **YES** | `.env` / GitHub Secrets | **NEVER** |
| `DB_SSLMODE` | Implemented (`config/database.php`) | PostgreSQL SSL mode (`prefer` locally; **must be `require` or `verify-full` in staging and production** for Neon connections). | Required | Staging, Prod | No | `.env` | No |
| `DB_MIGRATION_USERNAME` | **Implemented (Story 1.2)** | Administrative schema-owner database user (`pai_owner`) used **only** by the `migrations` connection (`php artisan migrate`) and RLS policy DDL (AD-4). The migrator is pinned to this connection by `TenancyServiceProvider`, so `php artisan migrate` never runs as the runtime role. | Required | All | No (Identifier) | Local `.env`; CI/CD deploy secrets | **NEVER** |
| `DB_MIGRATION_PASSWORD` | **Implemented (Story 1.2)** | Password for the schema-owner database user (`pai_owner`). | **Required** | All | **YES (Critical)** | Local `.env`; CI/CD deploy secrets (avoid storing on the web worker when migrations run from CI/deploy) | **NEVER** |
| `POSTGRES_SUPERUSER` / `POSTGRES_SUPERUSER_PASSWORD` | **Implemented (Story 1.2, local only)** | Container-superuser credentials for the local `pai-postgres` service. Provisioning only: neither the application nor migrations use them. Never set outside local development. | Optional (local) | Local | `POSTGRES_SUPERUSER_PASSWORD`: **YES** | Untracked local `.env` | **NEVER** |

### 3.3 Authentication & Session Security (`config/session.php`, `config/auth.php`, `config/fortify.php`)

| Variable Name | Status | Purpose | Req / Opt | Environments | Sensitive? | Where Value Should Be Stored | Safe for Frontend? |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `BCRYPT_ROUNDS` | Implemented | Work factor for bcrypt password hashing (`12` in `.env.example`). | Required | All | No | `.env` | No |
| `SESSION_DRIVER` | Implemented | Server-side session storage backend (`database`). | Required | All | No | `.env` | No |
| `SESSION_LIFETIME` | Implemented | Idle session expiration in minutes (`120`). | Required | All | No | `.env` | No |
| `SESSION_ENCRYPT` | Implemented | Whether session payload in `sessions` table is AES-encrypted. (`false` locally; **must be `true` in staging and production**). | Required | Staging, Prod | No (Security-critical) | `.env` | No |
| `SESSION_SECURE_COOKIE` | Implemented (`config/session.php`) | Restricts session cookie transmission to HTTPS only. **Must be `true` in staging and production.** | Required | Staging, Prod | No (Security-critical) | `.env` | No |
| `SESSION_HTTP_ONLY` | Implemented (`config/session.php`) | Prevents client-side JS from reading the session cookie (`true`). | Optional (defaults `true`) | All | No | `.env` | No |
| `SESSION_SAME_SITE` | Implemented (`config/session.php`) | CSRF cookie mitigation (`lax`). | Optional (defaults `lax`) | All | No | `.env` | No |
| `SESSION_DOMAIN` | Implemented | Cookie domain scope (`null` binds strictly to host). | Optional | All | No | `.env` | No |
| `SANCTUM_STATEFUL_DOMAINS` | **Proposed (Story 1.3)** | Allowed first-party origins for Sanctum stateful cookie authentication. | Required (from Story 1.3) | All | No | `.env` | No |

### 3.4 Object Storage: RustFS (Local) & Cloudflare R2 (Staging/Production) (`config/filesystems.php`)

The application uses Laravel's standard `s3` disk exclusively (`AD-20`). No provider-specific SDKs or code exist.

| Variable Name | Status | Purpose | Req / Opt | Environments | Sensitive? | Where Value Should Be Stored | Safe for Frontend? |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `FILESYSTEM_DISK` | Implemented | Default filesystem disk (`s3`). | Required | All | No | `.env` | No |
| `AWS_ACCESS_KEY_ID` | Implemented | S3 API access key ID (`pai-local` for local RustFS; R2 API token Access Key ID in staging/prod). | **Required** | All | **YES** | Local `.env`; VPS `.env` / GitHub Secrets | **NEVER** |
| `AWS_SECRET_ACCESS_KEY` | Implemented | S3 API secret access key (`pai-local-secret` for local RustFS; R2 API token Secret Key in staging/prod). | **Required** | All | **YES (Critical)** | Local `.env`; VPS `.env` / GitHub Secrets | **NEVER** |
| `AWS_DEFAULT_REGION` | Implemented | S3 region (`us-east-1` for local RustFS; `auto` for Cloudflare R2). | Required | All | No | `.env` | No |
| `AWS_BUCKET` | Implemented | Private bucket name (`property-asset-intelligence` locally; staging/prod private R2 bucket name). | Required | All | No | `.env` | No |
| `AWS_ENDPOINT` | Implemented | S3 API endpoint (`http://127.0.0.1:9000` for local RustFS; `https://<account_id>.r2.cloudflarestorage.com` for Cloudflare R2). | Required | All | No | `.env` | No |
| `AWS_USE_PATH_STYLE_ENDPOINT` | Implemented | `true` for local RustFS; `false` (or `true`) for Cloudflare R2 S3 API. | Required | All | No | `.env` | No |
| `AWS_URL` | Implemented | Public base URL for S3 objects. **Must remain empty (`""`) in all environments** because evidence buckets are strictly private and served only via pre-signed URLs (`AD-13`). | Optional (Keep empty) | All | No | `.env` (leave blank) | No |
| `EVIDENCE_SIGNED_URL_TTL_MINUTES` | Implemented | Lifetime in minutes for pre-signed download URLs (`10` per `AD-13`). | Required | All | No | `.env` | No |
| `BACKUP_R2_ACCESS_KEY_ID` / `BACKUP_R2_SECRET_ACCESS_KEY` / `BACKUP_R2_BUCKET` / `BACKUP_R2_ENDPOINT` | **Proposed (Story 6.3)** | Separate, isolated credentials and bucket for nightly portable `pg_dump` backups (`AD-20` Structural Seed: *"R2 backup bucket, separate creds"*). | Required before Pilot (`Story 6.3`) | Prod (and Staging drill) | **YES (Critical)** | Backup cron / VPS root-only env file (`0600`), **not** readable by `php-fpm` web workers | **NEVER** |

### 3.5 Queues, Cache, Logging, Mail, and External Services

| Variable Name | Status | Purpose | Req / Opt | Environments | Sensitive? | Where Value Should Be Stored | Safe for Frontend? |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `QUEUE_CONNECTION` | Implemented | Queue backend (`database` per `D6`). | Required | All | No | `.env` | No |
| `CACHE_STORE` | Implemented | Cache backend (`database` per `D6`). | Required | All | No | `.env` | No |
| `LOG_CHANNEL` / `LOG_STACK` / `LOG_LEVEL` | Implemented | Logging driver (`stack` / `single` locally; structured JSON daily/stderr in prod with `LOG_LEVEL=info` or `warning`). | Required | All | No | `.env` | No |
| `MAIL_MAILER` / `MAIL_HOST` / `MAIL_PORT` / `MAIL_USERNAME` / `MAIL_PASSWORD` / `MAIL_FROM_ADDRESS` | Implemented | Outbound transactional email (`log` locally; Resend/SMTP in staging/prod for password resets and notifications per `docs/development-plan-review.md` §8). | Required | All | **`MAIL_PASSWORD` / API Key: YES** | Local `.env`; VPS `.env` / GitHub Secrets | **NEVER** |
| `SENTRY_LARAVEL_DSN` | **Proposed (Story 6.4)** | Backend error reporting DSN for Sentry (`NFR-5.5`). | Required before Pilot | Staging, Prod | Yes (Ingest token) | VPS `.env` / GitHub Secrets | No |
| `VITE_SENTRY_DSN` | **Proposed (Story 6.4)** | Frontend browser error reporting public DSN for Sentry (`NFR-5.5`). | Required before Pilot | Staging, Prod | No (Public ingest DSN) | `.env` / Build env | **Yes** |
| `VITE_MAP_STYLE_URL` | **Proposed (Story 2.4)** | Vector tile style endpoint for `<PropertyMap>` (defaults to OpenFreeMap; swappable without code changes per `AD-20` & `F-309`). | Optional | All | No (unless a paid MapTiler key is used later) | `.env` | **Yes** |
| `NIPOST_ENABLED` | **Proposed (Story 2.3)** | Feature flag (`false` by default) controlling optional server-side NIPOST postcode verification (`FR-2`, `F-105`). | Optional | All | No | `.env` | No |
| `NIPOST_API_KEY` / `NIPOST_BASE_URL` | **Proposed (Story 2.3)** | Server-side API credentials for NIPOST lookup when `NIPOST_ENABLED=true`. Never called from the browser (`docs/updates.md` §11). | Optional (only if `NIPOST_ENABLED=true`) | Staging, Prod | **YES (`NIPOST_API_KEY`)** | VPS `.env` / GitHub Secrets | **NEVER** |
| `NDPA_RETENTION_DAYS` | **Proposed (Story 5.5)** | Single documented retention duration setting for administrative pruning job (`Story 5.5` / `OQ-7`). | Required before Pilot | Staging, Prod | No | `.env` | No |

---

## 4. Secret Rotation, Access Restrictions, and Safe Handling Procedures

### 4.1 File & Repository Hygiene
1. **Never commit `.env` files:** `.gitignore` blocks `.env`, `.env.backup`, `.env.production`, `/storage/*.key`, and `/auth.json`. Verify with `git status` before every commit.
2. **Placeholder-only `.env.example`:** Whenever a new configuration variable is introduced in `config/*.php`, add a documented placeholder entry to `.env.example` in the same pull request with an empty or clearly non-secret local value.
3. **Server File Permissions:** On staging and production Hetzner VPS instances, `.env` must have `0600` (`rw-------`) permissions owned by the deployment/runtime service account, and backup credentials (`BACKUP_R2_*`) must be isolated so web processes cannot modify or delete backups (`AD-20`).

### 4.2 Principle of Least Privilege for Credentials
1. **Database Credentials:** Separate `DB_MIGRATION_USERNAME` (`pai_owner`, used only during `php artisan migrate`) from `DB_USERNAME` (`pai_app`, used by the running app with `NOBYPASSRLS`).
2. **Cloudflare R2 Credentials:**
   - The application runtime token (`AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY`) must be scoped **only** to the single environment's evidence/exports bucket (`Object Read & Write`).
   - The nightly backup job token (`BACKUP_R2_*`) must be a **separate** R2 API token scoped **only** to the isolated backup bucket (`AD-20`), so a compromise of the web container cannot delete or overwrite historical database backups.

### 4.3 Secret Rotation Procedures
- **`APP_KEY` Rotation:** Generate a new key, move the old key into `APP_PREVIOUS_KEYS` (`config/app.php` natively decrypts using `previous_keys` and re-encrypts with the new `key`), deploy, and remove `APP_PREVIOUS_KEYS` after all active 120-minute sessions and queued jobs have cycled.
- **Database Password Rotation (`Neon`):** Create or update the password for `pai_app` in Neon, update the secret in the VPS `.env` and GitHub Actions Secrets, and reload `php-fpm` and `queue:work`.
- **Cloudflare R2 Token Rotation:** Roll or create a new scoped R2 API token in Cloudflare, update `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY`, reload workers (note: existing pre-signed URLs have a maximum 10-minute TTL and will naturally expire within 10 minutes), and revoke the old token.
- **Emergency Leak Response:** If any credential or private key is ever committed to Git or exposed in logs, treat it as **compromised immediately**: revoke/rotate the secret at the provider first, then purge it from Git history and record an incident audit entry (`Story 6.2`).

