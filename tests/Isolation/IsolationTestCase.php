<?php

namespace Tests\Isolation;

use App\Domain\Tenancy\Enums\MembershipRole;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Organisation;
use App\Infrastructure\Tenancy\TenantContext;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Base for tenant-isolation tests (AGENTS.md §6 gate).
 *
 * Every test here proves an isolation boundary, so nothing is stubbed and no
 * scope is removed. The suite runs under the non-owner runtime role `pai_app`
 * against real PostgreSQL (phpunit.xml), which means the assertions exercise
 * the same FORCE RLS policies production uses.
 *
 * Fixtures for two separate organisations are written on the runtime connection,
 * each inside a transaction bound to its own tenant, because FORCE RLS applies
 * to the schema owner too. Everything after that runs exactly as production
 * does: the tenant comes from the resolved membership, never from input.
 */
abstract class IsolationTestCase extends TestCase
{
    /**
     * Schema resets run as the schema owner; every assertion runs as the
     * non-owner runtime role.
     *
     * pai_app owns nothing and holds no DDL privileges (AD-4), so it cannot
     * migrate or drop tables. Making it an owner to "fix" that would destroy
     * the isolation guarantee this suite exists to prove.
     *
     * @return array<string, string>
     */
    protected function migrateFreshUsing()
    {
        return [
            '--database' => 'migrations',
            '--seed' => false,
        ];
    }

    protected Organisation $organisationA;

    protected Organisation $organisationB;

    protected User $userA;

    protected User $userB;

    protected Membership $membershipA;

    protected Membership $membershipB;

    protected function setUp(): void
    {
        parent::setUp();

        // All fixture writes share the runtime connection (and its transaction),
        // so rows written for organisation A are visible to the FK checks of
        // rows written for organisation B. Only the tenant binding differs
        // between them, which is the property under test.
        $this->organisationA = $this->createOrganisation('Organisation A');
        $this->organisationB = $this->createOrganisation('Organisation B');

        $this->userA = User::factory()->create(['email' => 'a@example.test']);
        $this->userB = User::factory()->create(['email' => 'b@example.test']);

        $this->membershipA = $this->createMembership($this->organisationA, $this->userA);
        $this->membershipB = $this->createMembership($this->organisationB, $this->userB);
    }

    /**
     * Create an organisation.
     *
     * Organisation INSERT is granted to the runtime role (provisioning creates
     * organisations); it is UPDATE that is withheld, because that is what would
     * let one tenant rewrite another's name or status. The fixture therefore
     * runs on the same connection as every other fixture.
     */
    protected function createOrganisation(string $name): Organisation
    {
        return $this->asRuntimeRole(
            fn (): Organisation => Organisation::factory()->create(['name' => $name]),
        );
    }

    /**
     * Create a membership for an organisation.
     *
     * FORCE ROW LEVEL SECURITY applies to the owner too, so even seeding has to
     * satisfy the policy: each membership is written in a transaction bound to
     * its own tenant. That is not a test shortcut, it is the same rule
     * production obeys, and it is why a cross-tenant insert is impossible even
     * for the schema owner.
     */
    protected function createMembership(Organisation $organisation, User $user, array $roles = [MembershipRole::Technician]): Membership
    {
        // Seeding needs the database setting only; there is no resolved context
        // yet (that is what the tests are about), so bind the raw setting for
        // the insert rather than going through asTenant().
        return $this->asRawTenant(
            $organisation->id,
            fn (): Membership => Membership::factory()
                ->forOrganisation($organisation)
                ->for($user)
                ->withRoles($roles)
                ->create(),
        );
    }

    /**
     * Run a callback on the runtime connection.
     *
     * This is deliberately NOT a schema-owner switch. Every fixture and
     * assertion in this suite runs as `pai_app`, inside the transaction
     * `RefreshDatabase` opened on the `pgsql` connection. Switching connection
     * here would move the writes outside that transaction, so they would be
     * invisible to the assertions and never rolled back.
     *
     * The name says exactly what happens: nothing about the connection changes.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function asRuntimeRole(callable $callback): mixed
    {
        return $callback();
    }

    /**
     * Switch the default connection to the schema owner for a callback.
     *
     * The runtime role cannot UPDATE `organisations`, so changing an
     * organisation's status genuinely requires the owner. Because this moves
     * work off the runtime connection it also leaves the test transaction, so
     * callers must commit their intent explicitly and clean up: use it only for
     * assertions about role privileges, never for fixtures the runtime-side
     * assertions depend on.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function asSchemaOwner(callable $callback): mixed
    {
        $default = config('database.default');

        config(['database.default' => 'migrations']);

        try {
            return $callback();
        } finally {
            config(['database.default' => $default]);
        }
    }

    /**
     * Run a callback inside a transaction bound to a tenant on the runtime
     * connection, exactly as the request middleware does.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function asTenant(string $organisationId, callable $callback): mixed
    {
        // The middleware binds the in-memory context and the transaction-scoped
        // database setting together; tests do the same, so Eloquent scopes and
        // PostgreSQL RLS see one consistent tenant.
        return TenantContext::run(
            $this->contextFor($organisationId),
            fn (): mixed => DB::transaction(function () use ($organisationId, $callback) {
                DB::select("select set_config('app.org_id', ?, true)", [$organisationId]);

                return $callback();
            }),
        );
    }

    /**
     * Run a callback in a transaction bound to a tenant at the database level
     * only, without an in-memory tenant context.
     *
     * Used for fixtures, which write rows for a tenant before any context can
     * be resolved. PostgreSQL RLS still applies, so the setting is required,
     * and the model's create hook allows an explicit organisation_id when no
     * context is bound.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function asRawTenant(string $organisationId, callable $callback): mixed
    {
        return DB::transaction(function () use ($organisationId, $callback) {
            DB::select("select set_config('app.org_id', ?, true)", [$organisationId]);

            return $callback();
        });
    }

    /**
     * Build the context a request for the given organisation would carry.
     */
    protected function contextFor(string $organisationId): TenantContext
    {
        $membership = $organisationId === $this->organisationA->id
            ? $this->membershipA
            : $this->membershipB;

        return TenantContext::fromMembership(
            membershipId: $membership->id,
            organisationId: $organisationId,
            userId: $membership->user_id,
            roles: $membership->roles,
            clientScope: $membership->client_scope,
        );
    }

    /**
     * Bind only the application-layer tenant context for a callback.
     *
     * The middleware binds the context and the database setting together;
     * splitting them lets a test exercise one layer at a time.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function boundTo(string $organisationId, callable $callback): mixed
    {
        $membership = $organisationId === $this->organisationA->id
            ? ($this->membershipA ?? null)
            : ($this->membershipB ?? null);

        return TenantContext::run(
            TenantContext::fromMembership(
                $membership->id ?? (string) Str::uuid(),
                $organisationId,
                $membership->user_id ?? (string) Str::uuid(),
                $membership->roles ?? [MembershipRole::Technician->value],
                $membership->client_scope ?? null,
            ),
            $callback,
        );
    }
}
