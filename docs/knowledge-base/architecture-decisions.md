# Architecture decisions (ADRs)

Full rationale is in `docs/development-plan-review.md` (Revision 2). This is the concise record.

| ID | Decision |
| --- | --- |
| D1 | **Neon + PostGIS** — week-1 spike verifies `CREATE EXTENSION postgis` in a dedicated schema before the schema is locked. |
| D2 | **Coordinate storage** — `geometry(Point, 4326)` canonical; lat/lng derived (`ST_Y`/`ST_X`), no independent columns. |
| D3 | **Auth** — Sanctum for first-party now; Passport / OAuth 2.1 for external integrators/agents deferred to Phase 3. |
| D4 | **Design system is the visual source of truth** — map its tokens into the Tailwind theme and re-skin shadcn/ui to match. |
| D5 | **Map provider** — MapLibre GL JS + OpenFreeMap (verify licensing); MapTiler not mandatory. |
| D6 | **Queue driver** — database driver for MVP; Redis only if needed later. |
| D7 | **Hosting/region** — Hetzner (Germany) for the app + Neon EU Central (Frankfurt) for the database. |
| D8 | **File storage** — Cloudflare R2, private bucket + short-lived signed URLs. |
| D9 | **Repository** — one monorepo: main app + Docusaurus docs. |
| D10 | **Tenant model** — single schema + `organisation_id` + RLS (no schema-per-tenant, no database-per-tenant). |

## Invariants

1. **Shared service layer** — controllers, REST API and (future) MCP tools all call the same services; business rules exist once.
2. **Modular monolith** — no microservices.
3. **Portability** — standard PostgreSQL, portable object storage, provider-agnostic auth, independent backups.

## Stack (summary)

Laravel + React/TypeScript + Inertia + Tailwind/shadcn (themed to the design system) · PostgreSQL + PostGIS (Neon) · Cloudflare R2 · Laravel Queues · REST API (Sanctum) · Docusaurus docs.
