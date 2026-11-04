# PRD ↔ Source Inputs — Reconciliation Notes

Reviewed `prd.md` + `addendum.md` against: `docs/development-plan-review.md` (Rev 2), `docs/knowledge-base/mvp-scope.md`, `data-model.md`, `architecture-decisions.md`, `api-conventions.md`.

Deferred items already listed in the PRD's Non-Goals / MVP-scope / Open Questions sections are intentionally **not** flagged below.

## Gaps (material source rules omitted or narrowed)

1. **Role/permission model not enumerated.**
   Source: `mvp-scope.md` §"Roles & permissions (Phase 1)" lists six roles — platform admin · organisation owner · operations manager · site supervisor · technician · contractor (minimal) — with permissions assigned per role. The PRD's FR-20 ("Authorise by role") is generic and never names the role set or maps what each role may do; "platform admin" and "organisation owner" never appear anywhere. Should map to §2 (Target User) / §4.4 FR-20.

2. **Supervisor sign-off on inspections dropped.**
   Source: `data-model.md` (Deferred section) and `development-plan-review.md` §12 both state supervisor sign-off is a field on work orders **and inspections** (not a separate `approvals` table). The PRD carries this only for work orders (FR-13); inspection-level sign-off is absent. Should map to §4.2 FR-11 (conduct inspection).

3. **NIPOST data rule not fully carried.**
   Source: `data-model.md` §"Postcode" — lookups are optional, server-side and feature-flagged, and "store only the verification outcome, never a copy of NIPOST data." PRD FR-2 covers the string + validation status + never-blocks, but omits "never store a copy of NIPOST data" and the server-side/feature-flag constraint. Should map to §4.1 FR-2 and §6.2 (data governance).

4. **Pilot go/no-go criteria omitted.**
   Source: `development-plan-review.md` §10.3 defines explicit proceed vs pivot/stop thresholds (≥1 paid pilot, target 2 + improvement in ≥2 measures + renewal intent). PRD §9 captures the metrics and counter-metrics but not the go/no-go decision rule. Should map to §9 (Success Metrics).

5. **Qualitative "editorial" tone/feel not carried.**
   Source: `development-plan-review.md` §12 and D4 name the design system as the visual source of truth and require restyling to "the editorial look." The PRD preserves the "proof of work" positioning (§1) and points to the design system (§0), but carries no tone/voice/feel guidance — the "editorial" descriptor is dropped. Should map to §1 (Vision) or a dedicated UX/tone section.

## Checked and present (no gap)

- **Tenant isolation** — session-only `organisation_id`; app scoping + RLS (+ `FORCE RLS`); `SET LOCAL` per transaction; background/cron job re-scoping; private buckets + signed URLs; 3-layer test matrix (HTTP, service, raw SQL) plus negative forged-`organisation_id` tests. → FR-19, §5.1, addendum.
- **Geospatial** — `geometry(Point,4326)` canonical; lat/lng derived (`ST_Y`/`ST_X`), no independent columns; metre distances via the `::geography` cast only through `Geo::distanceMetres()`/`Geo::withinMetres()`; `ST_MakePoint(lng, lat)`; single `setLocation()` mutator + `locations` history (`accuracy_m`, `source`, `captured_by`, `captured_at`). → FR-3, addendum.
- **"Proof of work" positioning** and all deferred/out-of-scope items → §1, §7, §8.
- **NDPA pilot-readiness** (controller/processor + DPA, data minimisation, purpose limitation/retention, breach notification, cross-border transfer, DSAR) → §6.1; NDPC registration and National Cloud Policy carried as open questions → §10.
