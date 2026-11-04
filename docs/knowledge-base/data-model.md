# Data model — Phase 1

## Tenant isolation

- Shared database, single schema. `organisation_id` on every tenant-owned row; UUID primary and foreign keys (`HasUuids`) across all tables.
- **Table classification:**
  - *System Identity Tables (No RLS):* `users` (+ Sanctum `personal_access_tokens`), `organisations`. Accessed only during authentication and initial tenant resolution.
  - *Tenant Tables (RLS Mandatory):* `memberships`, `client_accounts`, `properties`, `locations`, `site_zones`, `assets`, `asset_custody`, `inspection_templates`, `inspection_template_items`, `inspections`, `inspection_responses`, `maintenance_requests`, `work_orders`, `work_order_events`, `contractors`, `attachments`, `audit_events`.
- RLS: `ENABLE ROW LEVEL SECURITY` + `FORCE ROW LEVEL SECURITY` + `USING (organisation_id = current_setting('app.org_id', true)::uuid)`. Runtime connects as a non-owner role without `BYPASSRLS`.
- `SET LOCAL app.org_id = '<id>'` executed inside a database transaction at the start of each request/job (safe with Neon PgBouncer transaction pooling).
- **Client scoping:** Every client-owned table (`properties`, `site_zones`, `assets`, `inspections`, `maintenance_requests`, `work_orders`, `attachments`) carries a denormalised, immutable `client_account_id` enforced by a global `ClientScope` when `memberships.client_scope` is restricted.
- Defense in depth: the app scopes every query **and** RLS enforces at the database. Automated tests verify 5 vectors: HTTP, service layer, raw SQL, queued jobs, and signed URLs.

## Entities & key invariants (Phase 1)

`organisations` · `users` (+ Sanctum `personal_access_tokens`) · `memberships` (org_id, user_id, role, client_scope, status) · `client_accounts` (company name, primary contact person, email, phone, address, status `active`/`inactive`, internal notes) · `properties` (with `geom`, `site_code`, `postcode_value`, `postcode_status`) · `locations` (append-only coordinate history) · `site_zones` · `assets` (unique `(organisation_id, property_id, tag)`) · `asset_custody` (append-only handover history) · `inspection_templates` (versioned via `template_family_id` + `version`, unique per org) + `inspection_template_items` · `inspections` (immutable once `approved_at` set; corrections link via `supersedes_id`) + `inspection_responses` · `maintenance_requests` · `work_orders` (status enum: `open` / `assigned` / `in_progress` / `completed` / `on_hold` / `cancelled`) + `work_order_events` (append-only transitions) · `contractors` · `attachments` (provenance: `uploaded_by`, `uploaded_at`, polymorphic owner `inspection`/`asset`/`work_order`, optional coordinates; immutable once parent is completed/approved) · `audit_events` (append-only).

## Deferred (do NOT create in Phase 1)

`api_clients` · `mcp_tools` · `tool_invocations` · `approvals` · `webhook_subscriptions` / `webhook_events` · `client_share_links` · `asset_locations` (relocation history) · `subscriptions` / `usage_records` (billing is added at commercial launch).

Supervisor sign-off in Phase 1 is a field on work orders/inspections, **not** a separate `approvals` table.

## Geospatial (canonical)

- `properties.geom geometry(Point, 4326)` with a GiST index is the **single source of truth**.
- `latitude`/`longitude` are **derived** (`ST_Y(geom)` / `ST_X(geom)`); there are no independent lat/lng columns to drift.
- Distances are computed in **metres** via the `::geography` cast, only through `Geo::distanceMetres()` / `Geo::withinMetres()`.
- `ST_MakePoint(longitude, latitude)` — x is longitude, y is latitude.
- `locations` rows carry `accuracy_m`, `source`, `captured_by`, `captured_at`; the current location is the latest row.

## Postcode

- Store the customer-entered postcode string + validation status.
- NIPOST lookups are optional, server-side and feature-flagged; never block saving a site on them.
- Store only the verification **outcome**, never a copy of NIPOST data.
