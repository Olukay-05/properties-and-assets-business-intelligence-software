# Epic 1 Context: Platform Foundations & Design System

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Establish the foundation every later epic builds on: a team member signs in, lands inside their organisation's isolated workspace with role-scoped abilities, and sees a consistent editorial application shell on desktop and mobile. The epic also delivers the cross-cutting services every workflow consumes: session-derived tenant context with database-enforced isolation, the private evidence store with signed URLs, the append-only audit trail, and one structured error envelope. It closes with a CI gate that makes the tenant-isolation suite unskippable, so the merge requirement in the standing rules is enforced from here on.

Scaffold and tenant-isolation work already exist in the repository and the architecture spine is final; the role matrix, evidence, audit and error surfaces do not. Treat points below as decisions to make here, not settled inputs.

## Stories

- Story 1.1: Greenfield scaffold, Laravel React starter kit with typed contract tooling
- Story 1.2: Tenant context and RLS isolation infrastructure
- Story 1.3: Authentication with Sanctum and membership resolution
- Story 1.4: Role-ability policy foundation
- Story 1.5: Design tokens in Tailwind `@theme` and base typography
- Story 1.6: Core component library I, layout and content primitives
- Story 1.7: Core component library II, interactive and field primitives
- Story 1.8: Application shell, desktop header, mobile navigation, quick-task bar
- Story 1.9: Operational states and editorial copy system
- Story 1.10: Evidence service and signed-URL gateway
- Story 1.11: Audit service and structured error envelope
- Story 1.12: CI isolation gate enforcement

## Requirements & Constraints

**Tenancy and isolation (non-negotiable).** The tenant comes only from the authenticated membership, never from request input: forged `organisation_id` values are ignored, a creating hook stamps the real value and aborts on conflict. Every tenant table enables and forces row-level security under a non-owner runtime role with no bypass; an unbound tenant yields zero rows. Isolation must hold at HTTP, service layer, raw SQL, queued jobs and signed URLs, never through one layer alone.

**Roles and scope.** Six roles, with the union of abilities for multi-role memberships, and a client scope that is either unrestricted or a set of client accounts. Out-of-tenant or out-of-scope records resolve as not-found, never forbidden, so UUIDs cannot be probed; forbidden is for an unauthorised action on a visible in-scope record.

**Authentication.** First-party cookie session only: no external OAuth, no API client registry, no open self-service sign-up. Users exist through a membership and cannot authenticate without an active one. A failed sign-in never reveals which field was wrong and is audited. Rate limits: sign-in 5/min per IP plus email, uploads 30/min, exports 5/min per organisation, API 120/min, each returning a retry-after.

**Evidence.** Files are validated by content against a strict allowlist (jpeg, png, webp, pdf; no SVG or HTML), capped at 10 MB, stored under server-generated keys in a private bucket. Reads only via short-lived signed URLs issued after tenant and scope verification; non-images download as attachments.

**Audit, errors and logs.** Sensitive operations are written atomically inside the originating transaction to a table the runtime role cannot update or delete. Errors use one envelope with permanent snake_case codes; raw database errors are sanitised to error tracking. Logs carry organisation, user and request identifiers but no personal data or credentials.

**Accessibility is a hard gate**, executed to WCAG 2.2 AA, verified by keyboard-only passes and automated scans on every component.

**Testing gate.** No security-sensitive story is complete without tests for allowed access, denied role/scope access and cross-tenant isolation, run on real PostgreSQL 17 plus PostGIS as the non-owner runtime role, never SQLite and never a superuser connection. The isolation suite is a merge gate: CI must run it explicitly, fail when it reports zero tests as well as when any test fails, and stay green on a clean branch.

**Documentation truthfulness.** Docs, API references and tool descriptions describe only implemented and tested behaviour, never planned behaviour, and are updated in the same change that alters a requirement, contract or security control. Gaps and unverified controls are reported, never marked done.

## Technical Decisions

- **Service layer owns rules.** Inertia and REST adapters validate input, call one service method and format output; policy checks, tenant stamping and audit writes live in the service. One owning module per table; cross-module writes go through the owner's service.
- **Two-layer isolation.** Application scopes filter queries and database policies back them up. Client scope is a global filter driven by the membership, with the client account denormalised onto client-owned rows.
- **Database roles.** An administrative owner role runs migrations and is never used at runtime; the runtime role owns nothing, cannot bypass RLS, and holds only select/insert on append-only tables. Policy SQL is applied through one reusable helper, never hand-written per story.
- **Queued work** must carry tenant and actor, re-establish context inside its own transaction, and clear context afterwards; unscoped execution is fatal.
- **Typed contract.** Server DTOs are the single source of truth for inputs and outputs, backing both API payloads and Inertia props; TypeScript types are generated and hand-written duplicates are forbidden.
- **Identifiers and time.** Ordered UUID keys everywhere; UTC storage, rendered in `Africa/Lagos`.
- **Provider-agnostic infrastructure.** Object storage only through the framework's standard S3 disk configured by environment variables, private bucket per environment. Every new configuration variable gets an example-environment placeholder in the same change, and no secret reaches the client bundle or logs.
- **Stack.** Official Laravel React starter kit (Inertia, React, Tailwind) reskinned with shadcn/ui, Sanctum, Pest, PostgreSQL plus PostGIS, docker-compose locally. Pin versions and commit lockfiles.

## UX & Interaction Patterns

- **Editorial, light-mode-only language.** White paper surfaces, navy ink, hairline rules, Georgia for body copy and Arial for controls and headings. Zero external webfonts so nothing is render-blocking on mobile networks.
- **Tokens only.** Colour palette with semantic badge triples and three border tiers, fixed type scale, 4px spacing grid, 0–3px radii with pills banned, three elevation levels, 150/200ms motion, breakpoints at 760px and 1024px. Reduced motion disables transitions globally.
- **Component batches.** Layout and content primitives first, then interactive and field primitives (map, evidence uploader and gallery, checklist runner, async export banner, quick-task bar). Each renders its applicable states and passes keyboard and automated accessibility checks; data tables keep a sticky first column, tabular numerals and scroll shadows on mobile.
- **Global chrome.** Skip link is first focusable on every page; the desktop header shows organisation, a client-account scope switcher that discards client state on change, module links and a role badge; mobile gets a compact header, slide-over navigation and a bottom quick-task bar that hides when the keyboard opens. One primary action per view; overlays never stack; navigation outside a user's abilities is omitted.
- **States and copy implemented once.** The eleven operational states, live-region announcements, the three-part error formula (what happened, impact, next action) preserving entered data, sentence case with no em or en dashes, a banned-words list, and reusable legal copy for postcode truthfulness, immutable records and zero-activity certification.
- **Banned patterns that fail review.** Infinite scroll, optimistic compliance mutations, hover-only actions, stacked modals, decorative empty-state illustrations, colour-only status, placeholder-as-label, unnamed icon-only buttons.

## Cross-Story Dependencies

- The scaffold and tenant-isolation stories precede everything; the context, helper and test suite they install are consumed by every later epic, which extends isolation coverage as it adds each tenant table.
- Authentication depends on tenant resolution; role policies depend on stored memberships and client scope. Membership administration screens are out of scope here.
- Tokens precede both component batches; the batches precede the shell (which composes them) and the states layer (which applies to them). The copy system is reused by every later UI story.
- The evidence service is consumed by Epic 3 workflow capture and by the uploader and gallery components; the audit service is called from sign-in and every later module, and its error envelope by all adapters and export jobs.
- The CI gate closes the epic: until it is green the merge requirement is unenforced for every later story. The queued-job and signed-URL vectors cannot be exercised until later epics add jobs and attachments; auditing the full matrix belongs to the late hardening epic.
