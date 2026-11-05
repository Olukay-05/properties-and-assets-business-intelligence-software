<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy;

use App\Infrastructure\Tenancy\Exceptions\TenantContextAlreadyBound;
use App\Providers\TenancyServiceProvider;
use Closure;

/**
 * Holds at most one {@see TenantContext} for the current unit of work.
 *
 * Bound as a container singleton (see {@see TenancyServiceProvider}).
 * It is the only mutable piece of tenancy state; `TenantContext` itself is
 * immutable and every caller reads it through this manager.
 *
 * Single-binding is deliberate: within one request (or one queued job) the
 * tenant is fixed. Attempting to bind a second, different context throws, so a
 * bug in a later story can never silently switch tenant mid-request.
 */
final class TenantContextManager
{
    private ?TenantContext $context = null;

    public function context(): ?TenantContext
    {
        return $this->context;
    }

    public function isBound(): bool
    {
        return $this->context !== null;
    }

    /**
     * Bind the context, or re-bind the same context idempotently.
     *
     * @throws TenantContextAlreadyBound when a different context is already bound.
     */
    public function bind(TenantContext $context): void
    {
        if ($this->context !== null && ! $this->context->is($context)) {
            throw TenantContextAlreadyBound::for($this->context, $context);
        }

        $this->context = $context;
    }

    public function forget(): void
    {
        $this->context = null;
    }

    /**
     * Run a callback with the given context bound, always restoring afterwards.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function run(TenantContext $context, Closure $callback): mixed
    {
        return TenantContext::run($context, $callback);
    }

    /**
     * Internal seam used by {@see TenantContext::run()} to swap and restore.
     *
     * @internal
     */
    public function set(?TenantContext $context): void
    {
        $this->context = $context;
    }
}
