# Product Knowledge Base — index

Operational source of truth for the **reduced MVP** (three workflows). AI coding agents: read the relevant file before implementing, and update it in the same task when you change a requirement, data model, API contract or business rule.

## Files

| File | Holds |
| --- | --- |
| `mvp-scope.md` | Vision, the three workflows, roles/permissions, business rules, deferred list |
| `data-model.md` | Phase 1 entities, tenant isolation, geospatial canonical storage, postcode policy |
| `architecture-decisions.md` | Confirmed decisions (D1–D10), invariants, stack |
| `api-conventions.md` | REST API scope, versioning, OpenAPI, auth |

## Security & Data Protection Foundation (`docs/security/`)

| File | Holds |
| --- | --- |
| `../security/AUTH.md` | Authentication, onboarding model, session rules, 6-role union permissions, `ClientScope`, API auth |
| `../security/SECURITY.md` | Mandatory secure coding rules for developers & AI agents, rate limiting, `finfo` upload checks, headers |
| `../security/RLS-POLICIES.md` | PostgreSQL `FORCE RLS` policies, `pai_owner` vs `pai_app` DB roles, `SET LOCAL` pooling safety, 17-table matrix |
| `../security/ENV.md` | Environment variables (`local`, `staging`, `production`), RustFS/R2 storage config, secret rotation |
| `../security/THREAT-MODEL.md` | Assets, threat actors, prioritised threat scenarios (`T-01`–`T-11`), verified vs outstanding controls |
| `../security/DATA-PROTECTION.md` | NDPA 2023 controller/processor roles, data minimisation, retention/deletion, sub-processors, breach workflow |

## Source-of-truth hierarchy

1. `docs/development-plan-review.md` (Revision 2) — approved scope, decisions, roadmap, hosting.
2. This knowledge base — operational conventions and data model.
3. `docs/updates.md` — principles, problem analysis, pricing hypotheses, NIPOST/compliance detail.

Where these conflict on **scope**, the plan and knowledge base win.

## Development standards

- Modular monolith (Laravel). No microservices.
- Plain SQL / Laravel migrations; never edit an applied migration — add a new one.
- Business rules live in the service layer only (AGENTS.md rule 2).
- "Done" for a feature = feature + tests + API/docs update.
- Environments: `dev` and `staging` on Neon branches; `production` on a separate Neon project and VPS.

## Testing requirements (CI gates)

1. Automated tests for the three workflows and critical business rules.
2. Tenant-isolation matrix for each core entity at three layers: HTTP, service layer, raw SQL. Must pass before merge/deploy.
3. Negative tests: a forged `organisation_id` in a request body is ignored/rejected.
