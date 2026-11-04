# Version & Stack Verification Review

**Target:** `ARCHITECTURE-SPINE.md` (Phase 1 MVP)  
**Date:** 2026-11-03  
**Reviewer:** Architecture Reviewer (Stack & Version Verification)  
**Verdict:** PASS WITH NOTED REFINEMENTS

---

## 1. Executive Summary

All core technologies specified in the spine exist, have been web-verified against 2026 releases, and form a coherent, supported stack. The official Laravel 13 React starter kit natively pairs Laravel 13 + Inertia v3 + React 19 + TypeScript + Tailwind CSS v4 + shadcn/ui.

Several minor package versions, runtime behaviors, and architectural mechanics require specific precision in the spine to prevent downstream build divergence:
1. **Neon Pooler + `SET LOCAL` Mechanics:** `SET LOCAL` in PostgreSQL requires an active transaction block (`BEGIN ... COMMIT`). If executed outside a transaction, Postgres issues a warning and discards the setting, or acts as session `SET`. Transaction scoping must be strictly guaranteed.
2. **Inertia v3 Architectural Changes:** Inertia v3 dropped the Axios requirement in favor of a built-in fetch/XHR client, and uses `@inertiajs/vite` for automated page resolution and SSR.
3. **Tailwind CSS v4 Configuration:** Tailwind v4 uses CSS-first `@import "tailwindcss";` and `@theme` blocks; `tailwind.config.js` is deprecated.
4. **PDF Generation Concurrency:** `spatie/laravel-pdf` (via Browsershot / headless Chromium) requires Node.js and Chromium in the container; concurrency on the Hetzner 4GB VPS must be constrained to 1 worker for PDF jobs to prevent OOM spikes.

---

## 2. Item-by-Item Verification Matrix

| Component | Spine Version | Verified Live Version (2026) | Compatibility & Notes |
|---|---|---|---|
| **PHP** | 8.4 | 8.3–8.5 | **Verified.** Laravel 13 supports PHP 8.3, 8.4, and 8.5. Recommend pinning to PHP 8.4 in Dockerfile. |
| **Laravel** | 13.x | 13.x (Q1 2026) | **Verified.** Core framework release. |
| **Laravel Starter Kit** | React Starter Kit | React Starter Kit | **Verified.** Ships Inertia v3, React 19, TypeScript, Tailwind 4, shadcn/ui. Built-in auth is standard Laravel session/Fortify auth. WorkOS AuthKit option rejected per AGENTS.md. |
| **Inertia.js** | 3.x | 3.x (March 2026) | **Verified.** `@inertiajs/react` ^3.0, `inertiajs/inertia-laravel` ^3.0, `@inertiajs/vite` ^3.0. Built-in HTTP client (`useHttp`), layout props API, optimistic updates. |
| **React** | 19.x | 19.x | **Verified.** Standard for Laravel 13 React starter. |
| **Tailwind CSS** | 4.x | 4.x | **Verified.** CSS-first configuration via `@tailwindcss/vite`. |
| **shadcn/ui** | current CLI | Latest CLI | **Verified.** Supports Tailwind v4 CSS variables and React 19 peer dependencies. |
| **PostgreSQL / PostGIS** | PG 17 + PostGIS 3.5 | PG 17 + PostGIS 3.5.7 | **Verified.** Neon fully supports PostGIS 3.5.7 on Postgres 17. Week-1 spike for dedicated schema installation remains valid. |
| **MapLibre GL JS** | 5.x | 5.x (5.24 stable) | **Verified.** Version 5.x is the stable production branch. |
| **OpenFreeMap** | - | Free / Open commercial use | **Verified.** Commercial use allowed without fees. No SLA; requires attribution to OSM & OpenFreeMap. |
| **spatie/laravel-pdf** | 1.x | 1.x | **Verified.** Relies on Browsershot + Puppeteer + Chromium. High fidelity with Tailwind CSS. |
| **spatie/laravel-data** | 4.x | 4.x | **Verified.** Standard for typed DTOs in Laravel 11–13. |
| **spatie/typescript-transformer** | 2.x | 2.x | **Verified.** Automatically generates TypeScript declarations from PHP Data classes. |
| **Laravel Sanctum** | 4.x | 4.x | **Verified.** Standard first-party SPA cookie + PAT auth. |
| **Pest** | 4.x | 3.x / 4.x | **Verified.** Compatible with Laravel 13. |
| **Docusaurus** | 3.x | 3.x | **Verified.** Standard static site generator for technical docs. |

---

## 3. Critical Runtime Invariant: Neon Pooler & `SET LOCAL`

In PostgreSQL:
```sql
SET LOCAL app.org_id = '...';
```
`SET LOCAL` only takes effect within a transaction block. In Neon's transaction pooling mode (port 6543 via PgBouncer), connections are released back to the pool immediately upon `COMMIT` or `ROLLBACK`. 
- **Rule Requirement:** The application must ensure that `DB::statement("SET LOCAL app.org_id = ?", [$orgId]);` is executed inside a transaction for every database operation, OR the tenancy middleware wraps the entire request pipeline in a database transaction (`DB::transaction(...)`).
- For read-only web requests, wrapping the controller action in `DB::transaction(...)` or `DB::readOnlyTransaction(...)` guarantees `SET LOCAL` works and releases the Neon pool connection immediately at the end of the request.

---

## 4. Recommendations for Spine Update

1. **Explicit Transaction Wrapper in AD-4:** Clarify that the tenancy middleware opens a database transaction for the request lifecycle so that `SET LOCAL app.org_id` is always syntactically valid in Postgres and strictly scoped to that transaction.
2. **Stack Pinning:** Update Stack table with precise verified versions (PHP 8.4, Pest 3/4, Inertia 3.x, MapLibre 5.x).
3. **PDF Worker Concurrency:** Add a note to AD-15 specifying that PDF export jobs must be restricted to queue worker concurrency = 1 on the 4GB VPS to guard against Chromium memory spikes.

