<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy\Exceptions;

use App\Infrastructure\Tenancy\TenantContext;
use LogicException;

/**
 * A tenant-owned model was queried or written without a bound `TenantContext`.
 *
 * This is a programming error, not a user error: every entry point that can
 * reach a tenant model must bind the context first (the tenancy middleware for
 * HTTP, {@see TenantContext::run()} elsewhere).
 * Failing loudly prevents an unscoped query from ever reaching PostgreSQL.
 */
final class TenantContextNotBound extends LogicException
{
    public static function make(): self
    {
        return new self(
            'No tenant context is bound. Tenant models may only be used inside an '.
            'authenticated request with an active membership, or inside TenantContext::run().',
        );
    }
}
