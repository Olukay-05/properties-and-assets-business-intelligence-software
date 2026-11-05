<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Application-layer tenant filter (AD-3, defence in depth).
 *
 * PostgreSQL `FORCE ROW LEVEL SECURITY` is the authoritative barrier; this
 * global scope is the second layer, applied to every query on every model that
 * uses {@see HasTenant}. It is registered by the trait's `bootHasTenant()` so
 * there is no way to declare a tenant model and forget the scope.
 *
 * If no context is bound, {@see TenantContext::current()} throws rather than
 * returning all rows, so the failure mode is an aborted query, never a leak.
 *
 * Removal of this scope is deliberate and never done in a production code
 * path: tests use {@see withoutTenantScope()} and PostgreSQL itself still
 * enforces RLS underneath.
 *
 * @implements Scope<Model>
 */
final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(
            $model->getTable().'.organisation_id',
            '=',
            TenantContext::current()->organisationId,
        );
    }
}
