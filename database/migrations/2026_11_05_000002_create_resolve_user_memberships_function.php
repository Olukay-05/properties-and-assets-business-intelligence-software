<?php

use App\Infrastructure\Database\TenantIsolation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Pre-tenant membership bootstrap (RLS-POLICIES.md §5.3)
|--------------------------------------------------------------------------
|
| `memberships` is a tenant table, so it cannot be read before `app.org_id` is
| bound - and `app.org_id` is read *from* a membership. This function is the
| single, narrow hole that breaks the cycle.
|
| How it works:
|
| - The function is owned by a dedicated NOLOGIN role, `pai_bootstrap`, whose
|   only special attribute is BYPASSRLS. `pai_owner`, which owns the tables,
|   does NOT hold BYPASSRLS, so FORCE ROW LEVEL SECURITY still applies to the
|   schema owner; that is what makes the owner safe for migrations.
|
| - SECURITY DEFINER makes the function run as `pai_bootstrap`, so its SELECT
|   is not filtered by the tenant policy.
|
| - SET search_path = public, pg_temp pins name resolution, so a caller cannot
|   shadow `memberships` with an object in their own schema.
|
| - It returns only (membership_id, organisation_id, user_id, roles,
|   client_scope, status) for one user id, joined to an ACTIVE organisation.
|
| - `pai_app` receives EXECUTE and nothing else, and `pai_bootstrap` gets
|   SELECT only on `memberships` and `organisations` - enough for this query
|   and nothing more. No DML, no DDL, no connection of its own.
|
| There is deliberately no `app.user_id` policy on `memberships`; the table's
| policy stays strictly tenant-scoped.
|
*/

return new class extends Migration
{
    /**
     * The deliberately narrow role that owns the bootstrap function.
     */
    public const BOOTSTRAP_ROLE = 'pai_bootstrap';

    public function up(): void
    {
        $this->assertBootstrapRoleExists();

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION resolve_user_memberships(p_user_id uuid)
            RETURNS TABLE (
                membership_id uuid,
                organisation_id uuid,
                user_id uuid,
                roles jsonb,
                client_scope jsonb,
                status text
            )
            LANGUAGE sql
            STABLE
            SECURITY DEFINER
            SET search_path = public, pg_temp
            AS $function$
                SELECT m.id,
                       m.organisation_id,
                       m.user_id,
                       m.roles,
                       m.client_scope,
                       m.status
                FROM memberships AS m
                INNER JOIN organisations AS o ON o.id = m.organisation_id
                WHERE m.user_id = p_user_id
                  AND o.status = 'active'
                ORDER BY m.created_at ASC, m.id ASC
            $function$
            SQL);

        // Handing ownership to pai_bootstrap requires the migration role to be a
        // member of it; that membership is granted at provisioning time (see
        // tools/create-test-database.php). Without it, ownership transfer fails
        // loudly rather than silently leaving FORCE RLS in the way of the lookup.
        DB::statement(sprintf(
            'ALTER FUNCTION resolve_user_memberships(uuid) OWNER TO %s',
            $this->quote(self::BOOTSTRAP_ROLE),
        ));

        DB::statement('REVOKE ALL ON FUNCTION resolve_user_memberships(uuid) FROM PUBLIC');
        DB::statement(sprintf(
            'GRANT EXECUTE ON FUNCTION resolve_user_memberships(uuid) TO %s',
            $this->quote(TenantIsolation::runtimeRole()),
        ));
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS resolve_user_memberships(uuid)');
    }

    /**
     * Ensure the `pai_bootstrap` role exists before depending on it.
     *
     * The role is provisioned outside migrations, alongside `pai_owner` and
     * `pai_app`, because the schema owner deliberately has no CREATEROLE
     * privilege: a role that can mint roles is a privilege-escalation path.
     * See docker/postgres/init/01-create-roles.sh and
     * tools/create-test-database.php.
     */
    private function assertBootstrapRoleExists(): void
    {
        $exists = DB::selectOne(
            'select 1 as present from pg_roles where rolname = ?',
            [self::BOOTSTRAP_ROLE],
        );

        if ($exists === null) {
            throw new RuntimeException(sprintf(
                'The [%s] role is missing. Provision it with docker/postgres/init/01-create-roles.sh '
                .'or php tools/create-test-database.php before running migrations.',
                self::BOOTSTRAP_ROLE,
            ));
        }

        // The least privilege the function needs: read the two tables it joins.
        // (`USAGE ON SCHEMA public` is granted at provisioning time: only the
        // schema owner or a superuser may grant schema privileges, and the
        // migration role is deliberately neither for the public schema.)
        DB::statement(sprintf(
            'GRANT SELECT ON TABLE memberships, organisations TO %s',
            $this->quote(self::BOOTSTRAP_ROLE),
        ));
    }

    private function quote(string $identifier): string
    {
        if (preg_match('/^[a-z_][a-z0-9_]*$/', $identifier) !== 1) {
            throw new RuntimeException(sprintf('Refusing to build SQL with unsafe identifier [%s].', $identifier));
        }

        return '"'.$identifier.'"';
    }
};
