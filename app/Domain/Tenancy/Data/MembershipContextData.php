<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\Data;

use App\Domain\Tenancy\Enums\MembershipStatus;
use App\Infrastructure\Tenancy\TenantContext;
use Spatie\LaravelData\Data;

/**
 * The narrow projection returned by the `resolve_user_memberships()`
 * `SECURITY DEFINER` function, and the only data that can build a
 * {@see TenantContext}.
 *
 * It carries exactly the five columns the resolved decisions allow, so the
 * pre-tenant bootstrap path cannot read any other membership or organisation
 * column even though it bypasses RLS.
 */
final class MembershipContextData extends Data
{
    /**
     * @param  list<string>  $roles
     * @param  list<string>|null  $client_scope
     */
    public function __construct(
        public string $membership_id,
        public string $organisation_id,
        public string $user_id,
        public array $roles,
        public ?array $client_scope,
        public MembershipStatus $status,
    ) {}
}
