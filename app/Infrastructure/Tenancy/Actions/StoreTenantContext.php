<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy\Actions;

use App\Infrastructure\Tenancy\TenantContext;
use App\Infrastructure\Tenancy\TenantScope;
use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

/**
 * Runs a unit of work inside a database transaction that is bound to one tenant.
 *
 * Two things happen together, and they must never be separated:
 *
 *  1. the immutable {@see TenantContext} is bound for the duration, so
 *     application-layer scopes ({@see TenantScope})
 *     and the `creating` stamp use the same tenant as the database; and
 *  2. `set_config('app.org_id', <uuid>, true)` is issued on that transaction's
 *     connection, so PostgreSQL `FORCE ROW LEVEL SECURITY` filters the rows.
 *
 * The third argument `true` scopes the setting to the current transaction
 * (`SET LOCAL` semantics). Production connects through PgBouncer in
 * transaction pooling mode (AD-3), where a session-level `SET` would leak
 * tenant state onto a pooled connection used by another request; a
 * transaction-scoped setting is cleared by PostgreSQL the moment the
 * transaction commits or rolls back.
 */
final class StoreTenantContext
{
    public const SESSION_VARIABLE = 'app.org_id';

    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function handle(TenantContext $context, Closure $callback, ?string $connection = null): mixed
    {
        $connection = $connection ?? $this->defaultConnectionName();

        return DB::connection($connection)->transaction(
            function (ConnectionInterface $db) use ($context, $callback): mixed {
                $this->bindSessionVariable($db, $context);

                return TenantContext::run($context, $callback);
            },
            // Nested inside an enclosing transaction the savepoint is used and the
            // setting stays bound to the outer transaction, which is correct: the
            // tenant does not change for the lifecycle of that transaction.
            attempts: 1,
        );
    }

    /**
     * Bind `app.org_id` to the current transaction on the given connection.
     */
    public function bindSessionVariable(ConnectionInterface $db, TenantContext $context): void
    {
        // The parameter is bound by the driver, never interpolated, so the value
        // cannot be used to smuggle additional statements or settings.
        $db->selectOne(
            'select set_config(?, ?, true) as bound',
            [self::SESSION_VARIABLE, $context->organisationId],
        );
    }

    private function defaultConnectionName(): string
    {
        return (string) config('database.default');
    }
}
