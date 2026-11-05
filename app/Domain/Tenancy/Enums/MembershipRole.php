<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\Enums;

/**
 * The six Phase 1 application roles (AUTH.md §9.1, AD-12).
 *
 * Role *abilities* are not modelled here: the permission union and the
 * Policies that consume it are Story 1.4. This enum defines the stored
 * vocabulary so `memberships.roles` is constrained and typo-safe.
 */
enum MembershipRole: string
{
    case PlatformAdmin = 'platform_admin';
    case OrganisationOwner = 'organisation_owner';
    case OperationsManager = 'operations_manager';
    case SiteSupervisor = 'site_supervisor';
    case Technician = 'technician';
    case Contractor = 'contractor';

    /**
     * The values the `memberships.roles` column accepts.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $role): string => $role->value, self::cases());
    }
}
