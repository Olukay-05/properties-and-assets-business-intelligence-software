# Sprint Change Proposal — Tenant-isolation gate sequencing

**Date:** 2026-11-05
**Triggered by:** Story 1.2 (tenant context and RLS isolation infrastructure)
**Prepared by:** Developer agent, via Correct Course
**Scope classification:** Moderate — backlog reorganization (PO/DEV), no replan required

---

## 1. Issue Summary

### 1.1 Triggering story

Story 1.2 delivered the tenant-isolation *mechanism*: PostgreSQL roles, the `TenantContext`, `TenantScope`, the fail-closed RLS helper `TenantIsolation::enableTenantIsolation()`, and a real-PostgreSQL `Isolation` test suite. During its review, it became clear that the gate this story feeds sits in the wrong place in the plan.

### 1.2 Core problem

`AGENTS.md` §6 requires a tenant-isolation matrix, verified across five vectors, to pass **before merge or deploy**. `epics.md` schedules that matrix as **Story 6.1**, inside Epic 6 (Pilot Hardening & Compliance Gate), which runs *after* Epics 2–5.

Epics 2–5 create the remaining **15 of the 17 tenant tables** — `client_accounts`, `properties`, `locations`, `site_zones`, `assets`, `asset_custody`, `inspection_templates`, `inspection_template_items`, `inspections`, `inspection_responses`, `maintenance_requests`, `work_orders`, `work_order_events`, `contractors`, `attachments`, `audit_events`. Under the current plan every one of those merges with **no enforced isolation gate**. A cross-tenant leak introduced in Epic 2 would not be caught until Epic 6 — after the code has been built on.

Issue type: **a sequencing defect in the plan**, exposed by implementation. The requirement never changed; its position in the backlog does not match its stated enforcement point.

### 1.3 Supporting evidence

| Evidence | Source |
|---|---|
| AGENTS.md §6 makes the five-vector matrix a merge/deploy gate | `AGENTS.md` §6 |
| Story 6.1 sits in Epic 6, after Epics 2–5 | `epics.md:824` |
| 15 of 17 tenant tables are created in Epics 2–5 | `epics.md:348–820`, `ARCHITECTURE-SPINE.md` AD-2/AD-4 |
| Story 1.2 ships the mechanism and 3 of 5 vectors, for `memberships` only | `docs/security/RLS-POLICIES.md` §8.1 |
| CI cannot enforce anything today: `composer ci:check` runs `phpstan analyse`, which fails on 11 pre-existing errors from Story 1.1 (`InstallFeaturesCommand.php`, missing `laravel/chisel`) | `composer.json:75–89`; verified by running PHPStan |
| Two of the five vectors (queued jobs, signed URLs) have no infrastructure until Epics 3–4 | `RLS-POLICIES.md` §8.1 rows 5–6 |

---

## 2. Impact Analysis

### 2.1 Epic impact

| Epic | Impact |
|---|---|
| **Epic 1** | Unchanged in scope. Story 1.2 is unaffected in its own acceptance criteria; it already delivered the reusable helper the gate depends on. |
| **Epics 2, 3, 4, 5** | Each gains a standing acceptance criterion on every story that creates or modifies a tenant table: ship isolation coverage in the same change. |
| **Epic 6** | Story 6.1 is rescoped from *build the matrix* to *audit and enforce the matrix*, because by then the matrix coverage should already exist per-epic. |

### 2.2 Story impact

- **All stories creating a tenant table** (2.1, 2.3, 2.6, 2.7, 3.1, 3.3, 3.5, 3.7, 3.9, 3.12, 4.3, 5.1, 5.3, 5.5): add the isolation-coverage acceptance criterion.
- **Story 6.1**: rescoped; its "all core entities" AC becomes an audit against a matrix that is already populated.
- **New story (Epic 1): CI isolation gate enforcement** — makes `ci:check` green and proves the `Isolation` suite cannot silently pass by being skipped.

### 2.3 Artifact conflicts

| Artifact | Conflict | Action |
|---|---|---|
| `epics.md` | Story 6.1 placed after the stories it must gate | Rescope 6.1; add per-epic criterion |
| `AGENTS.md` §6 | States the gate exists; nothing enforces it in CI | No text change needed — implementation catches up to the stated rule |
| `docs/security/RLS-POLICIES.md` §8.1 | Marks vectors 5–6 Outstanding "for Story 6.1" | Repoint to the epic that introduces jobs/attachments |
| `composer.json` `ci:check` | Fails on pre-existing PHPStan errors, so CI enforces nothing | Fix in the new Epic 1 story |
| `.github/workflows/tests.yml` | Runs a suite that is not verified to include `Isolation` | Add an explicit assertion |

### 2.4 Technical impact

No architectural change. The mechanism Story 1.2 built (`enableTenantIsolation()`, `IsolationTestCase`, the `Isolation` testsuite, the `pai_app` connection) is exactly what the per-epic criterion needs. The change is *where in the backlog* the obligation lands, plus making CI actually fail when it is unmet.

---

## 3. Recommended Approach

**Selected: Option 1 — Direct Adjustment (hybrid: new story + rescope + per-epic criterion).**

**Justification**

- The mechanism already exists, so this is a backlog and acceptance-criteria change, not a redesign. Effort: **Low–Medium**.
- Each Epic 2–5 story that adds a tenant table is the natural place to prove that table is isolated. Deferring to 6.1 means writing isolation tests for 15 tables in one late batch, by which point the authors have lost the context of each table's invariants — strictly more effort and worse tests.
- The two remaining vectors (queued jobs, signed URLs) genuinely cannot be built before Epics 3–4 introduce jobs and attachments. Therefore a small part of the gate **must** stay late. That is why 6.1 survives in rescoped form rather than being deleted.

**Alternatives considered**

| Option | Assessment |
|---|---|
| **Move Story 6.1 to Epic 1 entirely** | Rejected: two of five vectors depend on jobs/attachments that do not exist until Epics 3–4. The story would sit blocked or be written against stubs that prove nothing. |
| **Leave as-is, enforce at 6.1** | Rejected: directly contradicts AGENTS.md §6 (merge gate) and defers detection of cross-tenant leaks to after 15 tables are built. |
| **Option 2 — Rollback** | Not applicable. No completed story needs reverting; the defect is sequencing, not implementation. |
| **Option 3 — MVP scope review** | Not applicable. The requirement is unchanged; MVP scope is unaffected. |

**Timeline impact:** negligible. Per-epic isolation coverage adds work to stories that were already writing tests; it does not add new stories to Epics 2–5. One new Epic 1 story is added.

**Risk assessment:** **Low**. The change adds obligations to existing stories and repoints one late story; it removes none.

---

## 4. Detailed Change Proposals

### 4.1 `epics.md` — Epic 1, new story

**ADD after Story 1.11:**

```markdown
### Story 1.12: CI isolation gate enforcement

As a platform owner,
I want the tenant-isolation suite to run in CI and fail the build when it is missing or red,
So that AGENTS.md §6's merge gate is actually enforced from Epic 1 onward (NFR-1, AD-19).

**Acceptance Criteria:**

**Given** the `Isolation` suite and the PostGIS test database from Story 1.2
**When** `composer ci:check` runs on a pull request
**Then** the job provisions the database roles, runs `--testsuite=Isolation`, and fails if the suite reports zero tests or any failure
**And** `ci:check` exits zero on a clean branch — meaning the pre-existing PHPStan failures in `InstallConsoleCommands`/`InstallFeaturesCommand` are resolved, baselined in `phpstan.neon`, or excluded with a documented rationale
**And** `phpunit.xml` and the workflow agree on a single database port, with the authoritative source documented
```

**Rationale:** verified that `phpstan analyse` currently fails on 11 chisel-related errors from Story 1.1, so `ci:check` has never passed and no CI gate exists today. Without this story, every other part of this proposal is unenforced.

### 4.2 `epics.md` — standing criterion for Epics 2–5

**ADD to the Epic 2, 3, 4 and 5 header sections:**

```markdown
**Isolation gate (applies to every story in this epic that creates or alters a tenant table):**
the story is not complete until the table is
- created with `TenantIsolation::enableTenantIsolation()` — never hand-written policy SQL,
- covered in `tests/Isolation/` across the vectors available at that point (HTTP via a route that reads the table, service layer, raw SQL under `pai_app`, and forged-input negatives),
- granted the minimum runtime privileges (append-only tables get `SELECT, INSERT` only), and
- recorded as Verified in `docs/security/RLS-POLICIES.md` §8.1.
```

**Rationale:** places the obligation at the moment the table is created, when its invariants are known.

### 4.3 `epics.md` — Story 6.1 rescope

**OLD:**

```markdown
### Story 6.1: Tenant-isolation test matrix as a CI gate

**Given** all core entities (client_accounts, properties, locations, ...)
**When** the matrix runs
**Then** each entity is verified across HTTP requests, direct service calls, raw SQL, queued jobs, and signed URL generation
```

**NEW:**

```markdown
### Story 6.1: Tenant-isolation matrix audit and completion

As a platform owner,
I want an audited, complete five-vector isolation matrix for every tenant table,
So that Epic 6 certifies coverage that was built per-epic rather than creating it from scratch (NFR-1, AD-19).

**Acceptance Criteria:**

**Given** the per-epic isolation coverage delivered through Epics 1–5
**When** the audit runs
**Then** every one of the 17 tenant tables has a row in the matrix, evidenced by a named passing test in `tests/Isolation/`
**And** the two vectors that could not exist earlier — queued jobs (`TenantAware` middleware, AD-14) and signed-URL generation (AD-13) — are implemented and verified for every table that uses them
**And** any table found without coverage fails the audit and is fixed in the same story
**And** CI blocks merge on failure
```

**Rationale:** retains the two genuinely late vectors while removing the duplication of building 15 tables' isolation tests at the end.

### 4.4 `docs/security/RLS-POLICIES.md` §8.1

**OLD:** `| 5. Background job isolation | Outstanding — no TenantAware jobs exist yet (Story 6.1) | — |`

**NEW:** `| 5. Background job isolation | Outstanding — no TenantAware jobs exist yet; delivered per-epic from Epic 3, audited in Story 6.1 | — |`

Same treatment for row 6 (signed URLs, delivered from Epic 4).

**Rationale:** the current text implies the gate is a single late event; the approved model is per-epic delivery plus a late audit.

---

## 5. Implementation Handoff

**Scope:** Moderate — backlog reorganization.

| Recipient | Responsibility |
|---|---|
| **Product Owner / Developer** | Apply sections 4.1–4.4 to `epics.md` and `docs/security/RLS-POLICIES.md`; add Story 1.12 to `sprint-status.yaml` as `backlog` under `epic-1`; add the standing criterion to Epics 2–5 headers. |
| **Developer agent** | Implement Story 1.12 next, before Epic 2 begins. Cannot slip into Epic 2: without it the gate is unenforced. |
| **Developer agents (Epics 2–5)** | Apply the standing criterion to each tenant-table story as it is built. |

**Sequencing**

1. This proposal approved → backlog updated.
2. Story 1.12 built (CI green, `Isolation` suite asserted).
3. Epic 2 proceeds with the per-epic criterion.
4. Story 6.1 performed as an audit at the end.

**Success criteria**

- `composer ci:check` passes on a clean branch and fails when the `Isolation` suite is removed or red.
- Every tenant table added from Epic 2 onward arrives with named isolation tests in the same PR.
- Story 6.1 finds no uncovered table.

**Known dependency:** Story 1.12's "`ci:check` exits zero" criterion requires a decision on the 11 pre-existing chisel PHPStan errors. Those are Story 1.1 debris, not a defect in this change; the options are to install `laravel/chisel` as a dev dependency, baseline them, or exclude the command with rationale. That decision is recorded as an open question in Story 1.12 rather than assumed here.
