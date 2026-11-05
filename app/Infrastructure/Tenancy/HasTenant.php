<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy;

use App\Domain\Tenancy\Models\Organisation;
use App\Infrastructure\Tenancy\Exceptions\TenantContextNotBound;
use App\Infrastructure\Tenancy\Exceptions\TenantMismatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Marks a model as tenant-owned and wires application-layer isolation.
 *
 * Using `HasTenant` does three things, none of which can be forgotten
 * separately:
 *
 *  1. registers the global {@see TenantScope} on every query;
 *  2. stamps `organisation_id` from the bound context on create; and
 *  3. exposes the `organisation()` relation.
 *
 * Stamping rules on create:
 *
 *  - context bound, model carries no organisation_id: stamped from the context;
 *  - context bound, model carries a DIFFERENT organisation_id: aborts with
 *    {@see TenantMismatch} (CONFLICTING_STAMP). The value is never overwritten
 *    silently, so an attempt to write into another organisation is an error;
 *  - no context bound, model carries an explicit organisation_id: allowed, for
 *    provisioning (seeders, the invitation flow, tests) where a tenant cannot
 *    yet be bound. RLS still enforces the insert if a tenant is bound;
 *  - no context bound, model carries no organisation_id: aborts with
 *    {@see TenantContextNotBound} rather than guessing a tenant.
 *
 * `organisation_id` is never in `$fillable` on a tenant model, so forged
 * request input can neither stamp nor conflict with it: it never reaches the
 * model at all.
 *
 * @mixin Model
 */
trait HasTenant
{
    public static function bootHasTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            $context = app(TenantContextManager::class)->context();
            $existing = $model->getAttribute('organisation_id');

            if ($context === null) {
                if ($existing === null || $existing === '') {
                    throw TenantContextNotBound::make();
                }

                return;
            }

            if ($existing !== null && $existing !== '' && (string) $existing !== $context->organisationId) {
                throw TenantMismatch::for($model::class);
            }

            $model->setAttribute('organisation_id', $context->organisationId);
        });
    }

    /**
     * The owning organisation.
     *
     * `organisations` is a system identity table (no RLS, AD-4), so this
     * relation is not tenant-scoped. It is reachable only from a row the
     * current tenant can already see.
     *
     * @return BelongsTo<Organisation, $this>
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    /**
     * Run a query with the application-layer tenant scope removed.
     *
     * Only legitimate for bootstrap work under an explicit context. PostgreSQL
     * RLS remains in force underneath, so this still returns only the bound
     * tenant's rows.
     *
     * @template TReturn
     *
     * @param  callable(Builder<static>): TReturn  $callback
     * @return TReturn
     */
    public static function withoutTenantScope(callable $callback): mixed
    {
        return $callback(static::query()->withoutGlobalScope(TenantScope::class));
    }
}
