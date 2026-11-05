# Epic 1 Context: Platform Foundations & Design System

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Establish the technical and visual foundation every later epic builds on: a scaffolded Laravel + Inertia + React application in which a team member can sign in with email/password, be placed inside their organisation's isolated workspace with role-scoped abilities, and see a consistent application shell rendered in the Cloud Crafters Editorial Intelligence design system on desktop and mobile. This epic also delivers the cross-cutting services all workflows depend on — tenant context with database-enforced isolation, the private evidence store with signed URLs, the append-only audit trail, and the structured error envelope — so that no workflow story ever has to invent security, evidence, or error handling ad hoc.

## Stories

- Story 1.1: Greenfield scaffold — Laravel React starter kit with typed contract tooling (`docs/security/ENV.md`, `docs/security/SECURITY.md`)
- Story 1.2: Tenant context and RLS isolation infrastructure (`docs/security/RLS-POLICIES.md`, `docs/security/THREAT-MODEL.md` T-01/T-04)
- Story 1.3: Authentication with Sanctum and membership resolution (`docs/security/AUTH.md`, `docs/security/THREAT-MODEL.md` T-05)
- Story 1.4: Role-ability policy foundation (`docs/security/AUTH.md` §9, `docs/security/RLS-POLICIES.md` §3, `docs/security/THREAT-MODEL.md` T-03)
- Story 1.5: Design tokens in Tailwind @theme and base typography
- Story 1.6: Core component library I — layout and content primitives
- Story 1.7: Core component library II — interactive and field primitives
- Story 1.8: Application shell — desktop header, mobile navigation, quick-task bar
- Story 1.9: Operational states and editorial copy system (`docs/security/DATA-PROTECTION.md` §4.4 NIPOST disclaimer)
- Story 1.10: Evidence service and signed-URL gateway (`docs/security/SECURITY.md` §4.7, `docs/security/RLS-POLICIES.md` §6.4, `docs/security/THREAT-MODEL.md` T-02/T-06)
- Story 1.11: Audit service and structured error envelope (`docs/security/SECURITY.md` §4.9, `docs/security/DATA-PROTECTION.md` §4.5/§7.1, `docs/security/THREAT-MODEL.md` T-07)

## Security Foundation References (`docs/security/`)

Before implementing any story in Epic 1, the developer/agent must consult:
- `docs/security/AUTH.md` — Authentication vs. authorisation, disabling open public self-registration (`Features::registration()`) in Story 1.3, migrating `users.id` / `sessions.user_id` to UUID (`AD-6`) in Story 1.2, 6-role union permissions, and `ClientScope` (`404` out-of-scope vs `403` unauthorised action).
- `docs/security/RLS-POLICIES.md` — PostgreSQL `FORCE ROW LEVEL SECURITY` on all 17 tenant tables (`NULLIF(current_setting('app.org_id', true), '')::uuid`), separating schema-owner role (`pai_owner`) from runtime non-owner role (`pai_app` with `NOBYPASSRLS` and no `UPDATE`/`DELETE` on `locations`, `asset_custody`, `work_order_events`, `audit_events`), transaction-local `set_config('app.org_id', ?, true)` for Neon PgBouncer compatibility, and updating `phpunit.xml` / CI from SQLite to PostgreSQL 17 + PostGIS.
- `docs/security/SECURITY.md` — Mandatory rules for AI coding agents, thin adapters (`AD-1`), 4-tier `RateLimiter` (`AD-22`), `finfo` magic-byte upload checks (`10 MB` cap, allowlist `jpeg`/`png`/`webp`/`pdf`), and exception sanitization.
- `docs/security/ENV.md`, `docs/security/THREAT-MODEL.md`, and `docs/security/DATA-PROTECTION.md` — Environment variable rules, threat verification tests (`T-01`–`T-11`), and NDPA 2023 data minimisation/audit requirements.

## Requirements & Constraints

**Tenancy & isolation (hard requirement, enforced everywhere from this epic on):**
- Tenant is resolved only from the authenticated user's membership — never from request input. A forged `organisation_id` in any payload must be ignored, and a creating-hook stamps the real value and aborts on conflict.
- Every tenant table enforces row-level security (`ENABLE` + `FORCE RLS`, policy on `current_setting('app.org_id')::uuid`); the runtime DB role is a non-owner without `BYPASSRLS`. A raw SQL cross-tenant query must return zero rows. Isolation must hold at HTTP, service, and raw SQL layers.
- Client scoping: memberships carry a `client_scope` (null = unrestricted, else a set of client account IDs); access outside scope resolves as 404 (no existence probing).

**Auth & roles:**
- Phase 1 auth is Sanctum SPA session only (no OAuth, no API clients). Invalid credentials return an editorial 3-part error without revealing which field failed. Suspended memberships cannot authenticate. Auth endpoints rate-limited to 5/min per IP+email with 429 + `Retry-After`.
- Six roles exist (Platform admin, Organisation owner, Operations manager, Site supervisor, Technician, Contractor); multi-role memberships inherit the union of permissions. A central service-layer policy verifies abilities and client scope before any business logic runs; unauthorized mutations throw a domain exception mapped to 403.

**Evidence:** uploads are validated by magic bytes against an allowlist (jpeg/png/webp/pdf, ≤10 MB), stored in private S3-compatible storage under a tenant/client/owner-namespaced key, and read only via 10-minute signed URLs issued after tenant + client-scope verification. No public or guessable URLs. Non-image downloads set `Content-Disposition: attachment`.

**Audit & errors:** sensitive operations (auth events, exports, role/scope changes, deletions, data-protection requests) are recorded atomically in the same transaction to an append-only audit table (runtime role has no UPDATE/DELETE). All errors use one structured envelope — domain exceptions carry permanent snake_case codes rendered as `{ error: { code, message } }` on REST and mapped form errors on Inertia; raw database exceptions are sanitized to error tracking, never shown.

**Non-functional floors set here:** accessibility executed to WCAG 2.2 AA; observability (error tracking FE+BE, structured logs with org/user/request IDs, no personal data in logs) before real customer data; tiered rate limiting (auth 5/min, uploads 30/min, API 120/min) and HTTP security headers (HSTS, nosniff, DENY framing, referrer policy, CSP); tests run on real PostgreSQL + PostGIS as the non-owner role.

## Technical Decisions

- **Greenfield scaffold:** official Laravel React starter kit (Inertia + React + Tailwind) is the base. Pinned stack: PHP 8.4, Laravel 13, Inertia 3, React 19, TypeScript 5, Tailwind 4 (`@tailwindcss/vite`), shadcn/ui CLI, Sanctum 4, Pest. Lock files committed. Local dev via docker-compose with a PostGIS container and a pinned S3-compatible object-storage container.
- **Object storage is provider-agnostic:** the application uses only Laravel's `s3` disk, configured through `AWS_*` environment variables (endpoint, bucket, key, secret, region). Locally that endpoint is RustFS; in production it is a private Cloudflare R2 bucket, so switching providers never requires an application change. Buckets are private and objects are read only through short-lived pre-signed URLs. MinIO was the original local choice but its public images were withdrawn; LocalStack was rejected because its current image is licence-gated and the last free tag does not enforce private buckets.
- **Layered modular monolith:** adapters (Inertia controllers, REST controllers, jobs) are thin — validate input, call one application-service method, format output. All business rules, authorisation, tenant stamping, and audit dispatch live in application services; a feature is incomplete without a direct service-layer test. Modules: Tenancy & Identity, Registry, Inspections, Maintenance, Evidence, Reporting (read-only), Audit. One owning module per table; cross-module writes go through the owner's service.
- **TenantContext singleton** (organisation_id, user_id, role, client_scope) initialised by middleware; each request runs in a transaction whose first statement is `SET LOCAL app.org_id` (compatible with transaction-mode connection pooling). `organisation_id` is barred from DTOs, `$fillable`, FormRequests, and query params.
- **All tables use ordered UUID primary/foreign keys** (HasUuids); no integer IDs anywhere.
- **Full-stack typed contract:** Spatie Data objects are the single contract between PHP and the UI; TypeScript types are auto-generated (typescript-transformer into `resources/js/types/generated`). Hand-written duplicate TS interfaces are forbidden.
- **Append-only history tables** (including `audit_events`): no UPDATE/DELETE grants for the runtime role.
- **Cloud-agnostic infrastructure:** portable PostgreSQL, S3-compatible object storage, env-configured endpoints; no provider lock-in.
- **Structured logging** includes org_id, user_id, request_id and never personal data or credentials.

## UX & Interaction Patterns

- **Token system:** the Cloud Crafters Editorial Intelligence token set is mapped into Tailwind 4 `@theme` — full color palette (canvas, navy/blue/teal ramps, 5 badge semantic triples, 3 border tiers), Georgia editorial body + Arial UI type scale, 4px spacing grid, 0–3px radius (pills banned), 3 elevation levels, 150/200ms motion, breakpoints (≤760px mobile, 761–1023px, ≥1024px desktop with 1120px `.cc-page`), z-index layers. Tokens only — no raw hex/pixel literals in components. Zero external webfonts (system fonts only). `prefers-reduced-motion` disables transitions globally.
- **21 canonical `.cc-*` components** built as reskinned shadcn/ui primitives with fixed behavioral contracts; Epic 1 delivers all of them in two batches (layout/content primitives, then interactive/field primitives including property-map, evidence-uploader, evidence-gallery, checklist-runner, async-export-banner, mobile-quick-task-bar). Each renders its applicable states and passes a keyboard-only pass and axe scan.
- **Global chrome:** `.cc-skip-link` is the first focusable element on every page; desktop header shows organisation, Tier-2 client account scope switcher (switching reloads props with `preserveState: false`), module links, and role badge; ≤760px gets compact header, slide-over nav, and bottom quick-task bar (hidden when the virtual keyboard opens). One primary action per view; overlays never stack beyond 1 level; nav items outside the user's abilities are omitted.
- **11 operational states** implemented once as shared patterns — skeleton, empty first-use, empty no-results, zero-activity certification, focus-visible, field validation error, domain exception alert, rate-limited 429, network-degraded/upload progress with interrupted-retry, GPS-unavailable notice, permission-denied (404-vs-403), immutable/locked record — plus live-region announcements for async changes.
- **Editorial voice codified as shared copy:** sentence case, no em/en dashes, 3-part error formula, banned-words list enforced, and mandatory legal copy blocks (NIPOST disclaimer, immutable-inspection notice, zero-activity certification) as reusable components/constants.
- **Accessibility floor:** WCAG 2.2 AA — contrast guarantees including ≥3:1 non-text, 40px touch targets (36px compact mobile), 16px mobile inputs (no iOS zoom), status never by color alone.
- **Nigerian mobile resilience:** client-side photo downscaling ≤1920px before upload, paginated payloads (20/page), scoped partial reloads, MapLibre lazy-loaded (initial JS budget on field routes), fixed aspect ratios with row-matched skeletons.
- **Banned patterns (fail in review):** infinite scroll, optimistic transitions on compliance mutations, hover-only actions, stacked modals, decorative empty-state illustrations, color-only status, placeholder-as-label, icon-only buttons without accessible names.

## Cross-Story Dependencies

- 1.1 (scaffold) precedes everything; 1.2 (tenant context/RLS) precedes 1.3 (auth) and 1.4 (policies), and its middleware/context is consumed by every later epic.
- 1.5 (tokens) precedes 1.6–1.8; 1.6/1.7 (component batches) precede 1.8 (shell composes them) and 1.9 (states layer applies to components); 1.9's copy system is reused by all UI stories in Epics 2–4.
- 1.10 (evidence service) is consumed by Epic 3's workflow evidence capture (Story 3.12) and by the evidence-uploader/gallery components (1.7); 1.11 (audit + error envelope) is consumed by all later services and by Epic 4 exports and Epic 5's audit surface.
- External dependency: local docker-compose PostGIS and S3-compatible object storage (1.1) plus an S3-compatible bucket for evidence (1.10).
