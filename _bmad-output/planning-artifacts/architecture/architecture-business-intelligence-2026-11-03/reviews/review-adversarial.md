# Adversarial Architecture Review

**Target:** `ARCHITECTURE-SPINE.md` (Phase 1 MVP)  
**Date:** 2026-11-03  
**Reviewer:** Architecture Reviewer (Adversarial & Security Attack Lens)  
**Verdict:** PASS WITH REQUIRED TIGHTENINGS (6 Findings)

---

## 1. Executive Summary

The architecture spine has a strong foundation: modular monolith, unified service layer, RLS enforcement, and strict geospatial mutators. However, when stress-tested against two independent developers building separate modules simultaneously, six concrete divergence points and potential security/isolation leaks emerge. 

Below are the constructed failure scenarios and the exact rule tightenings required to close them.

---

## 2. Adversarial Findings & Attack Scenarios

### Finding 1: Unauthenticated Login vs RLS Scope (High)
* **Attack / Conflict:** If RLS is blanket-applied to all tables including `users` and `organisations`, a user trying to log in at `POST /api/v1/login` has no `app.org_id` set yet. A query like `User::where('email', $email)->first()` will return zero rows because RLS policy `organisation_id = current_setting('app.org_id')::uuid` evaluates to null/false. Conversely, if a developer mistakenly removes RLS from tenant tables to "make login work", tenant isolation breaks completely.
* **Hole:** Table classification between system/identity tables and tenant tables is implicit in the spine.
* **Proposed Rule Fix (AD-4):** Explicitly classify tables into:
  1. *System Identity Tables (No RLS):* `users`, `organisations`. Accessed only during authentication and tenant resolution.
  2. *Tenant Tables (RLS Mandatory):* `memberships`, `client_accounts`, `properties`, `locations`, `site_zones`, `assets`, `asset_custody`, `inspection_templates`, `inspection_template_items`, `inspections`, `inspection_responses`, `maintenance_requests`, `work_orders`, `work_order_events`, `contractors`, `attachments`, `audit_events`.
  `memberships` table enforces `organisation_id = current_setting('app.org_id')::uuid` to prevent cross-tenant membership enumeration.

---

### Finding 2: Client Scope Leak in Application Queries (High)
* **Attack / Conflict:** Developer A builds Inspections and writes `Inspection::where('property_id', $id)->get()`. Developer B builds Work Orders and writes `WorkOrder::where('status', 'open')->get()`. RLS checks `organisation_id` only. If a technician user has a restricted `memberships.client_scope` (e.g. only Client X), but Property P belongs to Client Y, Developer A and B's queries leak Client Y's records unless every query manually specifies `whereIn('client_account_id', ...)`. Developers will inevitably forget this on nested endpoints.
* **Hole:** AD-5 describes denormalizing `client_account_id`, but enforcement relies on manual service-level filtering discipline.
* **Proposed Rule Fix (AD-5):** In addition to the `TenantScope` (`organisation_id`), all client-owned models must register a global `ClientScope`. When `TenantContext::clientScope()` is non-null, `ClientScope` automatically appends `whereIn('client_account_id', $scope)`. Only users with unrestricted client scope (or explicit system services) query across all clients in the organisation.

---

### Finding 3: Cross-Module Eloquent Write Bypass (Medium)
* **Attack / Conflict:** Developer A in Maintenance wants to attach a completion photo to a Work Order. Instead of calling `EvidenceService::attach()`, Developer A notices the Eloquent relation on the `WorkOrder` model: `$workOrder->attachments()->create(...)`. This directly inserts a row into `attachments`, bypassing file size checks, R2 upload path conventions, client_account_id stamping, and virus/MIME validation.
* **Hole:** AD-2 prohibits cross-module table writes, but does not explicitly ban writable Eloquent relationships across module models.
* **Proposed Rule Fix (AD-2 & AD-13):** Eloquent relationships traversing domain module boundaries (e.g., `WorkOrder::attachments()`, `Inspection::maintenanceRequests()`) are strictly **read-only** query relationships. All creation, modification, and attachment linking must execute through the owning module's application service (`EvidenceService::attachPhoto()`, `MaintenanceService::createFromInspection()`).

---

### Finding 4: Deletion of Evidence on Completed Work Orders / Inspections (Medium)
* **Attack / Conflict:** Technician marks a Work Order as `completed` with a required photo. Later, a user deletes the photo attachment via an evidence endpoint or direct call. The Work Order remains `completed` with zero evidence, breaking FR-15 and pilot audit readiness.
* **Hole:** AD-11 mandates evidence at the moment of transition to `completed`, but does not guard attachment lifecycle after transition.
* **Proposed Rule Fix (AD-11 & AD-13):** Attachments linked to completed work orders or approved inspections are immutable: `EvidenceService::delete()` rejects deletions for attachments whose parent entity is in a finalized state (`work_order.status = completed`, `inspection.approved_at IS NOT NULL`). Only NDPA data erasure routines running via privileged administrative background jobs may redact evidence.

---

### Finding 5: Initial Property Creation Skipping Coordinate History (Medium)
* **Attack / Conflict:** Developer A implements `PropertyService::create()` and passes `geom` directly to `Property::create($data)`. The property is saved on the map, but no entry is created in `locations`. The site history (FR-3) is empty until the site is updated a second time.
* **Hole:** AD-7 specifies `LocationService::setLocation` for updates, but does not explicitly bind initial creation.
* **Proposed Rule Fix (AD-7):** `properties.geom` is not mass-assignable. Initial creation of a Property with coordinates must invoke `LocationService::setLocation()` within the same database transaction, ensuring the initial `locations` row (`source = 'initial_registration'`) is always generated.

---

### Finding 6: Inspection Template Version Drift During Field Walk (Low)
* **Attack / Conflict:** Amara starts an inspection in the morning against Template v1. While she is in the field, an Operations Manager edits the template in the office. If the inspection dynamically resolves the template, checklist items may mismatch responses or fail validation upon submission.
* **Hole:** AD-10 does not fix the exact point at which the template version is pinned to the inspection.
* **Proposed Rule Fix (AD-10):** An inspection binds to a specific `inspection_template_id` (representing an exact version row) at the time the inspection is scheduled/created. Once assigned, `inspection.inspection_template_id` is immutable.

