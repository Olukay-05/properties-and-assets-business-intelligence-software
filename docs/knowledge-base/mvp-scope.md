# MVP scope — three workflows

## Vision

A multi-tenant platform for Nigerian FM providers to register client properties and assets, run inspections and maintenance, capture evidence, and produce client reports. Sold as **operational control + proof of work** — not as postcode, AI or advanced geospatial capability.

## The three workflows (build and test these before anything else)

1. **Property & asset registration** — organisation → client → property/site (address, site code, coordinates, optional postcode) → zones/areas → assets (tag, category, condition, status, custodian).
2. **Inspection & maintenance** — schedule → run inspection (checklist + photos + condition) → raise request → work order → assign technician → complete + evidence → update history.
3. **Client reporting & evidence** — compile a client-ready report → export PDF/CSV → share manually.

Each workflow ships only when it works end-to-end and passes its acceptance test and automated tests.

## Roles & permissions (Phase 1)

Platform admin · organisation owner · operations manager · site supervisor · technician · contractor (minimal). Client read-only access is **deferred** (see below).

## Business rules (the important ones)

- Tenant = the FM organisation; its managed property owners are `client_accounts` **inside** that tenant, not separate tenants.
- `organisation_id` comes from the authenticated session, never from request input.
- The postcode is an optional location reference; the product is fully usable without NIPOST.
- A postcode never proves title/ownership/occupancy; never label it as such.
- Approved inspections are immutable; corrections create a new version linked to the original.
- Work orders can reference a site without a registered asset.

## Deferred (Phase 3, gated on evidence)

MCP / AI-agent tools · webhooks · Tauri desktop · offline sync · advanced geospatial analytics (clustering, proximity) · AI-drafted reports · WhatsApp integration · contractor portal / SLA scorecards · preventive-maintenance rules engine · parts/inventory · IoT · rent/lease/tenant/access control · client-facing portal · NIPOST levels 2+.

## Out of scope (never)

Title, ownership, identity or tenant verification.
