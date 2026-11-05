<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\Enums;

/**
 * Lifecycle status of a tenant organisation.
 *
 * Only `active` organisations resolve to a tenant context; an organisation
 * that is suspended (or being offboarded under NDPA retention) cannot be
 * operated against even by an otherwise-active member.
 */
enum OrganisationStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';

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
