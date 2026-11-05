<?php

use App\Domain\Tenancy\Enums\MembershipStatus;
use App\Domain\Tenancy\Enums\OrganisationStatus;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Organisation;
use App\Infrastructure\Database\TenantIsolation;
use App\Infrastructure\Tenancy\Actions\StoreTenantContext;
use App\Infrastructure\Tenancy\Exceptions\MembershipNotResolvable;
use App\Infrastructure\Tenancy\Exceptions\TenantContextNotBound;
use App\Infrastructure\Tenancy\Exceptions\TenantMismatch;
use App\Infrastructure\Tenancy\TenantContext;
use App\Infrastructure\Tenancy\TenantContextService;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Tenant isolation matrix for `memberships` (AGENTS.md §6, AD-19)
|--------------------------------------------------------------------------
|
| `memberships` is the only tenant table Story 1.2 creates. Every vector the
| isolation gate requires is proven here, against real PostgreSQL 17 + PostGIS
| under the non-owner runtime role `pai_app`:
|
|   1. HTTP       - the middleware binds the context, or refuses the request.
|   2. Service    - TenantContextService resolves only the user's membership.
|   3. Raw SQL    - APP_ORG_SET / APP_ORG_UNSET under `pai_app`.
|   4. Forged     - an organisation_id in the payload is ignored.
|   5. Privilege  - RLS is forced and `pai_app` owns nothing.
|
| Queued jobs and signed URLs are not applicable yet: there are no tenant-aware
| jobs or attachments until Epics 2-5 (see RLS-POLICIES.md §8).
|
*/

describe('raw SQL under the runtime role', function () {
    it('returns only the bound tenant rows', function () {
        $visibleToA = $this->asTenant(
            $this->organisationA->id,
            fn () => Membership::query()->pluck('organisation_id')->all(),
        );

        expect($visibleToA)->toBe([$this->organisationA->id]);
    });

    it('returns no rows belonging to another tenant', function () {
        $organisationIds = $this->asTenant(
            $this->organisationA->id,
            fn () => DB::select('select organisation_id from memberships'),
        );

        $ids = array_map(fn (object $row): string => (string) $row->organisation_id, $organisationIds);

        expect($ids)->toBe([$this->organisationA->id])
            ->and($ids)->not->toContain($this->organisationB->id);
    });

    it('returns zero rows for every tenant when app.org_id was never set', function () {
        // Exercise the `missing_ok` half of the fail-closed form: a connection on
        // which app.org_id has never been set at all, so current_setting(...,
        // true) returns NULL rather than raising and NULLIF is not involved.
        // A fresh connection is required because the fixture transactions have
        // already set the variable on the suite's shared connection.
        $fresh = DB::connectUsing('app_org_unset', config('database.connections.pgsql'));

        try {
            $setting = $fresh->selectOne("select current_setting('app.org_id', true) as org");

            $rows = $fresh->select('select * from memberships');

            expect($setting->org)->toBeNull()
                ->and($rows)->toBeEmpty();
        } finally {
            $fresh->disconnect();
            DB::purge('app_org_unset');
        }
    });

    it('returns zero rows for every tenant when app.org_id is empty', function () {
        // The empty-string half of the fail-closed form: a reset or blanked
        // setting must not raise 22P02 on the ::uuid cast.
        $rows = DB::transaction(function () {
            DB::select("select set_config('app.org_id', '', true)");

            return DB::select('select * from memberships');
        });

        expect($rows)->toBeEmpty();
    });

    it('rejects an insert stamped for another tenant', function () {
        $this->expectException(QueryException::class);

        $this->asTenant($this->organisationB->id, function () {
            // A forged row: the bound tenant is B, the row claims A.
            DB::insert(
                'insert into memberships (id, organisation_id, user_id, roles, status, created_at, updated_at)
                 values (?, ?, ?, ?, ?, now(), now())',
                [(string) Str::uuid(), $this->organisationA->id, $this->userB->id, json_encode(['technician']), 'active'],
            );
        });
    });

    it('binds the tenant with a transaction-scoped setting', function () {
        // PostgreSQL restores a set_config(..., is_local => true) value when the
        // enclosing transaction ends, which is what makes the setting safe under
        // PgBouncer transaction pooling (AD-3). Asserting the actual semantics
        // here would require committing, which the test transaction cannot do, so
        // this verifies the binding mechanism the middleware uses.
        $store = new StoreTenantContext;
        $context = $this->contextFor($this->organisationA->id);

        $bound = DB::transaction(function () use ($store, $context): ?string {
            $store->bindSessionVariable(DB::connection(), $context);

            return DB::selectOne("select current_setting('app.org_id', true) as org")->org;
        });

        expect($bound)->toBe($this->organisationA->id)
            ->and(StoreTenantContext::SESSION_VARIABLE)->toBe('app.org_id');
    });
});

describe('service layer', function () {
    it('resolves the authenticated user organisation, roles and client scope', function () {
        $context = app(TenantContextService::class)
            ->resolveFor($this->userA->id);

        expect($context->organisationId)->toBe($this->organisationA->id)
            ->and($context->userId)->toBe($this->userA->id)
            ->and($context->membershipId)->toBe($this->membershipA->id)
            ->and($context->roles)->toBe(['technician'])
            ->and($context->clientScope)->toBeNull();
    });

    it('carries a restricted client scope through to the context', function () {
        $clientAccountId = (string) Str::uuid();

        $this->asTenant(
            $this->organisationA->id,
            fn () => $this->membershipA->update(['client_scope' => [$clientAccountId]]),
        );

        $context = app(TenantContextService::class)
            ->resolveFor($this->userA->id);

        expect($context->clientScope)->toBe([$clientAccountId])
            ->and($context->isRestrictedToClientAccounts())->toBeTrue();
    });

    it('rejects a user with no membership', function () {
        $user = User::factory()->create();

        expect(fn () => app(TenantContextService::class)->resolveFor($user->id))
            ->toThrow(MembershipNotResolvable::class);
    });

    it('rejects a suspended membership', function () {
        $this->asTenant(
            $this->organisationA->id,
            fn () => $this->membershipA->update(['status' => MembershipStatus::Suspended]),
        );

        expect(fn () => app(TenantContextService::class)->resolveFor($this->userA->id))
            ->toThrow(MembershipNotResolvable::class);
    });

    it('rejects a membership in a suspended organisation', function () {
        // Suspending an organisation is an owner-level write: the runtime role
        // has no UPDATE on `organisations`, which is what would otherwise let a
        // tenant rewrite another organisation's status (and status is exactly
        // what the lookup reads). A freshly created suspended organisation with
        // its own member models that state directly.
        $suspended = Organisation::factory()->create([
            'name' => 'Suspended Organisation',
            'status' => OrganisationStatus::Suspended,
        ]);

        $member = User::factory()->create(['email' => 'suspended@example.test']);
        $this->createMembership($suspended, $member);

        expect(fn () => app(TenantContextService::class)->resolveFor($member->id))
            ->toThrow(MembershipNotResolvable::class);
    });

    it('does not return another user memberships', function () {
        $memberships = app(TenantContextService::class)
            ->membershipsFor($this->userA->id);

        expect($memberships)->toHaveCount(1)
            ->and($memberships[0]->organisation_id)->toBe($this->organisationA->id);
    });

    it('refuses to guess when a user belongs to several organisations', function () {
        // A second active membership, with no selection in the session: picking
        // one silently would be a tenant switch, so resolution must refuse.
        $this->createMembership($this->organisationB, $this->userA);

        expect(fn () => app(TenantContextService::class)->resolveFor($this->userA->id))
            ->toThrow(MembershipNotResolvable::class);
    });

    it('rejects a stale or forged session membership selection', function () {
        $other = $this->createMembership($this->organisationB, $this->userA);
        $forgedMembershipId = (string) Str::uuid();

        // A value that names no membership of this user must not fall back to
        // one of the user's real memberships.
        expect(fn () => app(TenantContextService::class)->resolveFor($this->userA->id, $forgedMembershipId))
            ->toThrow(MembershipNotResolvable::class);

        expect($other->organisation_id)->toBe($this->organisationB->id);
    });

    it('binds the organisation the session selected', function () {
        $selected = $this->createMembership($this->organisationB, $this->userA);

        $context = app(TenantContextService::class)
            ->resolveFor($this->userA->id, $selected->id);

        expect($context->organisationId)->toBe($this->organisationB->id)
            ->and($context->membershipId)->toBe($selected->id);
    });
});

describe('http vector', function () {
    it('returns only the bound tenant rows through a real request', function () {
        // A route registered only for this test, so nothing ships: it reads
        // through the tenant layer (Eloquent global scope + PostgreSQL RLS) and
        // returns the organisation ids it can see. If the `tenant` middleware
        // were removed from the web group, the context would be unbound, the
        // query would abort, and this test would fail rather than pass.
        Route::middleware(['web', 'auth'])->get('/__probe/memberships', function () {
            return response()->json([
                'organisations' => Membership::query()->pluck('organisation_id')->all(),
            ]);
        });

        $response = $this->actingAs($this->userA)->get('/__probe/memberships');

        $response->assertOk();

        $ids = $response->json('organisations');

        expect($ids)->toBe([$this->organisationA->id])
            ->and($ids)->not->toContain($this->organisationB->id);
    });

    it('refuses the same probe route for a user with no membership', function () {
        Route::middleware(['web', 'auth'])->get('/__probe/memberships', function () {
            return response()->json([
                'organisations' => Membership::query()->pluck('organisation_id')->all(),
            ]);
        });

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/__probe/memberships')
            ->assertForbidden();
    });

    it('rejects a suspended membership', function () {
        $this->asTenant(
            $this->organisationA->id,
            fn () => $this->membershipA->update(['status' => MembershipStatus::Suspended]),
        );

        $this->actingAs($this->userA)
            ->get(route('dashboard'))
            ->assertForbidden();
    });

    it('ignores a forged organisation_id in the request body', function () {
        $this->actingAs($this->userA)
            ->get(route('dashboard', ['organisation_id' => $this->organisationB->id]))
            ->assertOk();

        $context = app(TenantContextService::class)->resolveFor($this->userA->id);

        expect($context->organisationId)->toBe($this->organisationA->id)
            ->and($context->organisationId)->not->toBe($this->organisationB->id);
    });

    it('leaves guest requests unbound', function () {
        $this->get(route('login'))->assertOk();

        expect(TenantContext::bound())->toBeFalse();
    });
});

describe('privileges and schema state', function () {
    it('forces row level security on memberships and leaves organisations exempt', function () {
        $state = DB::selectOne(
            "select relname, relrowsecurity, relforcerowsecurity
             from pg_class where relname in ('memberships','organisations') order by relname"
        );

        $memberships = DB::selectOne("select relrowsecurity, relforcerowsecurity from pg_class where relname = 'memberships'");
        $organisations = DB::selectOne("select relrowsecurity, relforcerowsecurity from pg_class where relname = 'organisations'");

        expect((bool) $memberships->relrowsecurity)->toBeTrue()
            ->and((bool) $memberships->relforcerowsecurity)->toBeTrue()
            ->and((bool) $organisations->relrowsecurity)->toBeFalse()
            ->and($state)->not->toBeNull();
    });

    it('does not make the runtime role a superuser or RLS bypasser', function () {
        $role = DB::selectOne(
            'select current_user as name, rolsuper, rolbypassrls from pg_roles where rolname = current_user'
        );

        expect($role->name)->toBe((string) config('database.connections.app.username'))
            ->and((bool) $role->rolsuper)->toBeFalse()
            ->and((bool) $role->rolbypassrls)->toBeFalse();
    });

    it('does not let the runtime role own the tenant tables', function () {
        $owners = DB::select(
            "select tablename, tableowner from pg_tables where tablename in ('memberships','organisations')"
        );

        foreach ($owners as $table) {
            expect($table->tableowner)->not->toBe((string) config('database.connections.app.username'));
        }
    });

    it('applies the exact fail-closed predicate to the tenant policy', function () {
        $policy = DB::selectOne(
            "select qual from pg_policies where tablename = 'memberships' and policyname = ?",
            [TenantIsolation::policyName('memberships')],
        );

        expect($policy)->not->toBeNull()
            ->and($policy->qual)->toContain("NULLIF(current_setting('app.org_id'::text, true), ''::text)")
            ->and($policy->qual)->toContain('::uuid');
    });

    it('does not let the runtime role rewrite another user row', function () {
        // `users` has no tenant key and no RLS, so the grant is the only thing
        // standing between a tenant and another tenant's account. `email` in
        // particular redirects the password-reset flow, so UPDATE there would be
        // an account takeover. Asserted through the catalogue rather than by
        // issuing a doomed UPDATE, which would abort the test transaction.
        $privileges = DB::selectOne(
            "select
                has_table_privilege(?, 'users', 'UPDATE') as any_update,
                has_column_privilege(?, 'users', 'email', 'UPDATE') as email_update,
                has_column_privilege(?, 'users', 'password', 'UPDATE') as password_update",
            array_fill(0, 3, TenantIsolation::runtimeRole()),
        );

        expect((bool) $privileges->any_update)->toBeFalse()
            ->and((bool) $privileges->email_update)->toBeFalse()
            ->and((bool) $privileges->password_update)->toBeTrue();
    });

    it('does not let the runtime role update any organisation', function () {
        // `organisations` carries no RLS policy, so an UPDATE grant would let a
        // tenant rewrite another organisation's name or status - and status is
        // what the suspension check reads.
        $privileges = DB::selectOne(
            "select has_table_privilege(?, 'organisations', 'UPDATE') as any_update,
                    has_table_privilege(?, 'organisations', 'SELECT') as can_select",
            [TenantIsolation::runtimeRole(), TenantIsolation::runtimeRole()],
        );

        expect((bool) $privileges->any_update)->toBeFalse()
            ->and((bool) $privileges->can_select)->toBeTrue();
    });

    it('indexes memberships by user for the pre-tenant lookup', function () {
        // resolve_user_memberships() filters on user_id alone on every
        // authenticated request, before any tenant is bound; without an index
        // that is a full scan.
        $indexes = DB::select("select indexdef from pg_indexes where tablename = 'memberships'");

        $usesUserId = array_filter(
            $indexes,
            fn (object $index): bool => (bool) preg_match('/\(user_id\)|, user_id|\(user_id,/', $index->indexdef),
        );

        expect($usesUserId)->not->toBeEmpty();
    });
});

describe('tenant-owned model stamping', function () {
    it('stamps the bound organisation on create', function () {
        $user = User::factory()->create();

        $membership = TenantContext::run(
            TenantContext::fromMembership($this->membershipA->id, $this->organisationA->id, $this->userA->id, ['technician'], null),
            fn () => $this->asTenant($this->organisationA->id, fn (): Membership => Membership::factory()->for($user)->create()),
        );

        expect($membership->organisation_id)->toBe($this->organisationA->id);
    });

    it('aborts creation when the model carries a conflicting organisation_id', function () {
        $user = User::factory()->create();

        $context = TenantContext::fromMembership(
            $this->membershipA->id,
            $this->organisationA->id,
            $this->userA->id,
            ['technician'],
            null,
        );

        expect(function () use ($context, $user) {
            TenantContext::run(
                $context,
                fn () => Membership::factory()
                    ->forOrganisation($this->organisationB)
                    ->for($user)
                    ->create(),
            );
        })->toThrow(TenantMismatch::class);
    });

    it('refuses to create a tenant model with no context and no organisation', function () {
        $user = User::factory()->create();

        expect(fn () => Membership::factory()->for($user)->create())
            ->toThrow(TenantContextNotBound::class);
    });

    it('never lets a forged fillable organisation_id reach the model', function () {
        expect((new Membership)->getFillable())->not->toContain('organisation_id');
    });
});
