<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Infrastructure\Tenancy\Actions\StoreTenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * The single place PostgreSQL tenant Row-Level Security is applied.
 *
 * Every tenant table must call {@see enableTenantIsolation()}; the policy text
 * is written exactly once, here. A later story that raises its own
 * `CREATE POLICY` by hand is a defect, not a variation (RLS-POLICIES.md §5).
 *
 * The fail-closed form is the invariant:
 *
 *     organisation_id = NULLIF(current_setting('app.org_id', true), '')::uuid
 *
 *  - `current_setting(..., true)` returns NULL instead of raising when the
 *    variable was never set on the connection;
 *  - `NULLIF(..., '')` turns a reset/empty setting into NULL before the cast,
 *    so an emptied variable can never raise a `22P02` cast error;
 *  - comparing to NULL is UNKNOWN, so SELECT returns zero rows, INSERT and
 *    UPDATE are rejected, and DELETE matches nothing: fail closed.
 *
 * @see docs/security/RLS-POLICIES.md §5
 */
final class TenantIsolation
{
    /**
     * The `USING` / `WITH CHECK` expression shared by every tenant policy.
     */
    public const TENANT_PREDICATE = "organisation_id = NULLIF(current_setting('".StoreTenantContext::SESSION_VARIABLE."', true), '')::uuid";

    /**
     * Apply `ENABLE` + `FORCE` row-level security and the tenant policy to a table.
     *
     * Call this immediately after `Schema::create()` for the table, inside the
     * same migration. The statements run in the migration's transaction, so
     * the table is never committed to the database without RLS in force: the
     * protection and the schema land together or not at all.
     */
    public static function enableTenantIsolation(string $table): void
    {
        self::assertIdentifier($table);

        DB::statement(sprintf('ALTER TABLE %s ENABLE ROW LEVEL SECURITY', self::quote($table)));
        DB::statement(sprintf('ALTER TABLE %s FORCE ROW LEVEL SECURITY', self::quote($table)));

        DB::statement(sprintf(
            'CREATE POLICY %s ON %s AS PERMISSIVE FOR ALL TO PUBLIC USING (%s) WITH CHECK (%s)',
            self::quote(self::policyName($table)),
            self::quote($table),
            self::TENANT_PREDICATE,
            self::TENANT_PREDICATE,
        ));
    }

    /**
     * Remove the RLS policy and disable RLS (used by `down()` migrations).
     */
    public static function disableTenantIsolation(string $table): void
    {
        self::assertIdentifier($table);

        if (! Schema::hasTable($table)) {
            return;
        }

        DB::statement(sprintf('DROP POLICY IF EXISTS %s ON %s', self::quote(self::policyName($table)), self::quote($table)));
        DB::statement(sprintf('ALTER TABLE %s DISABLE ROW LEVEL SECURITY', self::quote($table)));
    }

    /**
     * Grant the runtime role a privilege set on a table.
     *
     * Standard tables get `SELECT, INSERT, UPDATE, DELETE`; append-only tables
     * (`locations`, `asset_custody`, `work_order_events`, `audit_events`) get
     * `SELECT, INSERT` only (AD-8). Keeping this in one method means a missing
     * or over-broad grant is a one-line review rather than a diff hunt.
     *
     * @param  list<string>  $privileges
     */
    public static function grantToRuntimeRole(string $table, array $privileges): void
    {
        self::assertIdentifier($table);

        $role = self::runtimeRole();

        DB::statement(sprintf(
            'GRANT %s ON %s TO %s',
            implode(', ', $privileges),
            self::quote($table),
            self::quote($role),
        ));
    }

    /**
     * Grant the runtime role UPDATE on specific columns only.
     *
     * PostgreSQL grants UPDATE per column, which is how a shared table with no
     * tenant key (notably `users`) stays writable for the columns the
     * application legitimately sets while withholding the ones that would be an
     * escalation - `users.email` is the account-takeover vector, because a
     * changed address redirects the password-reset flow.
     *
     * @param  list<string>  $columns
     */
    public static function grantColumnUpdateToRuntimeRole(string $table, array $columns): void
    {
        self::assertIdentifier($table);

        foreach ($columns as $column) {
            self::assertIdentifier($column);
        }

        DB::statement(sprintf(
            'GRANT UPDATE (%s) ON %s TO %s',
            implode(', ', array_map(self::quote(...), $columns)),
            self::quote($table),
            self::quote(self::runtimeRole()),
        ));
    }

    /**
     * The non-owner runtime role (`pai_app`, `NOBYPASSRLS`) migrations grant to.
     *
     * Read from the dedicated `app` connection, whose `DB_APP_USERNAME` is the
     * runtime role in every environment. Falling back to a constant keeps
     * `migrate` runnable on a database where that connection is not configured
     * (for example a throwaway CI database), while the grant itself is a no-op
     * there because the role does not exist.
     */
    public static function runtimeRole(): string
    {
        $role = (string) config('database.connections.app.username', '');

        return $role !== '' ? $role : 'pai_app';
    }

    /**
     * Grant the runtime role USAGE on every sequence in the public schema.
     *
     * The framework's auto-increment tables (`cache`, `jobs`, `job_batches`)
     * use sequences; INSERT on the table is not enough without USAGE on the
     * sequence it draws from.
     */
    public static function grantSequenceUsageToRuntimeRole(): void
    {
        DB::statement(sprintf(
            'GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO %s',
            self::quote(self::runtimeRole()),
        ));
    }

    /**
     * Grant the runtime role a statement privilege (e.g. EXECUTE) on a function.
     */
    public static function grantFunctionExecutionToRuntimeRole(string $function, string $signature): void
    {
        self::assertIdentifier($function);

        DB::statement(sprintf(
            'GRANT EXECUTE ON FUNCTION %s(%s) TO %s',
            self::quote($function),
            $signature,
            self::quote(self::runtimeRole()),
        ));
    }

    public static function policyName(string $table): string
    {
        return $table.'_tenant_isolation_policy';
    }

    /**
     * Reject anything that is not a plain lower-case SQL identifier.
     *
     * All inputs are compile-time constants from migrations, but the guard makes
     * `DB::statement` interpolation safe by construction.
     */
    private static function assertIdentifier(string $identifier): void
    {
        if (preg_match('/^[a-z_][a-z0-9_]*$/', $identifier) !== 1) {
            throw new RuntimeException(sprintf('Refusing to build SQL with unsafe identifier [%s].', $identifier));
        }
    }

    private static function quote(string $identifier): string
    {
        return '"'.$identifier.'"';
    }

    /**
     * Let the runtime role use the schema (needed once for every object the
     * migration creates). Idempotent and harmless if already granted.
     */
    public static function grantSchemaUsage(): void
    {
        DB::statement(sprintf(
            'GRANT USAGE ON SCHEMA public TO %s',
            self::quote(self::runtimeRole()),
        ));
    }
}
