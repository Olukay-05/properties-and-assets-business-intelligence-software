# Hosting & deployment runbook (D7)

Target: **Hetzner (Germany) VPS** for the app · **Neon EU Central (Frankfurt)** for PostgreSQL/PostGIS · **Cloudflare R2** for files · **GitHub Actions** for CI/CD.

## 1. Database — Neon

1. Create a Neon project in **EU Central (Frankfurt)** (PostgreSQL 17 + PostGIS 3.5).
2. Run the PostGIS spike (`docs/spikes/postgis-neon-verification.md`) and record the result.
3. `dev` and `staging` use Neon **branches**; `production` uses a **separate Neon project**.
4. Provision two separate PostgreSQL roles (`docs/security/RLS-POLICIES.md` §3):
   - **`pai_owner`** (`DB_MIGRATION_USERNAME`): owns schema objects and runs `php artisan migrate` during CI/CD deployment only.
   - **`pai_app`** (`DB_USERNAME`): non-owner runtime role with `NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS` and no `UPDATE`/`DELETE` grants on append-only tables (`locations`, `asset_custody`, `work_order_events`, `audit_events`).
5. Store connection strings as CI secrets / server `.env` (`0600` permissions) — never commit them (`docs/security/ENV.md`).

## 2. Application VPS — Hetzner

1. Create a **Regular Performance** server (Nuremberg/Falkenstein), Ubuntu 24.04 LTS, 2–4 GB.
2. Runtime stack (`ARCHITECTURE-SPINE.md`): Docker container (`Caddy` reverse proxy + TLS + `php-fpm` 8.4 + queue workers + scheduler) or host-managed Nginx/Caddy + PHP-FPM 8.4+, Composer 2, Node.js 22+ (`pnpm`), Supervisor, `git`, and `postgresql-client-17` (for `pg_dump`).
3. Firewall (UFW): allow **22, 80, 443** only.

## 3. Deploy the application

1. Clone the monorepo; `composer install --no-dev --optimize-autoloader`; `npm ci && npm run build`.
2. `.env` from secrets; `php artisan migrate --force`.
3. Nginx vhost → PHP-FPM; SSL via Let's Encrypt (`certbot`).

## 4. Services that must run continuously

| Service | How |
| --- | --- |
| Nginx + PHP-FPM | systemd (web/API traffic) |
| Queue worker | Supervisor — `php artisan queue:work`, 1 worker, `autorestart=true` |
| Scheduler | cron — `php artisan schedule:run` every minute (recurring inspections, due-date checks, cleanup) |

## 5. Object storage — Cloudflare R2

1. Create a **private** bucket for runtime evidence and report exports; configure S3-compatible credentials (`AWS_*`).
2. Object keys namespaced `organisations/{org_id}/clients/{client_id}/{owner_type}/{owner_id}/{uuid}.{ext}` (`AD-13`).
3. All downloads go through **10-minute signed URLs** issued by `EvidenceService` / `ReportService` after tenant and `client_scope` authorisation (`docs/security/SECURITY.md` §4.7).

## 6. Backups

- Neon point-in-time recovery (paid tier).
- Nightly cron: `pg_dump` → **isolated Cloudflare R2 backup bucket using separate credentials (`BACKUP_R2_*`)** not accessible to the web runtime worker (`AD-20`, `docs/security/ENV.md` §3.4).

## 7. CI/CD — GitHub Actions

- On push/PR: install deps, run tests (including the tenant-isolation matrix), build frontend assets.
- On `main`: deploy over SSH — `git pull` → `composer install --no-dev` → `npm run build` → `php artisan migrate --force` → reload PHP-FPM.

## 8. Monitoring

- **Sentry** (free tier) for errors; **UptimeRobot** (free) for health checks; log rotation via `logrotate`.

## 9. Estimated cost

- Dev/staging: ≈ **$8–15/mo** (Neon Free + Hetzner starter + R2 free tier).
- Production: ≈ **$35–55/mo** (Neon paid + Hetzner 2–4 GB + R2 free tier).

Confirm final quotes at sign-up (Hetzner prices exclude VAT). NIPOST fees are unknown and excluded by design.

## 10. Portability (future Nigerian hosting)

To move later: repoint `DB_*` at a Nigerian PostgreSQL and redeploy the same image; migrate R2 objects with any S3 tool. No code change.
