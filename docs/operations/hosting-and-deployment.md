# Hosting & deployment runbook (D7)

Target: **Hetzner (Germany) VPS** for the app · **Neon EU Central (Frankfurt)** for PostgreSQL/PostGIS · **Cloudflare R2** for files · **GitHub Actions** for CI/CD.

## 1. Database — Neon

1. Create a Neon project in **EU Central (Frankfurt)**.
2. Run the PostGIS spike (`docs/spikes/postgis-neon-verification.md`) and record the result.
3. `dev` and `staging` use Neon **branches**; `production` uses a **separate Neon project**.
4. Store the connection string as a CI secret / server `.env` — never commit it.

## 2. Application VPS — Hetzner

1. Create a **Regular Performance** server (Nuremberg/Falkenstein), Ubuntu 24.04 LTS, 2–4 GB.
2. Install: Nginx, PHP-FPM 8.3+, Composer, Node.js 20+, Supervisor, `git`, and the PostgreSQL client (`postgresql-client` for `pg_dump`).
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

1. Create a **private** bucket; configure S3-compatible credentials.
2. Object keys namespaced `organisations/{org_id}/...`.
3. All downloads go through **short-lived signed URLs** issued by the service layer after authorisation.

## 6. Backups

- Neon point-in-time recovery (paid tier).
- Nightly cron: `pg_dump` → R2 (independent, portable copy).

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
