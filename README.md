# Property & Asset Intelligence

A multi-tenant platform for Nigerian facilities-management providers to register
client properties and assets, run inspections and maintenance, capture evidence,
and produce client reports.

Laravel 13 + Inertia 3 + React 19 + TypeScript 5 + Tailwind 4.

## Prerequisites

- PHP 8.4 or newer with `pdo_pgsql`, `pdo_sqlite`, `zip`, `fileinfo`, `intl` extensions
- Composer 2
- Node.js 20.19+ and pnpm 10
- Docker Desktop (for PostgreSQL/PostGIS and the local object store)

Some Windows PHP installs ship with the required extensions present but
commented out in `php.ini`. `tools/php-local.ini` enables them without touching
the system install. To have every child process (Composer scripts, Artisan's
test runner, Vite's Wayfinder plugin) pick it up, put the shim on your PATH for
the session:

```powershell
$env:Path = "$PWD\tools\phpshim;$env:Path"
```

If `php artisan test` fails with "could not find driver", that PATH entry is
missing — the test runner spawns a second `php` binary for each test process.

## Local setup

```bash
# 1. Dependencies
composer install
pnpm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Infrastructure (PostgreSQL 17 + PostGIS, and the S3-compatible store)
docker compose up -d
php artisan app:storage:init     # creates the bucket; safe to re-run
php artisan migrate

# 4. Run
pnpm build                       # or: pnpm dev  (Vite dev server)
php artisan serve                # http://localhost:8000
```

`docker compose up -d` starts two services and waits until both are healthy:

| Service | Image | Purpose | Host ports |
|---|---|---|---|
| `postgres` | `postgis/postgis:17-3.5` | Application database with PostGIS | 5433 |
| `storage` | `rustfs/rustfs:1.0.4` | S3-compatible object store | 9000 (API), 9001 (console) |

Images are pinned by digest in `docker-compose.yml` so the stack never changes
silently. To upgrade a service, change the tag and digest together (instructions
are in the file header).

### Object storage

The application uses Laravel's standard `s3` disk. Which provider sits behind it
is decided entirely by environment variables (`AWS_ENDPOINT`, `AWS_BUCKET`,
`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`):

- **Local development:** RustFS, started by docker-compose.
- **Production:** a private Cloudflare R2 bucket.

No application or business code knows which provider is in use, so switching is
a configuration change. Buckets are always private: objects are served only
through short-lived pre-signed URLs (10 minutes by default).

To point at a different S3-compatible service, change the `AWS_*` values and run
`php artisan app:storage:init` to create the bucket.

### Typical commands

```bash
php artisan test                 # full Pest suite
php artisan test --filter=Storage   # object-storage contract tests
php artisan types:transform      # regenerate TypeScript types from PHP Data classes
pnpm run build                   # production asset build
composer lint                    # Pint
pnpm run types:check             # TypeScript type check
docker compose down              # stop infrastructure (keeps data volumes)
```

## Architecture notes

- **Typed contract.** Spatie `Data` objects are the single contract between PHP
  and the UI. TypeScript definitions are generated into
  `resources/js/types/generated/` by `php artisan types:transform`; hand-written
  duplicate interfaces are not allowed.
- **Module layout.** Feature code lives under `app/Domain/<Module>/`
  (`Services`, `Models`, `Data`, `Policies`, `Enums`, `Exceptions`) and
  infrastructure concerns under `app/Infrastructure/`. Controllers are thin
  adapters over the service layer; business rules live in services only.
- **Tenant isolation.** Tenant rows carry `organisation_id`, resolved from the
  authenticated session and enforced by PostgreSQL row-level security. This is
  introduced in Story 1.2; the directory skeleton for it already exists.

See `docs/knowledge-base/` for scope, data model and architecture decisions, and
`AGENTS.md` for the working rules.

## Storage contract tests

`tests/Feature/StorageContractTest.php` exercises the real S3-compatible
endpoint rather than mocking it. It covers upload, retrieval, existence,
deletion, pre-signed URL access, expiry of a pre-signed URL, and denial of
anonymous access to a private object. The tests skip automatically when no
S3-compatible endpoint is configured, so `docker compose up -d` is required to
run them locally.
