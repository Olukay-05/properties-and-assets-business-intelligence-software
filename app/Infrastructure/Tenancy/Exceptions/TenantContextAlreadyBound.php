<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy\Exceptions;

use App\Infrastructure\Tenancy\TenantContext;
use LogicException;

/**
 * A different tenant was already bound for the current unit of work.
 *
 * The tenant is fixed for the lifetime of a request (or job). Rebinding to
 * another organisation is refused rather than allowed silently, because a
 * mid-request tenant switch is always either a bug or an escalation.
 */
final class TenantContextAlreadyBound extends LogicException
{
    public static function for(TenantContext $current, TenantContext $requested): self
    {
        return new self(sprintf(
            'A tenant context for organisation [%s] is already bound; refusing to rebind to [%s].',
            $current->organisationId,
            $requested->organisationId,
        ));
    }
}
