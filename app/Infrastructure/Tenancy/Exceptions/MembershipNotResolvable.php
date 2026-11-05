<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy\Exceptions;

use RuntimeException;

/**
 * An authenticated user has no membership that can open a tenant context
 * (NO_MEMBERSHIP / INACTIVE_MEMBERSHIP).
 *
 * The request is rejected before any tenant query runs. Rendered as a plain
 * access-denied response: it never says whether the organisation exists,
 * which memberships exist, or why a specific one was refused.
 */
final class MembershipNotResolvable extends RuntimeException
{
    public const NO_MEMBERSHIP = 'no_membership';

    public const INACTIVE_MEMBERSHIP = 'inactive_membership';

    public const AMBIGUOUS_MEMBERSHIP = 'ambiguous_membership';

    private function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message, 0);
    }

    public static function noMembership(): self
    {
        return new self(
            self::NO_MEMBERSHIP,
            'This account has no active organisation membership.',
        );
    }

    public static function inactiveMembership(): self
    {
        return new self(
            self::INACTIVE_MEMBERSHIP,
            'This account\'s organisation membership is not active.',
        );
    }

    public static function ambiguousMembership(): self
    {
        return new self(
            self::AMBIGUOUS_MEMBERSHIP,
            'This account belongs to more than one organisation and no active organisation is selected for this session.',
        );
    }
}
