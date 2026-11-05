<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\Enums;

use App\Infrastructure\Tenancy\TenantContext;

/**
 * Lifecycle status of a user's membership in an organisation (AUTH.md §7).
 *
 * Only `active` memberships resolve to a {@see TenantContext}.
 * Suspending or deactivating a member is therefore immediate: their next
 * request resolves no context and runs no tenant query.
 */
enum MembershipStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Inactive = 'inactive';

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /**
     * The values the database column accepts.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
