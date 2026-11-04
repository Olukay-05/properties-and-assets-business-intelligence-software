# Input Reconciliation Report 2: Architecture Spine (`ARCHITECTURE-SPINE.md`)

- **Source Reconciled**:
  - `_bmad-output/planning-artifacts/architecture/architecture-business-intelligence-2026-11-03/ARCHITECTURE-SPINE.md` (`AD-1` through `AD-22`, Consistency Conventions, Stack, Structural Seed, Capability Map, Deferred)
- **Target UX Spines**:
  - `_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/DESIGN.md`
  - `_bmad-output/planning-artifacts/ux-designs/ux-business-intelligence-2026-11-03/EXPERIENCE.md`
- **Reconciliation Status**: **PASS — All 22 Architecture Decisions (`AD-1`..`AD-22`), frontend stack specs, data contracts, security/rate-limit rules, and infrastructure constraints are faithfully reflected.** *(One minor path-casing convention note is surfaced in §3 below.)*

---

## 1. Architecture Decisions (`AD-1` .. `AD-22`) Matrix

| AD | Architectural Rule (`ARCHITECTURE-SPINE.md`) | UX Spine Realization (`DESIGN.md` & `EXPERIENCE.md`) | Status |
|---|---|---|---|
| **AD-1** | **Unified service layer; adapters are thin**: Zero business logic in React or Inertia controllers; server is authoritative. | `EXPERIENCE.md` §Foundation & §Interaction Primitives #6 (bans optimistic UI state transitions on compliance mutations; awaits service + `AuditService` commit). | Covered |
| **AD-2** | **Strict module ownership of tables & read-only cross-module relations**: 7 modules (`Tenancy & Identity`, `Registry`, `Inspections`, `Maintenance`, `Evidence`, `Reporting`, `Audit`); no separate `approvals` table. | `EXPERIENCE.md` §Information Architecture groups all 14 Inertia pages by owning module; `InspectionRunner` & `WorkOrderDetail` treat supervisor sign-off as an inline field/action on the entity. | Covered |
| **AD-3** | **Tenant resolved exclusively from authenticated session**: `TenantContext` (`organisation_id`, `user_id`, `role`, `client_scope`); `organisation_id` barred from DTOs/forms. | `EXPERIENCE.md` §Foundation & `app-shell-header` (#1): zero `organisation_id` inputs in any form or URL query. | Covered |
| **AD-4** | **PostgreSQL RLS backstop with transaction-scoped tenant binding**: `FORCE ROW LEVEL SECURITY` on all 17 tenant tables; non-owner runtime DB role. | `EXPERIENCE.md` §Foundation explicitly binds to `AD-4`. | Covered |
| **AD-5** | **Client scoping via denormalized `client_account_id` and global `ClientScope`**: `memberships.client_scope` (`null` or UUID array). | `EXPERIENCE.md` `app-shell-header` (#1 Client Account Scope Switcher reloading with `preserveState: false`), `Tenancy/MembershipsIndex.tsx` (`Flow S-3`), and `State Patterns` #10 (`404` on out-of-scope UUID access). | Covered |
| **AD-6** | **Universal UUID primary and foreign keys**: Ordered UUIDs across all tables and routes (`/properties/[uuid]`, `/assets/[uuid]`, etc.). | `EXPERIENCE.md` §Information Architecture route column uses `[uuid]` across all detail routes. | Covered |
| **AD-7** | **Canonical Point geometry storage & single geographic mutator**: `properties.geom geometry(Point, 4326)`, `LocationService::setLocation()`, `ST_Y`/`ST_X` derived lat/lng, `Geo::distanceMetres()`. | `EXPERIENCE.md` §Foundation, `property-map` (#15), and `UJ-1` steps 2 & 6 explicitly invoke `LocationService::setLocation()` and display distances/accuracy in metres (`±m`). | Covered |
| **AD-8** | **Append-only history tables; latest row defines current state**: `locations`, `asset_custody`, `work_order_events`, `audit_events` have no runtime `UPDATE`/`DELETE` grants; ordered descending. | `EXPERIENCE.md` `timeline-feed` (#19) & `Interaction Primitives` #2 (append-only location & custody modals; descending order with latest reading at top). | Covered |
| **AD-9** | **Asset timeline assembled dynamically as a read model**: Computed from `inspections`, `work_order_events`, `asset_custody`, and `attachments`. | `EXPERIENCE.md` `Registry/AssetDetail.tsx`, `timeline-feed` (#19), `UJ-3` step 4, and `Flow S-2`. | Covered |
| **AD-10** | **Inspection templates versioned by row; inspections immutable upon approval**: `template_family_id` + `version`; pinned `inspection_template_id`; immutable when `approved_at` set; `supersedes_id` for corrections. | `EXPERIENCE.md` `Inspections/TemplatesIndex.tsx`, `InspectionRunner.tsx`, `checklist-runner` (#18), `State Patterns` #11, `UJ-2` step 6, and `Flow S-1`. | Covered |
| **AD-11** | **Encapsulated work order state machine & evidence immutability**: `WorkOrderService::transition()` (`open → assigned → in_progress → completed`, `on_hold`, `cancelled`); completion requires `>=1` photo with `captured_at`; GPS optional; attachments locked upon completion/approval. | `EXPERIENCE.md` `WorkOrderDetail.tsx`, `evidence-gallery` (#17 hides delete button on locked parent), `State Patterns` #11, `Interaction Primitives` #4, and `UJ-3` steps 2–5. | Covered |
| **AD-12** | **Service-layer authorization via unified role policy**: 6 Phase 1 roles; multi-role union of permissions; assignee check on work order completion. | `EXPERIENCE.md` §Information Architecture role column, `State Patterns` #10 (`403` domain notice if wrong Technician attempts completion), `UJ-3` step 5, `Flow S-3`. | Covered |
| **AD-13** | **Private evidence storage with provenance & signed URL gateway**: `EvidenceService` writes to private R2; 10-minute signed URLs after tenant + `client_scope` check. | `DESIGN.md` & `EXPERIENCE.md` #16 `evidence-uploader` & #17 `evidence-gallery` (10-minute signed R2 URLs + automatic Inertia partial reload `only: ['attachments']` if a signed URL expires with `HTTP 403`). | Covered |
| **AD-14** | **Explicit tenant context propagation for background jobs**: `TenantAware` queued jobs serializing `organisation_id` and `user_id`. | `EXPERIENCE.md` `async-export-banner` (#20), `Interaction Primitives` #5, and `UJ-4` step 3. | Covered |
| **AD-15** | **Unified report read model with dual renderers & constrained concurrency**: `ReportService::compile()` → `ReportData` → `spatie/laravel-pdf` (Chromium, `concurrency = 1`) + CSV via queued job. | `DESIGN.md` #20 `async-export-banner` & `EXPERIENCE.md` `ReportCompiler.tsx`, `async-export-banner` (#20 `usePoll(3000)`), `Interaction Primitives` #5, and `UJ-4` steps 2–4. | Covered |
| **AD-16** | **Strongly-typed Data objects as full-stack contract**: Spatie `Data` DTOs transformed into `resources/js/types/generated` via `spatie/typescript-transformer`. | `EXPERIENCE.md` §Foundation explicitly binds all Inertia page props and `/api/v1` payloads to `resources/js/types/generated` (`AD-16`). | Covered |
| **AD-17** | **Standardized error envelope & domain exception mapping**: Permanent snake_case codes (`work_order_invalid_transition`, `work_order_missing_completion_photo`, etc.); sanitized DB errors. | `EXPERIENCE.md` `State Patterns` #5 (`HTTP 422`) and #6 (`DomainException` top-of-section editorial alert strip preserving unsaved form text). | Covered |
| **AD-18** | **Atomic audit logging within originating transaction**: `AuditService::record($action, $target, $metadata)` inside active transaction. | `EXPERIENCE.md` `Audit/AuditEventsIndex.tsx`, `Interaction Primitives` #6, `UJ-4` step 3, and `Flow S-3`. | Covered |
| **AD-19** | **Real PostgreSQL/PostGIS test execution with non-owner RLS enforcement**: 5-vector isolation matrix. | `EXPERIENCE.md` §Foundation & `State Patterns` #10 align HTTP/UI behaviour with the 5-vector isolation expectations. | Covered |
| **AD-20** | **Cloud- and AI-agnostic infrastructure abstractions**: Portable S3/R2 storage, standard PostGIS, `<PropertyMap>` encapsulating MapLibre GL JS + OpenFreeMap. | `DESIGN.md` #15 `property-map` & `EXPERIENCE.md` §Foundation / #15 (`<PropertyMap>` abstraction). | Covered |
| **AD-21** | **Concurrency locks, unique constraints, and idempotent queue consumers**: `lockForUpdate()`, unique `(org, property, tag)` and `(org, family, version)`, `ShouldBeUnique` export jobs. | `EXPERIENCE.md` `async-export-banner` (#20 `ShouldBeUnique`), `State Patterns` #8 (prevents duplicate submissions on flaky mobile networks), `button` (#7 `aria-busy="true"` while `processing`). | Covered |
| **AD-22** | **Tiered rate limiting, magic-byte upload verification, and HTTP security headers**: `5/min` auth, `30/min` evidence upload, `5/min` report export, `120/min` API (`HTTP 429` + `Retry-After`); `finfo` allowlist (`jpeg`, `png`, `webp`, `pdf` `<=10 MB`). | `EXPERIENCE.md` `evidence-uploader` (#16 allowlist & `<=10 MB` limit), `async-export-banner` (#20 `5/min`), and `State Patterns` #7 (`HTTP 429` `Retry-After` countdown banner). | Covered |

---

## 2. Specific Technical & UX Verification Checklist

| Prompt Verification Item | Architecture Specification | UX Spine Verification | Status |
|---|---|---|---|
| **Frontend Stack** | React 19.x + TypeScript 5.x + Inertia 3.x + Tailwind CSS 4.x (`@theme`) + reskinned `shadcn/ui`. | `DESIGN.md` frontmatter & §Brand & Style; `EXPERIENCE.md` §Foundation. | Verified |
| **Inertia Page Directory Structure** | `resources/js/pages/<module>/<Page>.tsx` across Registry, Inspections, Maintenance, Reporting, Tenancy, Audit. | `EXPERIENCE.md` §Information Architecture defines all 14 Inertia page components mapped to routes and FRs. | Verified |
| **`<PropertyMap>` Component** | Wraps MapLibre GL JS 5.x + OpenFreeMap vector tiles; displays `properties.geom` derived coordinates; graceful fallback if tiles fail. | `DESIGN.md` #15 (`property-map` tokens & pin specs) + `EXPERIENCE.md` #15 (interactive pin placement, browser GPS capture, and coordinate-table fallback on weak mobile signal). | Verified |
| **Spatie DTO Type Contracts** | `spatie/laravel-data` 4.x + `spatie/typescript-transformer` 2.x → `resources/js/types/generated`. | `EXPERIENCE.md` §Foundation. | Verified |
| **Tiered Rate Limits (`AD-22`)** | Auth `5/min`, Evidence `30/min`, Report export `5/min` per org, API `120/min` → `HTTP 429` + `Retry-After`. | `EXPERIENCE.md` `State Patterns` #7 reads `Retry-After`, disables submit button for `[N]` seconds, and displays `.cc-status--warning` banner. | Verified |
| **Private R2 Signed URLs (`AD-13`)** | 10-minute TTL signed URLs generated via `EvidenceService` after tenant & `client_scope` verification. | `DESIGN.md` #17, #20 & `EXPERIENCE.md` #17 (`evidence-gallery` with automatic partial-reload refresh on `403` expiry), #20 (`async-export-banner`). | Verified |
| **Client-Side Image Compression** | Max `1920px` dimension, WebP/JPEG prior to upload for Nigerian mobile data connections. | `DESIGN.md` #16 & `EXPERIENCE.md` #16 (`evidence-uploader` canvas downscaling to `<=1920px` WebP/JPEG at `0.82` quality) + §Responsive & Platform. | Verified |
| **Async Queued Report Exports (`AD-14`, `AD-15`)** | `ReportExportJob` (`TenantAware`, `ShouldBeUnique`, PDF `concurrency = 1`) writing PDF & CSV to R2. | `DESIGN.md` #20 & `EXPERIENCE.md` #20 (`async-export-banner` with `Queued / Compiling`, `Ready`, and `Failed` states + Inertia `usePoll(3000)`). | Verified |

---

## 3. Surfaced Assumptions & Minor Alignment Notes

No qualitative ideas from `ARCHITECTURE-SPINE.md` were dropped. Three items are surfaced for awareness during implementation / story breakdown:

1. **`[NOTE FOR UX / DEV]` Inertia Page Directory Casing (`ARCHITECTURE-SPINE.md` lines 227 & 336 vs. `EXPERIENCE.md` line 39)**:
   - `ARCHITECTURE-SPINE.md` writes the placeholder pattern as `resources/js/pages/<module>/<Page>.tsx` (while naming domain modules in PascalCase: `Registry`, `Inspections`, `Maintenance`, `Reporting`, `Tenancy`, `Audit`), and `EXPERIENCE.md` lists the concrete Inertia component paths with PascalCase module folders (`Registry/PropertiesIndex.tsx`, `Inspections/InspectionRunner.tsx`, etc. — standard Laravel/Inertia convention matching `Inertia::render('Registry/PropertiesIndex')`).
   - **Recommendation**: Standardize on PascalCase folder names (`resources/js/pages/Registry/...`) in `vite.config.ts` / `app.tsx` page resolver on Linux/Docker so case-sensitive filesystems match `EXPERIENCE.md` and `app/Http/Controllers/Web/<Module>` 1:1.
2. **Upstream `[ASSUMPTION]` in `AD-4` (Platform Admin Support Actions)**:
   - `ARCHITECTURE-SPINE.md` `AD-4` notes: *"`[ASSUMPTION: Platform admin support actions occur via an audited membership in the target organization, never via runtime RLS bypass.]`"*
   - `EXPERIENCE.md` honors this directly: `Platform admin` operates inside the standard `app-shell-header` Organisation & Client Account context (`Tenancy/MembershipsIndex.tsx` and `Audit/AuditEventsIndex.tsx`) with zero "bypass RLS" superuser toggle in the UI.
3. **Upstream `[ASSUMPTION]` in `AD-5` (Denormalized `client_account_id` + `ClientScope`)**:
   - `EXPERIENCE.md` relies on `AD-5` for the global `app-shell-header` Client Account Scope Switcher (`dropdown-editorial`), ensuring every table, map, and timeline view is cleanly filtered by `client_account_id`.
