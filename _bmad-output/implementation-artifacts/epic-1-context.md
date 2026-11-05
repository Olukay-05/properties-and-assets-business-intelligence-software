# Epic 1 Context: Platform Foundations & Design System

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Establish the foundation every later epic builds on: a Laravel + Inertia + React application where a team member signs in, is placed inside their organisation's isolated workspace with role-scoped abilities, and sees a consistent editorial application shell on desktop and mobile. This epic also delivers the four cross-cutting services every workflow consumes — tenant context with database-enforced isolation, the private evidence store with signed URLs, the append-only audit trail, and one structured error envelope — so no later story has to invent security, evidence, or error handling on its own.

Note: the Story 1.1 starter-kit scaffold already exists in the repository and the architecture spine is final, but its fine-grained role matrix and several schema decisions were explicitly deferred to this epic. Treat the open points below as decisions to make here, not settled inputs.

## Stories

- Story 1.1: Greenfield scaffold — typed contract tooling and provider-agnostic object storage
- Story 1.2: Tenant context and RLS isolation infrastructure
- Story 1.3: Authentication with Sanctum and membership resolution
- Story 1.4: Role-ability policy foundation
- Story 1.5: Design tokens in Tailwind theme and base typography
- Story 1.6: Core component library I — layout and content primitives
- Story 1.7: Core component library II — interactive and field primitives
- Story 1.8: Application shell — desktop header, mobile navigation, quick-task bar
- Story 1.9: Operational states and editorial copy system
- Story 1.10: Evidence service and signed-URL gateway
- Story 1.11: Audit service and structured error envelope

## Requirements & Constraints

**Tenancy and isolation (non-negotiable from here on):**
- The tenant comes only from the authenticated membership, never from request input of any kind. Forged `organisation_id` values are ignored; a creating hook stamps the real value and aborts on conflict.
- Every tenant table enforces row-level security (enable plus force) and the runtime database role is a non-owner without bypass. Raw SQL returns zero rows for another tenant, and zero rows when no tenant is bound.
- Isolation must hold at HTTP, service layer, raw SQL, queued jobs, and signed URLs — never by relying on a single layer.
- Client scope: a membership is either unrestricted for the organisation or limited to a set of client accounts. Out-of-tenant or out-of-scope access resolves as not-found, never forbidden, to prevent existence probing; forbidden is for unauthorised action on a visible in-scope record.

**Authentication and roles:**
- First-party only: Sanctum SPA cookie session. No external OAuth, no API client registry, no self-service tenant sign-up.
- Users exist only inside an organisation through a membership. Public uninvited self-registration must be disabled; the staff bootstrap flow (admin-created user with password reset, or token-gated invitation) is undecided and belongs to Story 1.3.
- Suspended or inactive memberships cannot sign in or keep an active session.
- Six roles (Platform admin, Organisation owner, Operations manager, Site supervisor, Technician, Contractor); multi-role memberships inherit the union. Platform admin has no runtime isolation bypass and works through an audited membership in the target organisation.
- Failed sign-in never reveals whether the email exists or the password was wrong, and is audited.
- Rate limits: sign-in 5/min per IP plus email, uploads 30/min per user, API 120/min per user, each with a retry-after header.

**Evidence:** uploads verified by file content (magic bytes) against a strict allowlist (jpeg, png, webp, pdf — no SVG or HTML), 10 MB cap, server-generated storage keys (never user filenames), private bucket. Reads only through 10-minute signed URLs issued after tenant and client-scope verification; non-images download as attachments.

**Audit and errors:** sensitive operations (authentication, exports, role/scope changes, deletions, data-protection requests) are written atomically inside the originating transaction to an append-only table the runtime role cannot update or delete. All errors use one envelope with permanent snake_case codes; raw database errors are sanitised and sent to error tracking, never shown.

**Data protection:** data minimisation is hard — no identity documents or biometrics, no residential tenant data, no continuous location tracking, only the postcode string plus verification outcome. Logs carry org, user, and request identifiers but never names, emails, phones, passwords, or tokens. Retention comes from one documented setting (engineering default 7 years; legal sign-off pending).

**Accessibility is a hard gate**, executed to WCAG 2.2 AA.

**Testing gate:** no security-sensitive story is complete without automated tests for allowed access, denied role/scope access, and cross-tenant isolation across the five vectors, run against real PostgreSQL + PostGIS under the non-owner runtime role — never SQLite, never superuser.

**Agent rules binding every story:** business rules live in the service layer, never in controllers or components; never write another module's tables; never weaken a security control for convenience; never claim a control is implemented before its passing test exists; report gaps rather than skipping them.

## Technical Decisions

- **Modular monolith, layered:** adapters (Inertia controllers, API controllers, jobs) validate input, call one application-service method, and format output. Business rules, authorisation, tenant stamping, and audit dispatch live in domain services; one owning module per table.
- **Tenant mechanism:** a request-scoped `TenantContext` singleton (organisation id, user id, roles, client scope) initialised by middleware; each request runs in a transaction whose first statement binds the organisation setting transaction-locally so pooled connections cannot leak context. Application scopes and database policies are both required. The membership lookup at sign-in precedes a known tenant — that bootstrap path still needs finalising in Story 1.2.
- **Database roles:** migrations run under an administrative owner role used only in deployment; application, queues, scheduler, and tests connect as a non-owner runtime role with no bypass and only select/insert on append-only tables.
- **Identifiers and time:** ordered UUID keys everywhere; timestamps stored in UTC, transmitted as ISO-8601, rendered in `Africa/Lagos`.
- **Typed contract:** Spatie Data objects are the single source of truth for inputs and outputs, backing both API resources and Inertia props; TypeScript types are generated and hand-written duplicates forbidden.
- **Data model introduced here:** `organisations`, `users`, `memberships` (roles, client scope, status). Later tenant tables adopt the same isolation pattern. A separate approvals entity is prohibited — supervisor sign-off is a field.
- **Provider-agnostic infrastructure:** object storage only through Laravel's standard S3 disk configured by environment variables, one private bucket per environment; no proprietary SDKs in domain code. Every new configuration variable gets an example-environment placeholder in the same change.
- **Stack:** PHP 8.4, Laravel 13 (official React starter kit), Inertia 3, React 19 + TypeScript 5, Tailwind 4, reskinned shadcn/ui, Sanctum 4, Pest, PostgreSQL 17 + PostGIS, docker-compose locally. Pin versions and commit lockfiles.
- **Observability:** structured JSON logs with org, user, and request identifiers and no personal data; error tracking spans frontend and backend and is configured before real customer data.

## UX & Interaction Patterns

- **Editorial, light-mode-only visual language:** white paper surfaces, navy ink, 1px architectural rules, Georgia for body and tabular copy, Arial for controls and headings. Zero external webfonts, so nothing is render-blocking on Nigerian mobile networks.
- **Tokens only:** colour palette with five semantic badge triples and three border tiers, fixed type scale, 4px spacing grid, 0–3px radii (pills and large rounded cards banned), three elevation levels with base surfaces unshadowed, 150/200ms motion, breakpoints at 760px and 1024px. Reduced-motion disables transitions globally.
- **21 canonical components in two batches** (layout/content, then interactive and field primitives), each rendering its applicable states and passing keyboard and accessibility checks. Interactive control borders meet 3:1 non-text contrast; data tables keep a sticky first column, tabular numerals, and scroll shadows on mobile.
- **Global chrome:** skip link is the first focusable element on every page; desktop header shows organisation, a client-account scope switcher that reloads page props with state discarded, module links, and a role badge; ≤760px gets a compact header, slide-over navigation, and a bottom quick-task bar that hides when the virtual keyboard opens. One primary action per view; overlays never stack beyond one level; navigation outside a user's abilities is omitted.
- **11 operational states implemented once as shared patterns** — skeleton, empty first-use, empty no-results, zero-activity certification, focus-visible, validation error, domain exception alert, rate-limited, network-degraded/interrupted retry, GPS-unavailable, permission denied, immutable record — plus live-region announcements for asynchronous changes. Skeletons match final row heights.
- **Voice codified as shared copy:** sentence case, no em or en dashes, a three-part error formula (what happened, operational impact, next action) that preserves entered data, a banned-words list, and reusable legal copy (postcode non-title disclaimer, immutable-record notice, zero-activity certification). "Accountable custodian" and "assigned technician" are never conflated.
- **Accessibility floor:** 40px touch targets (36px compact mobile), 16px mobile inputs to prevent iOS zoom, scroll padding so sticky chrome never obscures focus, status never by colour alone, universal visible focus ring.
- **Banned patterns that fail review:** infinite scroll, optimistic state on compliance mutations, hover-only actions, stacked modals, decorative empty-state illustrations, colour-only status, placeholder-as-label, icon-only buttons without accessible names.

## Cross-Story Dependencies

- 1.1 precedes everything; 1.2 precedes 1.3 and 1.4, and its middleware and context are consumed by every later epic.
- 1.5 precedes 1.6–1.8; 1.6 and 1.7 precede 1.8 (the shell composes them) and 1.9 (the states layer applies to them); 1.9's copy system is reused by all UI stories in Epics 2–5.
- 1.10 is consumed by Epic 3 workflow evidence capture and by the uploader and gallery components in 1.7; 1.11's audit service is called by sign-in (1.3) and every later module, and its error envelope is consumed by all adapters and Epic 4 exports.
- Full role/scope administration is Epic 5; Epic 1 delivers only the substrate — do not build membership administration screens here.
