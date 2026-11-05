# AGENTS.md — working instructions for AI coding agents

Read this before implementing anything, and consult the knowledge base referenced below.

## 1. What this project is

A multi-tenant **Laravel + React** platform for Nigerian facilities-management (FM) providers to register client properties and assets, run inspections and maintenance, capture evidence, and produce client reports. See `docs/knowledge-base/mvp-scope.md` and `docs/development-plan-review.md` (Revision 2) for scope.

## 2. Standing rules (non-negotiable)

1. **Never put business logic exclusively in the UI.** An action from a button, an API request or a (future) MCP tool must use the same underlying business rules.
2. **Never put business logic directly in an Inertia controller.** Inertia controllers, REST API endpoints and future MCP tools are thin adapters over the same Laravel service layer. A feature is not complete until its logic lives in the service layer, not in a controller or component.
3. **Never make the application dependent on one AI provider.** Expose capabilities through standard APIs; keep models/agents swappable.
4. **Never make the application dependent on one cloud provider.** Standard PostgreSQL + PostGIS, portable object storage, provider-agnostic auth, configurable infrastructure, independent backups.
5. **Never ship documentation for unverified behaviour.** Docs, API references and tool descriptions must reflect implemented and tested behaviour, never planned behaviour.

## 3. Working conventions

- **Modular monolith.** One Laravel app. No microservices, no separate services.
- **Tenant isolation.** Every tenant-owned row carries `organisation_id`; it is resolved from the authenticated session, **never** from request input. PostgreSQL RLS is enforced on all tenant tables (see `docs/knowledge-base/data-model.md`).
- **Geospatial.** Store `geometry(Point, 4326)`; compute distances in **metres** via the geography cast, through the single `Geo::distanceMetres()` / `Geo::withinMetres()` helper. Never use raw `ST_Distance` on geometry and expect metres.
- **API.** Versioned `/api/v1`, OpenAPI generated from routes. Phase 1 auth is **Sanctum only** (first-party). No OAuth, no `api_clients`, no webhooks yet.

## 4. Before implementing a feature

1. Consult `docs/knowledge-base/` (especially `mvp-scope.md`, `data-model.md`, `architecture-decisions.md`).
2. Consult the relevant security foundation document in `docs/security/` before implementing or modifying authentication, user permissions, tenant-scoped data, APIs, file uploads, report exports, environment variables, or NDPA data-protection functionality.
3. Check for an existing service and reuse it — do **not** duplicate business rules.
4. Confirm the feature is within the three MVP workflows. If it is deferred (MCP, webhooks, desktop, offline, advanced geo, client portal, etc.), stop and ask before building it.

## 5. When you change something

Update the relevant `docs/knowledge-base/` and `docs/security/` files in the same task whenever you change a requirement, data model, API contract, business rule, or security control. Documentation must describe what is implemented and tested.

## 6. Testing requirements (CI gates)

- Automated tests for the three workflows and critical business rules.
- A tenant-isolation matrix for each core entity, verified at five vectors — **HTTP**, **service layer**, **raw SQL** (under the non-owner runtime DB role), **queued jobs**, and **signed URLs** — plus negative tests that a forged `organisation_id` in a request body is ignored. These must pass before merge/deploy.

## 7. Security & data protection standard (`docs/security/`)

Every AI coding agent and contributor must enforce the six-document security foundation in `docs/security/`:

1. **Read before building:** Consult the matching security document before touching authentication, role/client-scope authorisation, tenant-scoped migrations/models, `/api/v1` endpoints, file uploads, report exports, or compliance workflows:
   - `docs/security/AUTH.md` — Authentication, onboarding, session management, 6-role union permissions, and `ClientScope`.
   - `docs/security/SECURITY.md` — Mandatory secure coding rules, input validation, rate limiting, `finfo` upload checks, error sanitization, and HTTP headers.
   - `docs/security/RLS-POLICIES.md` — PostgreSQL `FORCE ROW LEVEL SECURITY`, non-owner `pai_app` DB role (`NOBYPASSRLS`), transaction-scoped `SET LOCAL app.org_id`, append-only table privileges, and the 17 tenant tables.
   - `docs/security/ENV.md` — Environment variable inventory, placeholder rules, and secret management across Local, Staging, and Production.
   - `docs/security/THREAT-MODEL.md` — Prioritised threat scenarios, attack paths, verified vs. outstanding controls, and required verification tests.
   - `docs/security/DATA-PROTECTION.md` — NDPA 2023 controller/processor boundaries, data minimisation, retention/deletion, sub-processors, and 72-hour breach logging.
2. **Follow mandatory agent rules:** Obey the **Mandatory Rules for AI Coding Agents** and **Explicit Prohibitions** in `docs/security/SECURITY.md` (§2–§3).
3. **Respect tenant isolation:** Enforce all application-layer (`TenantScope`, `ClientScope`, `TenantAware` jobs) and database-layer (`FORCE RLS`, `NOBYPASSRLS`) requirements in `docs/security/RLS-POLICIES.md`.
4. **Zero secrets in code or bundles:** Never introduce credentials, API keys, tokens, or real passwords into source code, documentation, `.env.example`, Git commits, logs, or `VITE_*` frontend bundles (`docs/security/ENV.md`).
5. **Ship security tests with code:** Include positive authorisation, negative role/scope denial (`403`/`404`), and cross-tenant isolation tests whenever implementing or modifying security-sensitive functionality.
6. **Keep security docs in sync:** Update the relevant `docs/security/*.md` document whenever an implementation status, architectural decision, or security requirement changes.
7. **Surface gaps honestly:** Report any security assumptions, unverified controls, or gaps explicitly in your task completion report rather than silently ignoring them or marking unimplemented controls as complete.

## Key files

- `docs/knowledge-base/README.md` — index, development standards, testing
- `docs/knowledge-base/mvp-scope.md` — vision, three workflows, roles, deferred
- `docs/knowledge-base/data-model.md` — entities, tenant isolation, geospatial
- `docs/knowledge-base/architecture-decisions.md` — confirmed decisions (D1–D10)
- `docs/knowledge-base/api-conventions.md` — REST API scope and conventions
- `docs/security/AUTH.md` — authentication, sessions, roles, and client scope
- `docs/security/SECURITY.md` — application security standards and mandatory agent rules
- `docs/security/RLS-POLICIES.md` — PostgreSQL RLS policies, DB roles, and isolation test matrix
- `docs/security/ENV.md` — environment variables and secrets management
- `docs/security/THREAT-MODEL.md` — platform threat model and risk prioritisation
- `docs/security/DATA-PROTECTION.md` — NDPA 2023 data protection, privacy, and retention
- `docs/development-plan-review.md` — approved plan (Revision 2)
- `docs/updates.md` — principles, problem analysis, pricing hypotheses, NIPOST/compliance

<!-- enterprise-ui-ux:start -->
## UI/UX standard (enterprise-ui-ux skill)

Any task that creates, changes, reviews or specifies something users see or touch must follow the `enterprise-ui-ux` skill.

Skill location: `.agents/skills/enterprise-ui-ux/SKILL.md`

Required steps for every UI task:
1. Read `.agents/skills/enterprise-ui-ux/SKILL.md` and follow its modes.
2. Read `docs/design/design-context.md` first. It overrides generic defaults. If it is missing, create it from `.agents/skills/enterprise-ui-ux/templates/design-context.md` before designing or building.
3. Load only the reference files that SKILL.md maps to the task.
4. Run the matching checklist before finishing:
   - UX designer agent: `checklists/ux-spec-checklist.md`
   - Dev agent: `checklists/dev-implementation-dod.md`
   - Reviewer or QA: `checklists/design-review-rubric.md`

Hard rules: WCAG 2.2 AA, all states designed and built, tokens instead of raw values, mobile first, real copy, no dark patterns.

BMAD mapping: UX design documents and screen specs are produced in SPEC mode. Stories that touch UI include the UI acceptance criteria from the dev definition of done. Reviews that find Blocker or Major issues fail the story.
<!-- enterprise-ui-ux:end -->
