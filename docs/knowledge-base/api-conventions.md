# REST API conventions (Phase 1)

## Scope

**First-party only.** About 15 endpoints mirroring the three workflows. No public developer portal, no `api_clients` registry, no webhooks, no third-party rate-limit tiers.

## Endpoints

| Area | Endpoints |
| --- | --- |
| Identity | `GET /api/v1/me` |
| Workflow 1 (registration) | `GET/POST /api/v1/properties`, `GET /api/v1/properties/{id}`, `GET/POST /api/v1/properties/{id}/assets`, `GET/PATCH /api/v1/assets/{id}` |
| Workflow 2 (inspection & maintenance) | `POST /api/v1/inspections`, `GET /api/v1/inspections/{id}`, `POST /api/v1/inspections/{id}/complete`, `GET/POST /api/v1/work-orders`, `GET /api/v1/work-orders/{id}`, `POST /api/v1/work-orders/{id}/assign`, `POST /api/v1/work-orders/{id}/complete` |
| Workflow 3 (reporting) | `GET /api/v1/reports`, `GET /api/v1/properties/{id}/history` |

## Conventions

- Versioned under `/api/v1`; resource nouns plural; nesting for sub-resources (`/properties/{id}/assets`).
- Consistent JSON envelope `{ "data": …, "meta": … }` and error shape `{ "error": { "code", "message" } }`.
- **OpenAPI** generated from route definitions; the spec is published in the docs site.
- Add an endpoint only when a workflow or pilot requires it.

## Auth distinction

| Audience | Mechanism | Status |
| --- | --- | --- |
| First-party (own React/Inertia app + operator tooling) | Laravel **Sanctum** — SPA cookie session + personal-access tokens | Built in Phase 1 |
| External integrators / AI agents | OAuth 2.1 via **Passport**, scoped and per-tenant | Deferred to Phase 3 |

## Rules

- `organisation_id` is never accepted from the client; the tenant comes from the authenticated session.
- All endpoints go through the shared service layer — no business logic in controllers.
