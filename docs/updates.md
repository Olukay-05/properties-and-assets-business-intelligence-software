# Property & Asset Business Intelligence Platform — Consolidated Requirements

**Document status:** Final corrected copy (v2) — supersedes `original-task.md` and the two strategy documents.

> **Scope note (approved):** the reduced MVP scope is now authoritative in `docs/development-plan-review.md` (Revision 2) and `docs/knowledge-base/`. Where this document conflicts with those on **scope** (MCP, webhooks, desktop, offline, advanced geo, client portal, agent/integration tables), the plan and knowledge base prevail. This document remains the source for principles, problem analysis, pricing hypotheses, and NIPOST/compliance detail.

**Audience:** product/engineering leads and AI coding agents.
**Naming:** the product has no final name yet. It is referred to here as "the platform". All prior brand names ("Cloud Crafters", "Cloud Crafter") are placeholders and must not be used as the product name.

---

## Contents

1. Purpose and how this document corrects the source material
2. Fault log and corrections applied
3. Product vision and guiding principles
4. Target users (humans and agents)
5. Problem and jobs to be done
6. MVP scope and feature prioritisation
7. Agent-first architecture (new core requirement)
8. Delivery strategy: web, desktop and mobile
9. Data residency, portability and compliance
10. Data model
11. Technology stack (corrected)
12. NIPOST digital postcode integration
13. Documentation platform and product knowledge base
14. Security, multi-tenancy, privacy and auditability
15. Commercial model and pricing (reconciled hypothesis)
16. Validation strategy and go/no-go gates
17. Phased roadmap
18. Risk register
19. Open questions and decision log

---

## 1. Purpose and how this document corrects the source material

This document is the single corrected requirements baseline for building a **property and asset business-intelligence platform that both humans and AI agents can operate**. It was produced by reconciling three prior sources:

- `original-task.md` — the initial brief (authoritative on intent and on the original stack recommendation).
- `Cloud_Crafters_Property_Asset_Intelligence_Strategy.md` — a commercially oriented strategy document.
- `Property & Asset Intelligence MVP and Revenue Strategy.md` — a second, independently authored strategy document covering the same brief.

The two strategy documents agree on direction but contradict each other in several material places (pricing, pilot design, target-account size, database/auth platform, policy dates). They both predate the agent-readiness requirement, the desktop-app question, the local-data-residency question, and the day-one documentation platform. Those four "new" requirements are folded in here as first-class architecture requirements, not as later additions.

Every fact below that could not be independently verified is labelled **hypothesis**, **assumption**, or **to confirm**. Pricing and financial figures are planning hypotheses, not market data.

---

## 2. Fault log and corrections applied

| # | Fault found | Source | Correction applied |
| --- | --- | --- | --- |
| F1 | Application stack divergence: the brief and both strategy docs assumed Next.js, but the confirmed product direction is Laravel | `original-task.md` §6 and both strategy docs (Next.js) vs the confirmed stack (Laravel) | Confirmed on **Laravel + React + TypeScript** (the author's actual preference). Agent/desktop/docs/residency requirements are preserved in full. |
| F2 | Conflicting database & auth platform: Supabase vs Neon | `original-task.md`/doc 2 (Supabase) vs doc 1 (Neon) | Standardised on **standard PostgreSQL + PostGIS** with SQL migrations as the source of truth; **Neon** as the MVP host; **portable, host-agnostic authentication** and S3-compatible object storage so nothing is locked to one vendor. |
| F3 | Conflicting pricing tiers (≈4× spread on the starter plan) | doc 1 (₦85k starter) vs doc 2 (₦20k starter) | Replaced with one reconciled hypothesis baseline (§15), marked for pilot validation. |
| F4 | Conflicting pilot fee/duration (₦75k/90 days vs ₦350k–₦750k/6 weeks) | doc 2 vs doc 1 | Reconciled to a single scoped pilot design (§16). |
| F5 | Conflicting ideal-customer site count (5–30 vs 10–100 sites) | doc 1 vs doc 2 | Reconciled to 5–100 sites, sweet spot 10–50 (§4). |
| F6 | Conflicting policy date: "National Cloud Policy 2025" vs "National Digital Cloud Policy approved 17 Aug 2026" | `updates.md`/doc 1 vs doc 2 | Substantive obligations stated; exact instrument and date marked **to confirm with counsel** (§9). |
| F7 | "NDPA and GAID" typo | doc 2 §6 | Corrected to "NDPA 2023 and its implementation directive/guidance". |
| F8 | Inconsistent product naming ("Cloud Crafters" vs "Cloud Crafter") and a personal byline | doc 1, doc 2 | Neutral placeholder used throughout; naming deferred; byline removed (not a product requirement). |
| F9 | No agent-readiness (no API-first mandate, MCP, webhooks, or agent permission model) | both strategy docs | Added as core requirements (§7, §10, §14). |
| F10 | No desktop/PWA delivery path | both strategy docs | Added (§8). |
| F11 | No day-one documentation platform or product knowledge base | both strategy docs | Added as core requirements (§13). |
| F12 | No explicit data-residency/portability requirement (Neon → Nigerian PostgreSQL) | both strategy docs | Added (§9). |
| F13 | Divergent, non-comparable market-size figures treated inconsistently | doc 1 (US$27.76bn), doc 2 (US$2.5bn–US$14.35bn) | Unified conclusion: none is a usable SaaS TAM; use an account-based beachhead model (§4, §15). |
| F14 | Divergent competitor lists with no single canonical set | doc 1 vs doc 2 | Consolidated into one list (§4, §12). |

---

## 3. Product vision and guiding principles

**Vision.** A multi-tenant platform that gives organisations a single, reliable record of every managed property and physical asset — where it is, who is responsible for it, what condition it is in, what work is due, and what evidence proves the work was done — and makes that record usable by **humans through interfaces** and by **AI agents and other software through secure, standardised interfaces**, on the same underlying records, permissions and business rules.

**Positioning (to test, not final).** "Know where every site and asset is, prove every visit and every job, and report it by postcode" — sold as operational control and client-ready proof of work, **not** as a postcode directory.

### Non-negotiable principles

1. **Build the capability once.** Business rules live in one place and are shared by the web UI, desktop app, REST API, MCP tools and webhooks. No action may bypass the business-logic layer.
2. **Humans and agents are both first-class users.** An agent must be able to discover and call capabilities without imitating human mouse clicks, and must never bypass security controls.
3. **No single AI-provider lock-in.** Capabilities are exposed through standard APIs and MCP so different models/agents can be swapped without rebuilding.
4. **No single cloud-provider lock-in.** Standard PostgreSQL + PostGIS, portable object storage, provider-agnostic auth, configurable infrastructure and independent backups.
5. **Fallback-first.** The platform must be fully usable when the NIPOST postcode API is unavailable; the postcode is optional enrichment, never a hard dependency.
6. **Every claim is labelled.** Verified facts, hypotheses, and assumptions are never conflated; AI-authored output (including documentation) must reflect implemented and tested behaviour, not aspirations.

### Standing rules for AI coding agents (AGENTS.md)

These are architecture rules, not optional features. Copy them verbatim into the repository's `AGENTS.md` before development starts:

1. **Never put business logic exclusively in the UI.** Whether an action comes from a button, an API request, an MCP tool call or a webhook, it must use the same underlying business rules.
2. **Never put business logic directly in an Inertia controller.** Inertia controllers, the versioned REST API and MCP tools are thin adapters over the same Laravel service layer. A feature is not complete until its logic lives in the service layer, not in a controller or component.
3. **Never make the application dependent on one AI provider.** Expose capabilities through standard APIs and MCP so models/agents can be swapped without rebuilding.
4. **Never make the application dependent on one cloud provider.** Standard PostgreSQL, portable object storage, provider-agnostic auth, configurable infrastructure and independent backups; keep provider-specific services behind replaceable interfaces.
5. **Never ship documentation for unverified behaviour.** Docs, API references and MCP tool descriptions must reflect implemented and tested behaviour (§13.5).

---

## 4. Target users (humans and agents)

### 4.1 Beachhead segment (hypothesis, corrected)

Facilities-management and maintenance service providers that manage **multiple client sites** (reconciled range: **5–100 sites, sweet spot ~10–50**) in Lagos, Abuja and Port Harcourt. These firms' product is the service visit, and their clients demand proof of it.

### 4.2 Human personas

| Role | What they need | Adoption / buying signal |
| --- | --- | --- |
| Economic buyer (owner / MD / COO) | Contract retention, service quality, accountability, margin | A disputed visit, a new multi-site contract, or an expensive operational failure. |
| Operational champion (head of FM / operations manager) | One view across sites; open work, overdue inspections, unassigned assets, unresolved risk | Manually compiling weekly/monthly status or chasing site teams. |
| Day-to-day user (site manager / supervisor) | Fast lookup, checklists, assigning jobs, proving closure | Reconciling phone/WhatsApp/email/paper instructions. |
| Field user (technician / contractor) | Low-typing mobile workflow, photo evidence, poor-network tolerance | Missed jobs, repeat visits, disputed completion. |
| Client-side viewer (property owner / client FM lead) | Read-only status, maintenance history, SLA evidence | Wants auditable monthly reporting without full internal access. |

### 4.3 Agent personas (new)

Agents are not a separate user category with blanket access; they are **principal-bound service accounts** that act on behalf of an authorised human or organisation.

| Agent type | Typical capability | Authority model |
| --- | --- | --- |
| Internal operational agent | Monitor overdue maintenance, draft assignments, prepare reports, coordinate inspections | Scoped to a tenant; Execute only within authorised rules; high-impact actions require approval (§7.4). |
| External customer agent | Query the customer's own portfolio, read reports, raise requests | Scoped credentials + explicit customer authorisation + approval workflows. |
| Integrator / system agent | Push/pull records via REST API and webhooks | OAuth 2.1 / scoped API keys; no UI login. |

### 4.4 Competitor landscape (consolidated)

Local products (Porchplus, Ledge, Property360, Hutstack, EstateOps, RateMe CMMS/Watchtower, Gate Africa, CaretakerPro, OurProperty NG, Estate Vault, PropertyREM, VENCO/Vendr) are **rent- and estate-led and priced near zero**; the maintenance tools that fit facilities work (UpKeep, MaintainX, Fiix, Fogwing, etc.) are **foreign and priced per user in dollars**. The differentiated opening is a **multi-client FM work-order-to-evidence-to-report workflow priced in naira by sites/assets** — an unproven gap that discovery must validate, not a claimed moat. (Vendor claims are self-reported; treat them accordingly.)

---

## 5. Problem and jobs to be done

The strongest commercial problem is **not** "where is the asset". It is: **"can I prove, to a client or owner, that a site was visited, inspected and fixed?"** Location matters mostly as supporting evidence for that proof.

### 5.1 Core job to be done (corrected)

Connect, in one linked record: **site → asset → custodian → inspection → work order → completion evidence → client report.**

### 5.2 Problem treatment

| Problem | Commercial strength | MVP treatment |
| --- | --- | --- |
| Proof of visits, inspections, condition and history | Strongest — ties to client retention and payment | Core. Immutable inspection records + evidence + read-only client link. |
| Accurate site & asset register | High as the foundation | Core. Structured register with GPS, source and verification state. |
| Coordinate technicians/contractors | Strong but commoditised | Basic assignment, status, handoff and history. |
| Custodianship / responsibility | Medium; cheap to build | One field + timestamped handover history. |
| Precise location / postcode | Medium; supporting fact | Verification ladder, optional NIPOST validation (§12). |
| Portfolio & client reporting | Medium–high; buyer sees it directly | One exportable monthly report + dashboard. |
| Preventive maintenance schedules | Strong but complex | Phase 2 (MVP has recurring inspections only). |
| Underutilised/neglected assets | Weak until data exists | Defer; MVP flags overdue items only. |
| Legal ownership / title verification | Out of scope | Postcode does not prove title; never claim it (§12). |

---

## 6. MVP scope and feature prioritisation

The MVP is a **responsive web app, installable as a PWA, mobile-first for field work, with an API-first backend**. No native apps in the MVP.

### 6.1 Feature matrix (consolidated)

Effort key: **S** = days, **M** = 1–2 weeks, **L** = >2 weeks (lean team estimate, not measured).

| Feature | Priority | Effort | Acceptance test |
| --- | --- | --- | --- |
| Organisation / tenant setup, roles | Must | S | One FM company separates staff, client accounts and site access. |
| Client portfolio (client accounts) | Must | S | Sites belong to a client; scoping enforced. |
| Property / site registration | Must | S | Name, site code, address text, GPS pin, type, client, status, notes. |
| Map view + geospatial search | Must | M | Registered sites render; filter by client/region/status. |
| Postcode field + validation adapter | Must (data model) / API conditional | M | Postcode stored with validation state; API failure never blocks save. |
| Asset register + categories + status | Must | S | Tag, name, type, serial/model, site, condition, status, dates, notes. |
| Asset-to-site relationship + custodian | Must | S | Asset belongs to a site; custody changes are timestamped history. |
| Inspections + condition reporting | Must | M | 2–3 checklist templates; capture outcome, notes, photos, inspector. |
| Maintenance requests + work orders | Must | M | Request → assigned work order; priority, due date, status, closure. |
| Technician / contractor assignment | Must | S | Assign user or approved contractor; record handoff. |
| Activity history + audit trail | Must | S | Actor, action, timestamp and entity link on critical changes. |
| Dashboard + CSV/PDF report | Must | M | Open/overdue work, upcoming inspections, asset counts, period report. |
| Spreadsheet import + validation | Must | S | CSV template import with error report. |
| Read-only client link | Must | S | Client sees only its sites/reports (no database login). |
| Offline capture + sync queue | Should (pilot) | M | Drafts queue offline; sync without silently overwriting others' updates. |
| **REST API (versioned) + API keys/OAuth** | Must (architecture) | M | External software and agents call every capability through the API. |
| **MCP server (tools) + agent audit** | Phase 2 | M | Agents discover and call tools under the permission model (§7). |
| **Webhooks + event stream** | Phase 2 | M | Outbound events on request created / job overdue / inspection failed / asset out of service / job completed. |
| Recurring PM rules + template library | Phase 2 | L | Recurring schedules with exception/escalation. |
| Contractor portal + SLA scorecards | Phase 2 | M | External teams accept jobs against agreed SLAs. |
| WhatsApp notifications (approved provider) | Phase 2 | M | MVP uses click-to-share links instead. |
| AI-drafted report narrative / photo notes | Phase 2 | M | Not needed to prove demand. |
| Parts / procurement / inventory | Later | L | Only if pilot shows it blocks adoption. |
| IoT / telemetry / predictive maintenance | Later | L | Needs clean history + hardware partnerships. |
| Rent / lease / service-charge / access control | Out of scope | — | Avoid the crowded estate/rental category. |
| Title / ownership / identity verification | Out of scope | — | Postcode alone is not evidence of these facts. |

**Cut line if the build slips** (in order): offline queue → operations dashboard → Paystack billing (invoice pilots by hand). **Never cut** proof-of-visit, the monthly report, or the client link — those are why a buyer pays.

### 6.2 Non-negotiable MVP quality

- **Fast entry:** import, reusable categories/templates; no repeated long typing in the field.
- **Photo evidence:** client-side compression (~1,600 px), attach to the correct record, preserve who/when.
- **Permission boundaries:** staff see only assigned client portfolios; client users see only the agreed subset.
- **Network resilience:** autosave, visible sync state, no duplicate work orders on reconnect.
- **Exportability:** customers can export their own properties, assets, inspections and work-order history.
- **Operational support:** migration, training and first-month adoption support; an empty system is not success.

---

## 7. Agent-first architecture (new core requirement)

The platform is **API-first and agent-ready from day one**. The human interface and AI agents use the **same business-logic layer**; the interface is only one consumer of it.

### 7.1 Layered architecture

```
Human interface        Web · PWA · Desktop (Tauri, later) · Mobile web
AI agents              External agents · internal operational agents
Integration layer      REST API · MCP · Webhooks   ← the agent contract
Business logic layer   Properties · Assets · Inspections · Maintenance · Technicians · Reports
Data & services        PostgreSQL+PostGIS · Object storage · Background jobs · Events
```

### 7.2 Three integration surfaces

**A. REST API (versioned, typed).** Predictable endpoints for every capability — retrieve properties, register assets, list open work orders, assign a technician, create an inspection, generate a report. OpenAPI specification is the contract and is generated from the code (§13).

**B. MCP (Model Context Protocol).** The primary agent surface. An MCP server exposes tools derived from the same service layer. Initial tool set:

| Tool | What an agent can do |
| --- | --- |
| `search_properties` | Find properties for an authorised client |
| `get_property_details` | Retrieve property information |
| `list_assets` / `get_asset_history` | Retrieve assets / maintenance history |
| `list_open_work_orders` | Find outstanding jobs |
| `create_work_order` | Raise a maintenance job |
| `assign_technician` | Assign a job to a technician |
| `record_inspection` | Submit inspection results |
| `generate_client_report` | Prepare a client report |

**C. Webhooks + events.** Outbound notifications for: request created, inspection failed, work order overdue, asset out of service, job completed. An external agent may act on an event subject to its granted permissions.

### 7.3 Agent permission model (mandatory)

Agents never get unrestricted access. Enforce a permission tier per tool/action:

| Level | Meaning | Example |
| --- | --- | --- |
| Read | View information | Check outstanding jobs |
| Prepare | Draft an action | Prepare a maintenance report |
| Execute | Perform an authorised action | Assign a technician |
| Approval required | Prepare, then wait for human confirmation | Approve a high-cost repair |
| Restricted | Cannot perform the action | Delete a client's records |

Every agent action must record: **which agent, which authorising user/organisation, timestamp, action, result, and an audit-trail entry**. Use scoped credentials, tenant-level permissions, approval workflows and limits on high-impact operations. Per the MCP specification, maintain human oversight and clear visibility of tool use.

### 7.4 Principle

> Agents may **operate** the platform, but they must never **bypass** its business rules or security controls.

### 7.5 Phasing agent capability (not an afterthought)

- **Phase 1 (foundation):** multi-tenancy, domain workflows, **proper REST API**, audit logs and permissions, installable PWA. Business logic never lives only in the UI.
- **Phase 2 (agent-accessible):** MCP tools, search/retrieve/report, approval-based actions, webhooks.
- **Phase 3 (agent-driven ops):** agents monitor overdue work, draft/assign work orders within rules, coordinate inspections, produce summaries.
- **Phase 4 (delivery infra):** Tauri desktop, advanced offline, Nigerian cloud deployment if required, enterprise controls, multiple agent providers/models.

---

## 8. Delivery strategy: web, desktop and mobile

- **MVP:** one responsive web app, installable as a **PWA** (app icon, own window).
- **Desktop:** no separate app or separate business logic. When customers ask for a downloadable desktop app, package the existing frontend with **Tauri** (preferred over Electron for footprint). The desktop app connects to the same backend and API.
- **Mobile:** PWA/mobile-web first; native apps only if field adoption demands them.
- **Offline:** packaging as a desktop/PWA does **not** create offline capability by itself. Offline inspections, photos and sync are deliberately designed (§6), not implied.

---

## 9. Data residency, portability and compliance

**Correct position (both sources agree substantively):** Nigeria has **not** imposed a blanket rule that all commercial company data must be hosted in Nigeria. The National Cloud Policy (referenced as "2025" in one source and "approved 17 August 2026" in another — **exact instrument and date to confirm**) establishes data **classification and residency** requirements, with local hosting for sovereign data classified at Levels 3–4 and a focus on Federal Public Institutions. The **Nigeria Data Protection Act 2023 (NDPA)** governs transfers of personal data; cross-border transfer is permitted where required safeguards and transfer conditions are met.

**Therefore:** overseas hosting of ordinary customer data is not automatically unlawful, but the platform must not be dependent on it.

### 9.1 Portability requirements (mandatory from day one)

| Component | Requirement |
| --- | --- |
| Database | Standard PostgreSQL + PostGIS; schema in plain SQL migrations; export/restore via `pg_dump`/`pg_restore` to any PostgreSQL host. |
| Geographic data | Standard PostGIS-compatible data (SRID 4326), not vendor-proprietary. |
| Photos & documents | S3-compatible object storage; files + metadata independently exportable. |
| Application | Deployable to any PHP-FPM host or container; no vendor-only runtime features. |
| Authentication | Provider-agnostic identity; not bound to one host's identity system. |
| AI integrations | Provider-independent interfaces (MCP, standard APIs). |
| Backups | Independent copies restorable elsewhere. |

Moving the database does **not** automatically move photos, document storage, logs, backups or AI-generated data — each needs its own migration arrangement, and the location of AI processing, backups, logs and third-party services can matter as much as the main database for regulated clients.

### 9.2 Compliance baseline

- Map controller/processor roles and data flows; the customer is usually **controller**, the platform **processor** — sign a DPA with each.
- NDPA 2023: data minimisation, purpose limitation, retention rules, privacy notice, data-subject request process, and a **72-hour breach-notification clock** for the controller (platform must notify customers promptly enough to meet their deadline). Confirm registration category (controller/processor of major importance) and DPCO requirements with Nigerian counsel.
- NIPOST-derived data: retention/deletion follows the NIPOST contract **separately** from customer-owned operational records (§12).

---

## 10. Data model

One PostgreSQL database, shared schema, **`organisation_id` on every tenant row**, row-level security (RLS) as the isolation layer. The customer's *clients* (property owners the FM provider serves) are modelled as `client_accounts` **inside** the tenant — they are not platform tenants.

### 10.1 Core entities

| Entity | Essential fields | Purpose |
| --- | --- | --- |
| `organisations` | id, name, legal_name, plan_id, status, created_at | Tenant boundary. |
| `users` | id, name, email, phone, auth_user_id, status | Authenticated people. |
| `memberships` | org_id, user_id, role, client_scope, status | User↔organisation roles and scoping. |
| `client_accounts` | id, org_id, name, reference, contact, contract_start/end | The property owner/business client served by the FM provider. |
| `properties` (sites) | id, org_id, client_id, site_code, name, address_text, state, city, geom, location_source, postcode_value, postcode_status, status | Physical building/facility/campus; `geom` = PostGIS Point SRID 4326. |
| `locations` | property_id, geom, accuracy_m, source, captured_by, captured_at | History of coordinates; current one is referenced. |
| `site_zones` | site_id, name, type | Position inside a site (floor, room, plant room). |
| `assets` | id, org_id, asset_tag, category_id, name, make, model, serial_no, status, condition, installed_on, criticality | Physical maintainable asset, uniquely tagged per org. |
| `asset_locations` | *(deferred — Phase 3)* | Relocation history; MVP uses a simple current site/zone foreign key on `assets`. |
| `asset_custody` | asset_id, user_id/contractor_id, assigned_at, released_at, handover_note | Handover/responsibility history. |
| `inspection_templates` / `_items` | org_id, name, version, item_text, answer_type, required | Reusable checklists. |
| `inspection_runs` / `responses` | site_id, asset_id, template_version, inspector_id, started_at, completed_at, result, response_json, media_refs | Each inspection and its answers/evidence. |
| `maintenance_requests` | site_id, asset_id, requester_id, description, priority, status, created_at | Initial issue/service request. |
| `work_orders` | request_id, site_id, asset_id, assignee, priority, due_at, status, opened_at, closed_at, closure_code | Execution record; may reference a site without an asset. |
| `work_order_events` | work_order_id, actor_id, event_type, note, timestamp, media_refs | Immutable timeline. |
| `contractors` | org_id, name, trade, contacts, insurance_expiry, status | Approved external providers. |
| `postcode_verifications` | property_id, source, checked_at, result_code, request_id, permitted_payload_ref, retention_until | NIPOST validation record; stores **outcome**, not a copy of NIPOST data. |
| `client_share_links` | *(deferred — unless a pilot requires it)* | Read-only client portal; MVP uses exported reports shared by the FM provider. |
| `attachments` | entity_type, entity_id, storage_path, captured_at, captured_location | Photos/evidence. |
| `audit_events` | org_id, actor_id, entity_type, entity_id, action, old/new, timestamp, context | Append-only; protected from tenant tampering. |
| `plans` / `subscriptions` / `usage_records` | *(deferred — commercial launch)* | Billing; pilots are invoiced manually, Paystack billing is added at launch. |

### 10.2 Agent & integration entities (deferred — Phase 3)

These are **not** created in Phase 1 (see `docs/development-plan-review.md` §12). The MVP API is first-party only; Laravel Sanctum's built-in `personal_access_tokens` is the only token store needed now.

| Entity | Status |
| --- | --- |
| `api_clients` | Deferred — external integrators/agents arrive in Phase 3 (OAuth 2.1). |
| `mcp_tools` / `tool_invocations` | Deferred — MCP is Phase 3. |
| `approvals` | Deferred — supervisor sign-off is a field on work orders/inspections in Phase 1. |
| `webhook_subscriptions` / `webhook_events` | Deferred — webhooks are Phase 3. |

### 10.3 Relationship and geospatial rules

- One org → many clients, users, properties, assets, work orders, subscriptions. One client → many properties. One property → many assets.
- Custody uses dated `asset_custody` (one current accountable custodian unless shared responsibility is explicitly modelled). Asset relocation history (`asset_locations`) is deferred to Phase 3; the MVP uses a simple current site/zone reference on `assets`.
- Inspections and work orders link to a property and optionally an asset.
- Store GPS geometry (source, accuracy, verifier, timestamp) **separately** from the postcode value; the postcode never silently overrides customer GPS.
- GIST index on the geometry column; proximity search against the customer's own points only — never clone the national postcode database.
- Append-only audit on core inserts/updates/deletes; approved inspections are immutable (corrections create new versions linked to the original).

---

## 11. Technology stack (corrected)

This is the **confirmed stack** (the author's preferred direction). It supersedes the Next.js assumption in the brief and the two strategy documents; **all** agent/desktop/docs/residency requirements are preserved and remain framework-agnostic.

| Layer | Choice | Note |
| --- | --- | --- |
| Backend | **Laravel** | Single application: business logic, API, jobs and MCP all live here. |
| Frontend | **React + TypeScript** | One responsive web app; PWA-first. |
| Frontend integration | **Inertia.js** (initially) | Server-driven SPA bridge over Laravel; see §11.1 caution 2. |
| UI | **Tailwind CSS + shadcn/ui** | React component library on top of Tailwind. |
| Main database | **PostgreSQL** on **Neon** (initially) | Standard Postgres; plain SQL migrations; portable to any Nigerian PostgreSQL. |
| Geographic data | **PostGIS** (SRID 4326), GIST indexes | See §11.1 caution 1. |
| File storage | **Cloudflare R2** (initially) | S3-compatible; portable; photos resized client-side. |
| Background jobs | **Laravel Queues** | DB/Redis driver; not bound to one provider. |
| Maps | MapLibre GL JS + OpenFreeMap (low-cost initial; verify licensing) | MapLibre renders; tiles from OpenFreeMap; MapTiler not mandatory. |
| NIPOST | Server-side adapter + feature flag + queue + circuit breaker | API key never in the browser (§12). |
| Payments | Paystack (subscriptions + webhooks); bank-transfer fallback | Local card 1.5% + ₦100, capped ₦2,000; no auto-retry on failed subscriptions. |
| Application integration | **REST API** (versioned, OpenAPI) | The contract for integrators and agents. |
| AI agent connectivity | **Laravel MCP** (official package) | Exposes tools/resources to MCP clients; see §11.1 caution 4. |
| External events | **Webhooks** | Outbound event delivery (§7). |
| Authentication | **Laravel Sanctum** (first-party) + **OAuth 2.1 / Passport** (third-party agents/integrators) | See §11.1 caution 3. |
| Web access | React app (via Inertia) | — |
| Desktop | **Tauri** when justified | Same React frontend, no duplicate logic (§8). |
| Mobile | **PWA** initially | — |
| Offline capability | Local storage + sync queue | Deliberately designed (§6). |
| MVP hosting | Affordable cloud hosting (PHP-FPM / Forge / container) | Portable; see §9. |
| Future Nigerian hosting | Portable Laravel + PostgreSQL deployment | — |
| Source control | GitHub (monorepo) | §13. |
| Deployment | Automated CI/CD | — |

**Delivery principle:** the desktop app, REST API, MCP server and web UI all call the **same service layer**; there is exactly one implementation of each business rule.

### 11.1 Honest caveats (none are blockers — decide before agents start building)

1. **PostGIS is the one place Laravel is not first-class.** Core Eloquent has no native geometry type. Pick one maintained package early (`clickbar/laravel-magellan` or `mstaack/laravel-postgis`) and keep raw SQL (`ST_DWithin`, GiST) as an escape hatch. Standardise before Phase 2.
2. **Keep Inertia thin.** Inertia controllers return server-rendered props, not a JSON API — do not put business logic there. Rules live in a **domain/service layer**; Inertia controllers, the versioned REST API and MCP tools all call the same services.
3. **Split auth by audience.** Sanctum is for the platform's own web/PWA/desktop clients. Third-party agents/integrators needing scopes, client credentials or authorization-code + PKCE use OAuth 2.1 via **Passport**. Do not stretch Sanctum into a full OAuth provider.
4. **Confirm Laravel MCP's transport early.** Agents connect over HTTP/SSE (stdio is for local tools); long-lived SSE connections can hit PHP-FPM `max_execution_time`/keep-alive limits on some hosts. Verify the package's transport and hosting config in the Phase 1 spike.

---

## 12. NIPOST digital postcode integration

### 12.1 Confirmed public facts

- Launched **1 October 2026**; 11-character alphanumeric code in five segments (state 2 letters, LGA 2 digits, district 3 chars, area 2 letters, building 2 digits). Identifies a **building**, not its occupants or owner.
- Organisations integrate via a developer/organisation account, API key, and web/mobile SDKs or bulk access.
- The Acceptable Use Policy permits authorised commercial use **subject to agreements** but prohibits unauthorised resale, redistribution, commercial exploitation, and building a **substitute postcode service or derivative database**; it restricts retaining NDAPS data beyond the authorised period.
- The Terms state a postcode **does not establish ownership, title, occupancy, identity or legal status**, and that the service is provided as-available with no accuracy guarantee.

### 12.2 Not publicly established (do not plan around unpublished detail)

Commercial per-request/bulk pricing, caching/retention terms, whether the postcode value may be persisted in a customer's own register, multi-tenant vendor usage under one account, production-approval timelines, rate limits, SLA, and branding rights. **Treat any estimate of these as unverified.**

### 12.3 Product behaviour (fallback-first)

| Situation | Behaviour |
| --- | --- |
| New property | Always allow address text + site code + GPS pin; record source and verification state per field. |
| Authorised lookup available | Server-side call; show returned postcode + what was validated + when. |
| API down / rate limited | Save on address + GPS; queue optional re-check; show "not checked", never stale-as-current. |
| Building not found | Save unverified location; explain the official missing-building request route. |
| Field verification | Authorised staff confirm the pin matches the physical site (verifier, timestamp, optional photo) — separate from NIPOST validation. |
| Export / termination | Export customer-owned data; treat NIPOST-derived fields per the signed licence (no assumed redistribution/retention). |

**Verification ladder:** (1) Entered → (2) Format valid (offline) → (3) Assigned (free Level-1 lookup) → (4) Location matched (GPS within threshold of NIPOST point; threshold is a starting guess to test) → (5) Field confirmed (photo). A mismatch creates a review task; it never silently rewrites customer coordinates.

**Critical legal distinction:** never label anything "ownership/title/tenant/identity verified" because a postcode was returned. **Do not use NIPOST branding or "official"/"verified" language without written permission.**

---

## 13. Documentation platform and product knowledge base

The documentation platform is part of the product's **infrastructure from day one**, because agents — human developers and AI coding agents — build, maintain and operate the platform.

### 13.1 Two applications, one repository

| Application | Scope |
| --- | --- |
| **Main app** | The operational platform (Laravel backend + React frontend + API + MCP). |
| **Docs app** | Separate, independently deployable **Docusaurus** site reading Markdown/MDX from the repo. |

One monorepo, two separately built/deployed applications. Updating a user guide must not require redeploying the main app. Suggested layout (illustrative):

```
/
├── apps/
│   ├── web/            # Laravel backend + React frontend + API + service layer
│   └── docs/           # Docusaurus
├── docs-content/       # user-guides, administration, developer-guides, architecture, api, ai-agents
├── packages/           # shared-types, shared-contracts
├── api-spec/           # openapi.yaml
├── infrastructure/
├── AGENTS.md
└── README.md
```

### 13.2 Documentation audiences and access rules

| Type | Audience | Visibility |
| --- | --- | --- |
| User guides | Customers | Public / customer-accessible |
| Administrator guides | Customer admins | Customer-accessible |
| API reference | Integrators, developers | Public or authenticated |
| MCP tool reference | AI agents, developers | Public or authenticated |
| Architecture | Developers, coding agents | Private |
| Deployment/ops runbooks | Internal team | Private |
| Security procedures | Authorised personnel | Restricted |

Do not publish internal connection procedures, security configs or secrets to the public docs site.

### 13.3 Maintain documentation close to the implementation

| Doc | Maintenance |
| --- | --- |
| Customer usage guides | AI-authored Markdown, reviewed against the app |
| REST API reference | Generated from the OpenAPI spec |
| MCP tool reference | Generated from actual tool definitions |
| Database docs | Generated/updated from migrations and schema |
| Architecture docs | AI-maintained, reviewed on change |
| Changelog | Generated from approved releases |

**Three sources of truth:** the code (what it does), the tests (what is verified), and the docs (how it is understood/used). When they disagree, investigate — do not rewrite docs to match an assumption.

### 13.4 Product Knowledge Base (mandatory)

A controlled source of truth separate from customer-facing docs, containing: requirements, feature specs, business rules, data models, architecture decisions, API contracts, security/permission rules and user guides. **AI coding agents must consult it before implementing, and update it when approved decisions change.** This is the product's institutional memory so different agents work consistently across modules and time.

### 13.5 Documentation completion rule (for AI coding agents)

> Every new feature or change must include matching documentation updates (user guide, API reference, technical notes, architecture where applicable). Documentation must reflect **implemented and tested** behaviour, never assumed behaviour. Changes ship for review alongside the code.

---

## 14. Security, multi-tenancy, privacy and auditability

| Control | Minimum implementation |
| --- | --- |
| Tenant isolation | `organisation_id` on all tenant rows; PostgreSQL RLS on select/insert/update/delete; never rely on frontend filtering alone. |
| Roles | Platform admin; org owner; ops manager; site supervisor; technician; contractor; client read-only; **agent/service principals**. Enforce scope server-side. |
| Secrets | NIPOST, Paystack, map keys server-side only; restrict + rotate; never ship privileged keys to the browser. |
| Auditability | Append-only events for custody, location changes, approvals, work-order state, data exports, and **every agent action**; restrict deletion of audit evidence. |
| Data security | HTTPS, encrypted storage, secure backups, least privilege, MFA for privileged users, short-lived signed media URLs, monitoring + incident response. |
| Privacy | NDPA 2023: minimisation, purpose limitation, retention, privacy notice, DPA, data-subject requests (§9). |
| Agent security | Scoped credentials, tenant scoping, permission tiers, approval workflows, limits on high-impact ops, human oversight (§7). |
| Portability | Customer export + offboarding; NIPOST-derived data deletion follows the NIPOST contract separately. |

---

## 15. Commercial model and pricing (reconciled hypothesis)

The two strategy documents disagreed by ≈4× on pricing (doc 1: ₦85k–₦375k tiers; doc 2: ₦20k–₦150k tiers). **Every figure below is a hypothesis to be settled by paid pilots**, not a market fact. It is presented once, internally consistent, for planning only.

### 15.1 Recurring tiers (monthly, naira — hypothesis)

Price by **sites and assets**, never per technician seat (adding field staff must not raise the bill).

| Plan | Monthly | Limits | Includes |
| --- | --- | --- | --- |
| Starter | ₦45,000 | 10 sites · 300 assets · 5 office users | Register, map, custodians, basic inspections, work orders, dashboard, CSV export, monthly report. |
| Growth | ₦120,000 | 30 sites · 1,000 assets · 15 office users | + configurable templates, priority/assignment, client-ready report, import, client read-only links. |
| Portfolio | ₦280,000 | 80 sites · 3,000 assets · 30 office users | + recurring PM, advanced filters, portfolio analytics, priority support. |
| Enterprise | From ₦500,000 (quoted) | Custom | Custom onboarding/integrations, SLA, advanced roles, API, dedicated account management. |

### 15.2 One-time and service revenue (hypothesis)

| Stream | Price hypothesis | Basis |
| --- | --- | --- |
| Onboarding | ₦150k / ₦400k / ₦800k (by tier) | Setup, CSV cleanup, templates, training. |
| Asset-register digitisation | ₦1,500–₦3,000 per asset (or ~₦10k–₦15k per site) | Adjust by data condition, travel, tagging. |
| Field survey / physical tagging | Quoted (team-day + materials) | Separate from SaaS. |
| Custom integration / automation | ₦500k–₦3m+ one-off | Fixed-price after technical discovery. |
| Advanced reporting add-on | ₦30k–₦80k/month | Only after data is consistent. |

### 15.3 Economics notes (assumptions)

- SaaS subscription target gross margin once repeatable: **75–85%**; onboarding/migration **35–55%**; physical field survey **20–40%**; custom integration **35–55%**. (Ranges are planning assumptions.)
- Illustrative 90-day incremental cash budget: **₦5.4m–₦11.5m** (excluding founder salary, overhead, tax). Not a vendor quote.
- **Do not** use headline FM-services market sizes (US$2.5bn–US$27.76bn across sources) as a SaaS TAM. Use an **account-based beachhead model**: a working list of ~60 qualified accounts; 10–20% capture yields a small initial business (₦13m–₦27m ARR at blended ₦190k/mo) — additional verticals are needed for scale.

### 15.4 First customers without paid advertising

Named-account outreach (MD/COO/head of FM), a 45-minute workflow review (not a product demo), a paid register diagnostic, one live pilot site with weekly review, referral relationships, and client read-only links as the distribution loop. Price the operating value, not the postcode lookup.

---

## 16. Validation strategy and go/no-go gates

Validate before building more than a clickable prototype. The test that matters is **whether anyone pays**, not whether they say the idea is good.

### 16.1 Discovery

12–20 structured conversations across ≥8 organisations, speaking to the economic buyer **and** at least one operational/field user. Ask about the **last real event**, not the future; do not describe the product until late; record evidence separately from interpretation.

### 16.2 Paid pilot (reconciled)

| Element | Recommendation |
| --- | --- |
| Fee | **₦150,000–₦300,000**, scoped by data condition (reconciles the ₦75k and ₦350k–₦750k conflict); up to 50% credited against annual prepayment on conversion. |
| Duration | 6–8 weeks (baseline → live use → results). |
| Scope | 1–3 client sites, 100–300 assets, 1 ops manager, 2–4 supervisors, 5–15 technicians. |
| Baseline | Register completeness, requests/week, assign/close times, on-time inspections, proof-of-work coverage, report hours. |

**Pilot success targets (hypotheses, adjusted to each baseline):** ≥85% of pilot assets complete; ≥90% of sites with address + GPS; ≥75% of requests entered and assigned; ≥70% of closed work orders with agreed evidence; ≥80% of scheduled inspections on time; report time down ≥30%; buyer agrees to a recurring plan ≥₦150k/mo (or names a configuration that justifies a lower tier).

### 16.3 Go/no-go gates (reconciled)

| Gate | Evidence required | Decision |
| --- | --- | --- |
| G1 Discovery (≈week 3–4) | ≥12–15 qualified conversations; ≥5 name the core problem unprompted; ≥40 reachable firms identified | Pass → build MVP. Fail → re-run with multi-branch segment or pause. |
| G2 Willingness to pay (≈week 4–6) | ≥3 paid pilots committed; ≥2 written intents at ≥₦40k/mo | Pass → full build time. **<2 pilot payments after 12–15 conversations → pause/pivot.** |
| G3 Pilot usage (≈week 10) | ≥3 pilots live; ≥70% of jobs closed with evidence; weekly use per customer | Pass → open paid launch. |
| G4 Conversion (≈day 90) | ≥2 pilots convert; MRR ≥₦150k; no single customer >40% of revenue | Pass → continue. Marginal → extend 2 months. Fail → remain a services business with the toolkit as an internal asset. |
| NIPOST (parallel) | Written answers on storage, caching, pricing, production access | Unfavourable answers change the design (postcode stays optional), **not** the product. |

**Change direction if:** buyers want the register but not the postcode; property managers pay while FM firms do not; or buyers want digitisation only. **Abandon if:** <2 pilot payments after 12–15 qualified conversations, or two consecutive pilots churn for concept-level reasons.

---

## 17. Phased roadmap

~90 days, phases deliberately overlapping. NIPOST approval is a **parallel** dependency, never the critical path.

| Phase | Timing | Deliverables | Exit criteria |
| --- | --- | --- | --- |
| 1. Discovery & feasibility | Days 1–21 | 12–20 interviews; 60-account list; clickable prototype of inspection/report flow; NIPOST org account + integration form + written questions; PostGIS/offline/NIPOST-L1 spike; DPA + privacy checklist. | ≥5 name the problem unprompted; 3 pilot candidates; spike works on a mid-range Android phone. |
| 2. MVP build (API-first) | Days 15–56 | Tenant/org; client portfolio; site register; map + GPS; asset register; custody; inspections; requests/work orders; activity history; dashboard; CSV import; **versioned REST API**; audit; RLS tests. | End-to-end workflow on a test portfolio; tenant-isolation tests pass; imports reconcile; API is the same service layer as the UI. |
| 3. Paid pilot onboarding | Days 50–77 | 3–5 paid pilots; digitisation; baseline + metrics; weekly review; **MCP tools + approval workflow + webhooks**; fix high-impact bugs; NIPOST only if authorised. | ≥3 pilots active; ≥60–70% adoption by week 3; ≥1 customer asks to continue at list price. |
| 4. Commercial launch | Days 70–90 | Paystack billing; pricing page; onboarding playbook; case study; referral asks; pipeline; Docusaurus docs + knowledge base live; agent docs (MCP tool reference). | ≥2 pilots converted; 5 new qualified proposals; break-even path stated (~10 customers). |

**Sequencing rules:** book discovery calls in week 1; start full build only after ≥3 buyers agree to a pilot in principle; contract field agents before pilots; hold go/change/stop reviews on day 21 and day 77.

---

## 18. Risk register

Likelihood/impact are judgement. Largest risks are commercial, not technical.

| Risk | L / I | Mitigation | Early warning |
| --- | --- | --- | --- |
| Too few FM/multi-site firms to support 50–80 customers | Med / High | Count the pool in discovery; add multi-branch segment; keep services revenue. | <40 reachable firms after 3 weeks |
| Buyers won't pay more than ₦20k/month | Med / High | Test price every call; sell digitisation as paid entry; fall back to project work. | Price objections below ₦20k |
| NIPOST commercial terms make levels 2+ uneconomic | Med / Med | Build on Level 1 + GPS; make enrichment optional/pass-through. | Published credit prices exceed customer value |
| NIPOST restricts storage/caching more than assumed | Med / Med | Store only the customer's own postcode + verification result; get written answers. | Written reply restricts persistence |
| NIPOST outage / rate limits | Med / Low | Fallback-first, queue + retries, offline format check, circuit breaker. | Rising error rates |
| Rival adds asset/field-service features | Med / High | Move fast on a narrow segment; switching cost via verified history + client links. | Rival launch in segment |
| Postcode accuracy gaps | High / Med | Never override customer GPS; verification ladder + field confirmation. | High mismatch rate in pilots |
| Data-protection non-compliance | Med / High | Confirm registration duty; sign DPAs; minimise personal data; audit logs. | Security questionnaires you can't answer |
| Hosting outside Nigeria draws objections | Med / Med | Standard Postgres, documented migration path, clear disclosure (§9). | Enterprise lead requires Nigerian hosting |
| Technicians don't adopt | Med / High | Mobile-first, offline queue, free technician seats, supervisor-led rollout. | <60% of jobs closed in-app by week 3 |
| **Agent misuse / over-authorisation** | Med / High | Permission tiers, scoped credentials, approvals, limits, full audit (§7). | Unexpected bulk mutations by an agent |
| Founder bandwidth | High / High | Fixed weekly hours; contract field agents; cap pilots at 5; stop rule. | Slipping weekly deliverables |
| Naira volatility raises imported costs | Med / Low–Med | Annual pricing where possible; quarterly review; cost buffer. | FX moves >10% |
| Payment failures / churn | Med / Med | Bank-transfer + invoicing; Paystack retries. | Failed charges >10% |
| Postcode brand misuse | Low / High | Never use NIPOST branding/"official" without written permission. | Marketing copy review finds claims |

---

## 19. Open questions and decision log

### 19.1 Decisions made in this correction

| Decision | Rationale |
| --- | --- |
| Laravel + React + TypeScript (not Next.js) | The author's confirmed preference. The brief and both strategy docs assumed Next.js; that assumption is superseded. Agent-readiness (API-first, MCP via the official Laravel MCP package, webhooks) is fully supported. |
| Standard PostgreSQL + PostGIS, Neon MVP host, portable auth/storage | Resolves the Supabase-vs-Neon conflict; portability (§9) takes precedence over any single vendor. |
| Single reconciled pricing baseline | The two sources' ~4× spread was incoherent; one hypothesis baseline replaces both, pending pilots. |
| Neutral product name | Naming deferred per instruction; brand placeholders removed. |

### 19.2 Open questions to resolve (do not block the build)

1. **NIPOST commercial terms** — pricing, caching/retention, multi-tenant vendor use, production-approval timeline. *(Action: written request on day 1.)*
2. **National Cloud Policy** — exact instrument name and effective date (2025 vs 17 Aug 2026); confirm localisation scope for private commercial data with counsel.
3. **NDPA registration category** — whether the platform is a controller/processor of "major importance"; DPCO requirements.
4. **Pricing truth** — the reconciled tiers (§15) must be validated by paid pilots; do not treat them as final.
5. **Reachable FM pool size** — no credible count exists; discovery must count it directly.
6. **Auth approach** — select a portable identity provider that does not lock identity to the database host (evaluate before Phase 2 build).

---

*End of consolidated requirements. This document is the corrected baseline; where it conflicts with any prior document, this document prevails.*
