<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy\Exceptions;

use App\Infrastructure\Tenancy\HasTenant;
use RuntimeException;

/**
 * A tenant-owned model carries an `organisation_id` that conflicts with the
 * bound tenant.
 *
 * Raised by the `creating` hook in {@see HasTenant}
 * (CONFLICTING_STAMP): creation is aborted with this domain exception. The
 * stamp is never silently overwritten, so an attempt to write into another
 * organisation surfaces as a real error instead of a silent redirect.
 *
 * `getCode()` is the stable, machine-readable domain code
 * (AGENTS.md / AUTH.md domain-exception style); the message never contains
 * the conflicting value, so it cannot be used to probe other tenants' UUIDs.
 */
final class TenantMismatch extends RuntimeException
{
    public const CODE = 'tenant_mismatch';

    public static function for(string $modelClass): self
    {
        return new self(
            sprintf('Refusing to create [%s]: its organisation_id conflicts with the bound tenant context.', class_basename($modelClass)),
            0,
        );
    }
}
