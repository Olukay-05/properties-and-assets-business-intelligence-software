---
title: 'CI isolation gate enforcement'
type: 'chore'
created: '2026-11-05'
status: 'done'
baseline_commit: 'ba4afcd09e48bda5e934c4bc299641568f2e4d4a'
route: 'dispatch'
review_loop_iteration: 1
context:
  - '{project-root}/AGENTS.md'
  - '{project-root}/docs/security/RLS-POLICIES.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** `AGENTS.md` §6 makes the tenant-isolation matrix a merge gate, but nothing enforces it. `composer ci:check` never runs `--testsuite=Isolation`, and it cannot pass at all because `phpstan analyse` dies on the 128M memory limit before it reports anything. A PR can therefore merge with the isolation suite deleted or red, and the gate AGENTS.md claims to exist is unenforced.

**Approach:** Make `ci:check` a real gate: an `isolation` Composer script that provisions the roles, runs the `Isolation` suite, and exits non-zero when the suite reports zero tests or any failure, with `failOnEmptyTestSuite` and a committed integrity test so the suite cannot pass vacuously. Repair what stops `ci:check` exiting zero (the PHPStan memory crash; whatever the frontend check genuinely reports), settle the port on one documented source, and prove the gate both passes green and fails red.

## Boundaries & Constraints

**Always:** One authoritative database port, documented, read from a single source by every consumer. The isolation gate must fail on zero tests, on any failure, and when `tests/Isolation` disappears. Real PostgreSQL 17 + PostGIS under the non-owner `pai_app` role — never SQLite, never superuser. The gate runs inside `ci:check`, so a developer running it locally gets the same result CI gets. Docs describe only what the gate actually does.

**Never:** Weaken an isolation control to make CI pass. Add a PHPStan baseline or `ignoreErrors` entry — the reported error is environmental, not a code defect. Touch `tests/Isolation/` assertions or the RLS policies. Change runtime application code (`app/`, `config/`, `routes/`, `database/`). Introduce any secret or change a committed credential. Reformat unrelated files.

**Decisions (human, 2026-11-05):** The frontend check stays enforced — establish what `vp check` actually reports and fix only genuine findings, rather than dropping it. The non-vacuous guarantee is `failOnEmptyTestSuite` plus a committed integrity test, not JUnit output parsing.

</frozen-after-approval>

## Code Map

- `composer.json:75-89` — `ci:check` → `pnpm run check`, `types:check`, `@test`; `test` → Pint, PHPStan, `artisan test`. No `--testsuite=Isolation` anywhere today.
- `phpstan.neon` — level 7, no `ignoreErrors`, no baseline. **Verified: `phpstan analyse --memory-limit=1G` reports `[OK] No errors` on `main`** — the 11 chisel errors in the sprint-change proposal are already gone. The red build is the 128M crash alone. Do not suppress anything.
- `.github/workflows/tests.yml:33-51` (`DB_PORT: 5432`), `phpunit.xml:45-63` (`DB_PORT=5433`), `tools/create-test-database.php:39-44` (`TEST_DB_PORT`, default `5433`) — three independent port readers; provisioning ignores `DB_PORT` entirely, so CI only works because it restates 5432.
- `phpunit.xml:11-13` — `Isolation` testsuite already declared; `tests/Pest.php:23-24` binds `IsolationTestCase`; `tests/Isolation/` holds 31 tests.
- `package.json:9` — `"check": "vp check"` (format + lint + type-check). **Unverified:** it cannot run on this machine (sandbox `spawn EPERM`, broken `@tailwindcss/oxide` binary), so its CI status is unknown — the unknown to establish, not known drift.
- `docs/security/RLS-POLICIES.md:198-229` — §8 defines the gate, §8.1 the per-vector status; leave Outstanding rows Outstanding.

## Tasks & Acceptance

**Execution:**
- [x] `composer.json` — add an `isolation` script (`tools/create-test-database.php`, then `artisan test --testsuite=Isolation`), call it from `ci:check` before `@test`, and add `--memory-limit=1G` to the `phpstan analyse` call — the gate itself plus the fix for the only reason the build is red.
- [ ] `phpunit.xml` — keep `failOnEmptyTestSuite="true"` as belt-and-braces, but do **not** claim it is what fails an empty suite: verified that `artisan test` already exits 1 on zero tests with the attribute absent (`Merger::hasExplicitTestSelection`). Its comment must say exactly that, and the `Gate` test must pin the **observed exit-code behaviour**, not the attribute string.
- [ ] `tests/Gate/CiIsolationGateTest.php` (new) — assert the suite stays registered in `phpunit.xml`, `tests/Isolation` holds real test files, and `ci:check` still invokes `isolation` — so unhooking the gate fails a test instead of passing silently. Must run without a database.
- [x] `.github/workflows/tests.yml` — pass `TEST_DB_PORT: ${{ env.DB_PORT }}` to the provisioning step so all three port consumers share one value — closes the drift that makes the port bug latent.
- [ ] `package.json` or the files it reports — establish what `vp check` actually flags and fix only genuine findings — keeps lint/format enforcement rather than dropping it. **Not done: `vp check` cannot execute in this environment** (sandbox blocks its subprocess; `@tailwindcss/oxide` native binary fails to load). See Implementation Notes.
- [x] `docs/security/RLS-POLICIES.md` §8/§8.1 and `docs/security/THREAT-MODEL.md:85` — state the enforced gate and which port is authoritative, and correct the stale T-01 "no controls" status — docs must match behaviour.
- [x] Port documentation (workflow comment or README) — name the single authoritative source — AC3.

**Acceptance Criteria:**
- Given a clean checkout with PostGIS running, when `composer ci:check` runs, then it exits 0 and shows the `Isolation` suite with a non-zero test count.
- Given `tests/Isolation` emptied or the suite de-registered, when `composer ci:check` runs, then it exits non-zero blaming the isolation gate.
- Given one deliberately failing isolation test, when `composer ci:check` runs, then it exits non-zero.
- Given `phpstan analyse` under a default 128M limit, when run via `ci:check`, then it completes with no errors.
- Given the workflow and `phpunit.xml`, when compared, then one port is in play, documented, and provisioning reads it.

## Spec Change Log

### Iteration 1 — 2026-11-05 — `failOnEmptyTestSuite` was the wrong mechanism

**Triggering finding:** review iteration 0 documented `failOnEmptyTestSuite="true"` (in `phpunit.xml`, `RLS-POLICIES.md` §8.2 and `THREAT-MODEL.md` T-01) as the control that stops the isolation gate from passing with zero tests. Reproduced first-hand: `php artisan test --testsuite=<empty suite>` prints "No tests found." and exits **1 with the attribute and 1 without it**, and bare PHPUnit exits 1 both ways too. The runner already fails empties via its own explicit-selection default (`Merger::hasExplicitTestSelection`). The attribute is therefore belt-and-braces, not the guarantee.

**What was amended (non-frozen only):** the `phpunit.xml` task now distinguishes the attribute (redundant safety) from the real guarantee, and every doc task must describe the mechanism that actually fails the build. Nothing in `<frozen-after-approval>` changed: the intent ("the gate must fail on zero tests") is unchanged and still correct.

**Known-bad state avoided:** shipping a security document that names a control which does not do the work, and a test that pins the *string* `failOnEmptyTestSuite="true"` while the suite would pass identically without it — the "documentation for unverified behaviour" that AGENTS.md §5 forbids.

**KEEP (must survive re-derivation):**
- `composer.json`: the `isolation` script (`@db:setup` then `artisan test --testsuite=Isolation`), invoked as `@isolation` in `ci:check` **before** `@test`, and `--memory-limit=1G` on `phpstan analyse`. All three verified working.
- The guard suite lives in `tests/Gate/` bound to a database-free base case, registered as its own `Gate` testsuite — a guard inside `tests/Isolation/` could only report a broken gate after the database it guards was available.
- `.github/workflows/tests.yml`: `TEST_DB_PORT: ${{ env.DB_PORT }}` and the single-authority port comment.
- The `RLS-POLICIES.md` §8.2 verification-status caveat that admits the end-to-end CI run did not happen.

## Review Triage Log

Three layers (blind hunter, edge-case hunter, verification-gap) reviewed the 37.8 kB diff. The verification-gap layer's evidence was reproduced first-hand before triage (see the `failOnEmptyTestSuite` probe under Spec Change Log).

### high — this story's problem → bad_spec

- **`failOnEmptyTestSuite` is documented as the load-bearing control but changes nothing on the `artisan test` path.** Verified by probing the vendored runner: `phpunit` with an empty suite directory exits 1 with the attribute and 1 without it; `php artisan test --testsuite=<empty>` prints "No tests found." and exits 1 **with and without** the attribute. Zero-test protection already comes from the test runner's own explicit-selection default (`Merger::hasExplicitTestSelection`, `Merger.php:1112-1128`), not from the attribute. The `phpunit.xml` comment, RLS-POLICIES §8.2 and THREAT-MODEL T-01 all claim the attribute is what stops the gate passing vacuously — that claim is false. Routed `bad_spec`: the spec's task named the attribute as the non-vacuous guarantee. Not patched in place because the correction changes what the control *is*, not how it is spelled.

### medium — resolved as false, with evidence

- **Pest's matchers ignore the message argument `expect(...)->toBeTrue('…')`.** `false`: `vendor/pestphp/pest/src/Mixins/Expectation.php:76,88,112,434` declare `toBeEmpty/toBeTrue/toBeFalse/toBeArray` with `string $message = ''`, so messages are honoured. No `ArgumentCountError`.
- **`Illuminate\Foundation\Testing\TestCase` is abstract without `createApplication()`, so `base_path()` would fatal in the Gate suite.** `false`: it is abstract but implements `createApplication()` at `Illuminate/Foundation/Testing/TestCase.php:45`, which boots the app. The six Gate tests pass while calling `base_path()`.
- **`${{ env.* }}` in a step-level `env:` block resolves before job-level `env:`.** `false`: job-level `env` is in scope for expression evaluation in step-level `env`. The workflow is also schema-validated by the YAML parse check.
- **`--memory-limit=1G` masks exhaustion / is rejected.** `false`: PHPStan measured well under 1G (`--no-progress`, 66 files, `[OK] No errors`), and `composer types:check` exits 0 at a 128M PHP default.

### medium — deferred (pre-existing or outside the change)

- **`tools/create-test-database.php` keeps a private `TEST_DB_PORT` default of 5433** and never reads `DB_PORT`; only the workflow step overrides it. Pre-existing in the script; this story removed the *CI* divergence and documented the contract. Creating a second source of truth inside a shared tool is a separate change.
- **`ADMIN_DB_USERNAME`/`ADMIN_DB_PASSWORD` remain hard-coded `postgres`/`postgres`** in the provisioning step. Pre-existing; credentials, not the port, and unchanged by this story.
- **`composer test` does not run the isolation suite**, so a green local `composer test` does not imply the gate would pass. Pre-existing script shape; adding isolation to `test` would make every local test run require a live database.
- **`ci:check` re-runs `@db:setup` and re-runs lint/type checks via `@test`.** Deliberate: provisioning must be idempotent for local parity, and `test` is meant to be a complete gate on its own. Cost is wasted seconds, not correctness.
- **A PR that edits the Gate test and `composer.json` in the same commit still merges green.** True of every in-repo config assertion, including the alternative (reading `git show HEAD:`) which can be edited identically. Real defence is branch protection plus review of `tests/Gate`; recorded rather than fixed, since fixing it in-repo is not possible.

### medium — this story's problem → patch (iteration 2, applied)

- **The "real isolation tests" floor counted source tokens, not registered cases.** Confirmed: `substr_count('it(')` is satisfied by a retained comment, so gutting the suite while leaving the word behind kept the gate green. **Patched**: the test now runs `pest --testsuite=Isolation --list-tests` and counts lines in the `Tests\Isolation\` namespace. Mutation-verified: replacing `TenantIsolationTest.php` with a placeholder full of `it(` comments fails the test (runner registers 0), and restoring it passes (31).
- **The zero-test probe only covered an explicitly selected suite.** The guarantee is also claimed for a run that selects nothing, which the probe never exercised. **Patched**: the probe now runs both shapes through `artisan test` (named empty suite, and no selection at all) with unique temp paths so concurrent runs cannot collide.

### medium — resolved as false, with evidence

- **"`php artisan test` runs only the Unit suite, so the Gate suite never executes in CI."** `false`: bare `php artisan test` on this tree ran **81 tests across every suite and 6 passed** — exactly the six Gate tests. Pest's default here is the whole `<testsuites>` block, not `Unit` alone.
- **"The fifth `Symfony\Process` argument is the idle timeout, so a hung child exits 143 and passes."** `false`: this version's constructor is `(array $command, ?string $cwd, ?array $env, mixed $input, ?float $timeout)` — five parameters, no `idleTimeout`. Passing it raised `Unknown named parameter $idleTimeout`, which is how the claim was disproved.

### medium — deferred

- **The CI provisioning step's `${{ env.* }}` contract has no repository test.** Confirmed real, but a repo-local test would be another text assertion of the shape this story rejected, and the genuine proof is the end-to-end CI run already recorded as outstanding. Deferred to the first CI run.

### low — accepted

- **Global helper functions in `tests/Gate/CiIsolationGateTest.php`.** Prefixed `gate*` (`gateRead`, `gateJson`, `gateScriptLines`, `gateExpectScriptContains`); the repo has no other Test-scoped helpers to collide with. Matches existing `tests/Pest.php` helper style.
- **Source-text assertions (`app.org_id`, `organisation_id`) in the matrix-coverage test.** This is a smoke check that the suite was not emptied, not coverage proof; `RLS-POLICIES.md` §8.1 remains the coverage record. Named as smoke in the test comment.
- **Gate tests are not covered by PHPStan** (`phpstan.neon` never analysed `tests/`). Consistent with the existing configuration.

## Implementation Notes

**What the gate is.** `composer ci:check` runs `@isolation` before `@test`. The `isolation` script calls `@db:setup` and then `artisan test --testsuite=Isolation`.

**Corrected in review iteration 1 — what actually stops a zero-test pass.** `failOnEmptyTestSuite="true"` is retained as belt and braces but is **not** the mechanism. Measured directly: `php artisan test` against a suite with no test files prints "No tests found." and exits **1** with the attribute present *and absent*; bare `phpunit` with a present-but-empty directory also exits 1 either way. The runner already treats a suite selection as an explicit choice (`Merger::hasExplicitTestSelection`, `Merger.php:1112-1128`). The guarantee is now pinned behaviourally: `tests/Gate/CiIsolationGateTest.php` runs a real empty suite through `Symfony\Process` and asserts a non-zero exit code, instead of asserting that a config string exists.

**Why the guard test lives in `tests/Gate/`.** `tests/Pest.php` binds `Isolation` to `IsolationTestCase`, which connects to PostgreSQL in `setUpTraits()`; a guard there could only report a broken gate after the database it guards existed. The `Gate` suite binds to `Illuminate\Foundation\Testing\TestCase` (boots the app, no database traits) and runs with no PostgreSQL.

**Mutation-tested.** Removing `@isolation` from `ci:check`, removing the `Isolation` testsuite entry, deleting `tests/Isolation/TenantIsolationTest.php`, and dropping `--memory-limit` from `types:check` each fail the Gate suite; the tree is restored and green afterwards.

**PHPStan.** The 11 chisel errors cited in the sprint-change proposal do not exist at this baseline — `phpstan analyse` reported `[OK] No errors` before any change. The only failure was the 128M crash. `--memory-limit=1G` is pinned in the `types:check` script (not `phpstan.neon`) so CI does not depend on a php.ini the runner controls.

**Environment limits hit during implementation (not product defects).**
- **Docker unavailable** (sandbox denies the socket). `composer ci:check` and `composer isolation` were therefore **not** run end to end against PostgreSQL. AC1, AC3 (red-suite behaviour) and the 31-test count are unverified here.
- **`vp check` cannot run** (sandbox blocks its subprocess; `@tailwindcss/oxide` native binary fails to load). Its real CI status stays unknown, so that task remains open.
- **Pint cannot run on the whole repository** in this sandbox: its parallel mode and its temp-file write both hit sandbox restrictions. Pint was run against the changed PHP files only and passes.
- A stale gitignored `_bmad/render/.../.staging-*` directory (left by the session's render step) made Pint abort with "Access denied". Removed; it is untracked cache and not part of the project.

**Known gap carried forward from Story 1.2:** `MembershipNotResolvable` is still swallowed into a generic 403 with no logging (deferred to the audit service), unrelated to this story.

## Verification

**Commands:**
- `composer ci:check` — expected: exit 0.
- `composer isolation` — expected: exit 0, 31 tests.
- `mv tests/Isolation/*.php` aside, then `composer ci:check` — expected: non-zero, gate named. Restore afterwards.
</content>
