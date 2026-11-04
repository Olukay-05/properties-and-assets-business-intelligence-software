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
2. Check for an existing service and reuse it — do **not** duplicate business rules.
3. Confirm the feature is within the three MVP workflows. If it is deferred (MCP, webhooks, desktop, offline, advanced geo, client portal, etc.), stop and ask before building it.

## 5. When you change something

Update the relevant knowledge-base file in the same task whenever you change a requirement, data model, API contract or business rule. Documentation must describe what is implemented and tested.

## 6. Testing requirements (CI gates)

- Automated tests for the three workflows and critical business rules.
- A tenant-isolation matrix for each core entity, verified at three layers — **HTTP**, **service layer**, and **raw SQL** — plus negative tests that a forged `organisation_id` in a request body is ignored. These must pass before merge/deploy.

## Key files

- `docs/knowledge-base/README.md` — index, development standards, testing
- `docs/knowledge-base/mvp-scope.md` — vision, three workflows, roles, deferred
- `docs/knowledge-base/data-model.md` — entities, tenant isolation, geospatial
- `docs/knowledge-base/architecture-decisions.md` — confirmed decisions (D1–D10)
- `docs/knowledge-base/api-conventions.md` — REST API scope and conventions
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
