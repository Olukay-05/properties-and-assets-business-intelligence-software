# Data Protection, Privacy, and Retention (`DATA-PROTECTION.md`)

**Status:** Active Security & Privacy Foundation  
**Last Updated:** 2026-11-05  
**Scope:** Cloud Crafters Property & Asset Intelligence (Phase 1 MVP & Paid Pilot)  
**Primary References:** `docs/development-plan-review.md` (§11), `prd.md` (`FR-2`, `FR-21`, `FR-22`, `FR-23`, `§6.1`, `§6.2`, `§10`), `ARCHITECTURE-SPINE.md` (`AD-8`, `AD-13`, `AD-14`, `AD-18`, `AD-20`), `epics.md` (Stories 5.5, 5.6, 6.3, 6.6), `_bmad-output/arch-audit/flag-registry.md` (`F-104`, `F-206`)

---

## 1. Purpose, Scope & Legal Disclaimer

This document defines how the Cloud Crafters Property & Asset Intelligence platform collects, processes, isolates, retains, exports, and protects personal and operational information in alignment with the **Nigeria Data Protection Act 2023 (NDPA)**.

> **Important Accuracy & Legal Review Notice:**
> - This document translates product and architectural requirements into engineering and operational controls. It does **not** constitute formal legal advice or a certification of legal compliance.
> - No retention periods, legal bases, or contractual terms have been invented here. Where a legal or business decision remains open (such as concrete retention durations in `PRD Open Question #7` or NDPC registration classification in `PRD Open Question #4`), it is explicitly marked **Outstanding — Requires Legal / Product Owner Confirmation**.

---

## 2. Controller and Processor Responsibilities (`NDPA 2023`)

### 2.1 Role Allocation (`docs/development-plan-review.md` §11.1, `prd.md` §6.1)
- **Data Controller (The Customer / FM Organisation):** Each paying facilities-management (FM) organisation using the platform determines *why* and *which* client properties, assets, staff, contractors, and client contact details are entered into the platform. The FM Organisation is the **Data Controller** (or primary processor acting on instructions from its commercial property-owner clients).
- **Data Processor (The Platform / Cloud Crafters):** The platform processes and stores personal and operational data on behalf of the FM Organisation strictly to provide the agreed facilities-management workflows, evidence storage, and reporting services.
- **Customer Data Ownership:** Each FM Organisation retains ownership of the operational records, property/asset registers, inspection findings, work orders, photos, and reports created within its tenant workspace.

### 2.2 Data Processing Agreement (DPA) Requirement
- **Requirement (`NFR-6`, `Story 6.6`):** Before any real customer or pilot data is entered into the platform, a written **Data Processing Agreement (DPA)** (or pilot data-processing addendum) must be signed with the customer.
- **Status:** **Outstanding (`Flag F-104` / `Story 6.6` — Requires Legal Review).**

---

## 3. Categories of Information Collected & Purpose of Processing

The platform processes two primary categories of data: **Operational Business Data** and **Personal Data** (kept to the strict minimum needed for accountability and operational contact).

| Category | Specific Data Elements | Data Subjects (if Personal Data) | Purpose of Processing | Storage Location |
| --- | --- | --- | --- | --- |
| **1. Technician, Supervisor & Staff Information** | Full name, work email address, optional phone number, password hash (or 2FA/passkey metadata), organisation membership, role(s), `client_scope`, and timestamps of inspections/work orders/uploads (`users`, `memberships`). | FM provider employees & staff | Authentication (`FR-19`), role-based access control (`FR-20`), work-order assignment (`FR-13`), asset custody accountability (`FR-6`), and audit trail provenance (`FR-21`, `FR-22`). | PostgreSQL (`users`, `memberships`) |
| **2. External Contractor Information** | Contractor/company name, trade/specialty, contact email/phone, status, and assigned work orders (`contractors`). | Individual contractors or contractor contact persons | Assigning and tracking external maintenance work (`FR-13`–`FR-15`). | PostgreSQL (`contractors`) |
| **3. Customer (`Client Account`) Contact Details** | Client company name, primary contact person's name, contact email, contact phone number, business address, active/inactive status, and internal operational notes (`client_accounts`). | Commercial property owner representatives / client FM leads | Identifying which client owns a portfolio of properties (`FR-1`) and scoping access and monthly reports (`FR-8`, `FR-17`). | PostgreSQL (`client_accounts`) |
| **4. Property, Zone & Asset Records** | Site name, address text, site code, optional postcode string + validation outcome (`postcode_value`, `postcode_status`), `site_zones`, and `assets` (tag, category, make/model/serial, condition, status) (`properties`, `site_zones`, `assets`). | Business/operational data (linked to a Custodian in `asset_custody`) | Maintaining an accurate register of managed sites and physical assets (`FR-2`–`FR-8`). | PostgreSQL (`properties`, `site_zones`, `assets`, `asset_custody`) |
| **5. Inspection, Maintenance & Audit Records** | Checklist responses, asset condition ratings, fault descriptions, work-order progress notes, status transitions, and `audit_events` (`actor_id`, action, target, timestamp, IP/request metadata). | Linked to acting staff/technician (`user_id`) | Documenting maintenance execution, enforcing immutability of approved records, and providing compliance auditability (`FR-9`–`FR-16`, `FR-22`). | PostgreSQL (`inspections`, `work_orders`, `work_order_events`, `audit_events`) |
| **6. Photographs, Documents & Report Exports** | Site/asset condition photos, work-order completion photos, PDF attachments, and compiled client report exports (PDF/CSV), along with provenance (`uploaded_by`, `uploaded_at`, owning entity) (`attachments`). | Operational media (may incidentally capture staff on site; linked to `uploaded_by` user) | Providing verifiable visual proof of inspections and completed maintenance (`FR-11`, `FR-15`, `FR-18`, `FR-21`). | Private S3-compatible bucket (RustFS locally; Cloudflare R2 in staging/prod) + metadata in PostgreSQL (`attachments`) |
| **7. Location Information** | Canonical property point (`properties.geom`), append-only site coordinate history (`locations`: accuracy, source, `captured_by`, `captured_at`), and optional point-in-time coordinates on uploaded evidence (`attachments`). | Property coordinates (business data); optional timestamped capture coordinates linked to the uploading user (`captured_by` / `uploaded_by`) | Locating client sites on the map (`FR-3`, `FR-7`) and recording optional spatial provenance on field evidence (`FR-15`, `FR-21`). | PostgreSQL + PostGIS (`properties.geom`, `locations`, `attachments`) |

---

## 4. Data Minimisation Rules (`prd.md` §6.1, `mvp-scope.md`)

To minimize privacy and regulatory risk, developers and AI coding agents must enforce these hard boundaries:

1. **No Identity Documents or Biometrics:** Never collect, upload, or store national ID numbers (NIN), BVN, passports, driver's licences, or biometric data.
2. **No Residential Tenant / Occupant Personal Data:** The platform is a B2B facilities-management tool, not a residential lease/rent/tenant portal (`prd.md` §2.2 & §7). Never create tables or fields for residential occupants, leases, rent payments, or visitor access logs.
3. **No Continuous Background Location Tracking:** Location is captured **only** when a user explicitly sets a Property's coordinates (`FR-3`) or attaches a photo/file to an Inspection or Work Order (`FR-15`, `FR-21`). If device GPS is unavailable or denied, the platform records the work without coordinates and **never** blocks completion (`FR-15`).
4. **No NIPOST Dataset Cloning (`FR-2`, `docs/updates.md` §12):** When optional NIPOST postcode verification is enabled, store **only** the customer-entered postcode string and the verification status/outcome (`postcode_value`, `postcode_status`). Never store bulk copies of NIPOST directory data, and always display the mandatory legal disclaimer that a postcode identifies a postal routing reference only and never proves property title, ownership, or legal occupancy (`EXPERIENCE.md` §Voice and Tone).
5. **No Personal Data in Application Logs (`AD-18`, `SECURITY.md`):** Structured JSON application logs may record `org_id`, `user_id` (UUID), and `request_id`, but must never log names, emails, phone numbers, passwords, or tokens.

---

## 5. Access, Disclosure, and Isolation Rules

- **Strict Multi-Tenant Isolation (`AD-3`, `AD-4`):** Data belonging to one FM Organisation is logically isolated from all other Organisations via application `TenantScope` and PostgreSQL `FORCE ROW LEVEL SECURITY` (`docs/security/RLS-POLICIES.md`), plus tenant-namespaced storage keys in Cloudflare R2.
- **Intra-Tenant Client Scoping (`AD-5`):** Within an FM Organisation, staff and contractors whose `memberships.client_scope` is restricted can only access records and attachments belonging to their permitted `client_account_id` list.
- **Private Media & Short-Lived Signed URLs (`AD-13`):** All photographs, uploaded documents, and exported reports are stored in private buckets and disclosed only via **10-minute pre-signed URLs** generated after verifying the requester's `Organisation` and `client_scope`.
- **Platform Admin Access (`AD-4`):** Platform administrators do not bypass RLS at runtime; any support action requires an explicit, audited membership in the target Organisation.

---

## 6. Data Exports & Portability (`FR-18`, `AD-15`, `AD-20`)

- **Client Report Exports (`FR-17`, `FR-18`):** Authorised users (`Organisation owner`, `Operations manager`) can compile and export client-scoped reports in **PDF** and **CSV** formats. Every export execution is rate-limited (`5/min` per Organisation), runs inside a `TenantAware` background job, and writes an atomic `report.exported` entry to `audit_events` (`Story 4.3`).
- **Customer Offboarding & Data Portability (`prd.md` §6.2, `docs/updates.md` §9.1):** All customer data is stored in standard PostgreSQL/PostGIS tables and standard S3-compatible object keys (`organisations/{org_id}/...`), allowing a customer's complete register, history, and evidence files to be exported or deleted upon offboarding without vendor lock-in.

---

## 7. Data Retention, Deletion, and Append-Only Audit Limitations

### 7.1 Append-Only Operational & Audit Integrity vs. NDPA Deletion (`AD-8`)
- **Decision (`AD-8`):** During normal operation, `locations`, `asset_custody`, `work_order_events`, and `audit_events` are strictly append-only (the application runtime database role `pai_app` has no `UPDATE` or `DELETE` privileges on these tables), and approved inspections (`approved_at != null`) and completed work-order attachments cannot be deleted by end users (`AD-10`, `AD-11`).
- **NDPA Pruning & Deletion Mechanism (`AD-8`, `Story 5.5`, `Story 6.3`):** When data must be deleted or anonymized to satisfy configured retention limits, customer offboarding, or a verified NDPA Data Subject Request, deletion executes exclusively through a dedicated, audited, `TenantAware` administrative maintenance job/script running under an elevated maintenance database role, and records the pruning/deletion event in `audit_events`.

### 7.2 Retention Periods (`PRD Open Question #7` — `Flag F-206`)
- **Decision (Approved Engineering Default 2026-11-05; Legal Sign-Off Pending `PRD OQ-7` / `Flag F-206`):**
  - **Configurable Engineering Default:** Set `NDPA_RETENTION_DAYS=2555` (7 years, aligned with standard Nigerian commercial/contract limitation windows) as the default value in configuration (`config/compliance.php` / `.env.example`) so Stories 5.5 and 6.3 can be implemented and tested deterministically without hardcoded magic numbers.
  - **Final Legal Confirmation Before Live Customer Offboarding/Pruning (`Story 6.6`):** Before onboarding the first pilot customer, the Product Owner and legal counsel will confirm the final contractual retention schedule in the customer DPA for:
    1. Operational records and evidence attachments (`attachments`, `inspections`, `work_orders`),
    2. Compliance audit logs (`audit_events`),
    3. Temporary exported report files (`PDF`/`CSV` in R2), and
    4. Backup archives (`pg_dump` in R2 and Neon PITR window).

### 7.3 Backups and Retention Limitations (`AD-20`, `Story 6.3`)
- **Decision:** Production uses Neon Point-in-Time Recovery (PITR) plus nightly portable `pg_dump` exports to an isolated Cloudflare R2 backup bucket.
- **Limitation to Disclose in Customer DPA:** When a record or user is deleted or anonymized in the live production database, historical copies in encrypted PITR windows and nightly `pg_dump` archives remain until those backup snapshots naturally expire and rotate out according to the backup retention window (and would need to be re-applied if a historical backup is ever restored after a disaster).

---

## 8. Data Subject Access, Rectification, and Deletion Requests (DSRs)

- **Requirement (`FR-23`, `prd.md` §6.1):** Support access, rectification, and deletion of stored personal data (such as staff, technician, contractor, or client contact details) in coordination with the customer (Data Controller).
- **Decision (`Story 5.5`, `Story 5.6`):**
  - Because the FM Organisation is the Data Controller, data-subject requests from a technician or client contact are authorised and executed by the `Organisation owner` (or `Platform admin` acting on the Controller's written instruction).
  - **Self-Service & Admin Rectification:** Users can update their own profile name/email (`routes/settings.php`), and Organisation Owners/Operations Managers can update `memberships`, `contractors`, and `client_accounts`.
  - **DSR Logging & Execution Workflow (`Story 5.5` & `Story 5.6`):** The `Audit & NDPA compliance` surface (`/organisation/audit`, `AuditEventsIndex.tsx`) provides a workflow to log each Data Subject Request (`request_type`: `access`, `rectification`, or `deletion`, subject identifier, timestamps, and resolution notes), generating an atomic entry in `audit_events`.
  - **Preserving Historical Integrity on User Deletion:** Where a deleted user (`user_id`) is referenced as the historical actor on immutable client inspections, custody handovers, or work orders required for the customer's commercial proof of work, the deletion workflow must anonymize/redact the user's personal identifiers (`name`, `email`, `phone`) while preserving the non-personal operational audit and inspection integrity, subject to legal counsel confirmation of the DPA terms.
- **Status:** Profile update exists in starter kit (`ProfileController`); NDPA DSR service and UI panel are **Outstanding** (**Story 5.5**, **Story 5.6**).

---

## 9. Third-Party Sub-Processors & Cross-Border Data Transfers

### 9.1 Sub-Processors Inventory (Phase 1 Architecture)

| Sub-Processor | Service Provided | Data Processed | Hosting Location / Region | Status |
| --- | --- | --- | --- | --- |
| **Hetzner Online GmbH** | Application VPS hosting (web, API, queue worker, scheduler) | All data in transit and during request/job processing | **Germany (Nuremberg / Falkenstein, EU)** (`D7`) | Architectural Decision (Provisioning Outstanding) |
| **Neon, Inc.** | Managed PostgreSQL 17 + PostGIS database & PITR backups | All structured database records (`organisations`, `users`, `properties`, `assets`, `inspections`, `work_orders`, `audit_events`) | **EU Central (Frankfurt, Germany — AWS `eu-central-1`)** (`D1`, `D7`) | Architectural Decision (Spike & Provisioning Outstanding) |
| **Cloudflare, Inc. (R2)** | S3-compatible private object storage for evidence, exports, and nightly `pg_dump` backups | Uploaded photos/PDFs, compiled PDF/CSV reports, and encrypted/portable DB dumps | **Global / EU region hint (`D8`)** | Architectural Decision (Provisioning Outstanding) |
| **Resend (or configured SMTP provider)** | Transactional email delivery | User email addresses, names, and password-reset / notification emails | Cloud (Provider region to confirm at setup) (`docs/development-plan-review.md` §8) | Architectural Decision (Configuration Outstanding) |
| **Sentry (Functional Software, Inc.)** | Application error reporting & performance monitoring | Sanitized exception traces, `org_id`, `user_id`, `request_id` (PII stripped per `AD-18` & `Story 6.4`) | EU or US ingest region (prefer EU data residency at project creation) | Architectural Decision (`Story 6.4` Outstanding) |
| **OpenFreeMap** | Client-side vector map tiles rendered by `<PropertyMap>` | Browser IP and map viewport tile requests only (no customer PII or property names sent to tile server) | Public CDN (`D5`) | Architectural Decision (`Story 2.4` Outstanding) |

### 9.2 Cross-Border Data Transfer Considerations (`NDPA 2023` Part VI)
- **Architectural Fact (`D7`, `prd.md` §6.2):** Primary application compute (Hetzner) and database storage (Neon) are hosted in **Germany (European Union)** because Neon has no African region and co-locating app and DB in Frankfurt/Germany provides the lowest achievable latency (~110–150 ms RTT from Nigeria).
- **Portability Safeguard (`AD-20`, `docs/updates.md` §9.1):** The platform uses standard PostgreSQL 17 + PostGIS and standard S3 APIs so the entire stack can be migrated to a Nigerian cloud/data-centre host without application code changes if required by a regulated enterprise customer.
- **Compliance Requirement (`Flag F-104`, `Story 6.6`):** Under the NDPA 2023, transferring personal data of Nigerian data subjects to the European Union requires documenting an appropriate cross-border transfer basis (such as adequacy/comparable data protection laws in the EU and standard data-protection clauses in the customer DPA) and disclosing the hosting location transparently to the customer.

---

## 10. Security Incident and Breach Handling (`72-Hour Clock`)

- **Requirement (`FR-23`, `prd.md` §6.1, `docs/development-plan-review.md` §11.6):** Under the NDPA 2023, a Data Controller must notify the Nigeria Data Protection Commission (NDPC) within **72 hours** of becoming aware of a personal data breach that poses a risk to data subjects. As the **Data Processor**, the platform must detect, log, and notify the affected customer (Controller) promptly upon discovering an incident so the customer can meet their 72-hour statutory window.
- **Planned Technical Controls (`Story 5.5`, `Story 5.6`, `Story 6.4`):**
  1. **Detection & Alerting (`Story 6.4`):** Sentry error tracking, uptime monitoring alerts, and append-only `audit_events` logs.
  2. **Breach Logging Workflow (`Story 5.5` & `Story 5.6`):** The NDPA compliance service and `AuditEventsIndex` panel include a dedicated workflow to record security incidents/breach notifications (discovery timestamp, affected Organisation/records, primary customer contact notified, notification timestamp, and containment actions), persisted atomically in `audit_events`.
  3. **Immediate Containment Actions:** Revoke compromised Sanctum tokens or sessions, suspend affected `memberships`, rotate compromised secrets (`ENV.md` §4.3), and preserve `audit_events` and server logs as forensic evidence.

---

## 11. Matters Requiring Legal or Data Protection Professional Review

Before onboarding the first paid pilot customer (`Story 6.6` / `Flag F-104` / `Flag F-206`), the following items must be reviewed and confirmed by qualified Nigerian legal counsel or a licensed Data Protection Compliance Organisation (DPCO):

1. **NDPC Registration Status (`PRD Open Question #4` / `Flag F-104`):** Confirm whether Cloud Crafters (as processor) or the pilot FM customer (as controller) qualifies as a *"Data Controller or Data Processor of Major Importance"* under NDPC guidance requiring formal registration and filing with the NDPC.
2. **Pilot Data Processing Agreement (DPA) & Privacy Notice (`Flag F-104`):** Draft and approve the customer DPA (including controller/processor obligations, sub-processor list in §9.1, EU cross-border transfer clauses, and the processor-to-controller breach notification SLA).
3. **National Cloud Policy Classification (`PRD Open Question #6` / `Flag F-104`):** Confirm the exact instrument and residency requirements under the National Cloud Policy for private commercial facilities-management data (and verify if any prospective public-sector FM client triggers sovereign data residency rules).
4. **Concrete Data Retention Schedule (`PRD Open Question #7` / `Flag F-206`):** Define the exact retention periods (in days/years) for operational records, photo evidence, `audit_events`, and backups, and confirm the legal rules for anonymizing vs. retaining staff names on historical inspection/maintenance records when a DSR deletion request is received.
5. **NIPOST Commercial & Data Retention Terms (`PRD Open Question #5`):** If server-side NIPOST postcode lookup (`NIPOST_ENABLED=true`) is ever turned on for a pilot, review the signed NIPOST licence terms governing storage and deletion of verification outcomes.

