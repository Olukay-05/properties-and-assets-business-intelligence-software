<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy;

use App\Domain\Tenancy\Data\MembershipContextData;
use App\Domain\Tenancy\Enums\MembershipStatus;
use App\Infrastructure\Tenancy\Exceptions\MembershipNotResolvable;
use Illuminate\Support\Facades\DB;

/**
 * Resolves which organisation an authenticated user may act as (AD-3).
 *
 * `memberships` is RLS-protected, so it cannot be read before `app.org_id` is
 * bound, and `app.org_id` comes from the membership: a chicken-and-egg that is
 * solved with one narrow, audited hole.
 *
 * That hole is the `resolve_user_memberships(uuid)` `SECURITY DEFINER`
 * function owned by the schema owner (`pai_owner`). It returns only
 * membership id, organisation id, user id, roles, client scope and status for
 * a single user, and only for memberships in an active organisation. The
 * runtime role `pai_app` holds `EXECUTE` on that function and nothing else.
 *
 * Critically, no policy is added to `memberships` granting a user access by
 * `app.user_id`: the table's policy stays strictly tenant-scoped, so the
 * function is genuinely the only pre-tenant read path.
 *
 * The user id always comes from the authenticated session, never from input.
 */
final class TenantContextService
{
    public const RESOLVE_FUNCTION = 'resolve_user_memberships';

    /**
     * The session key holding the active membership id for a multi-organisation
     * user (AUTH.md §8). It is a server-side selection that is re-verified
     * against the membership list on every request, never a tenant id.
     */
    public const SESSION_MEMBERSHIP_KEY = 'tenancy.active_membership_id';

    /**
     * Resolve the context for a user, or fail closed.
     *
     * @throws MembershipNotResolvable when there is no active membership, or when
     *                                 the user holds several and none is selected
     *                                 for this session.
     */
    public function resolveFor(string $userId, ?string $preferredMembershipId = null): TenantContext
    {
        $memberships = $this->membershipsFor($userId);

        $active = array_values(array_filter(
            $memberships,
            fn (MembershipContextData $membership): bool => $membership->status->isActive(),
        ));

        if ($active === []) {
            throw $memberships === []
                ? MembershipNotResolvable::noMembership()
                : MembershipNotResolvable::inactiveMembership();
        }

        if ($preferredMembershipId !== null) {
            $selected = $this->find($active, $preferredMembershipId);

            if ($selected === null) {
                // A stale or forged session selection is treated exactly like no
                // active membership: never silently fall back to another tenant.
                throw MembershipNotResolvable::inactiveMembership();
            }

            return $this->toContext($selected);
        }

        if (count($active) > 1) {
            // Phase 1 treats each FM company as an independent tenant. Guessing
            // which organisation a multi-membership user means would be a silent
            // tenant switch, so the session must have selected one explicitly.
            throw MembershipNotResolvable::ambiguousMembership();
        }

        return $this->toContext($active[0]);
    }

    /**
     * Every membership row for the user, regardless of status, as projected by
     * the security-definer function.
     *
     * @return list<MembershipContextData>
     */
    public function membershipsFor(string $userId): array
    {
        /** @var list<object{membership_id: string, organisation_id: string, user_id: string, roles: string|null, client_scope: string|null, status: string}> $rows */
        $rows = DB::select(
            sprintf('select * from %s(?) ', self::RESOLVE_FUNCTION),
            [$userId],
        );

        return array_map(
            fn (object $row): MembershipContextData => MembershipContextData::from([
                'membership_id' => (string) $row->membership_id,
                'organisation_id' => (string) $row->organisation_id,
                'user_id' => (string) $row->user_id,
                'roles' => $this->decodeJsonArray($row->roles),
                'client_scope' => $this->decodeNullableJsonArray($row->client_scope),
                'status' => MembershipStatus::from($row->status),
            ]),
            $rows,
        );
    }

    private function toContext(MembershipContextData $membership): TenantContext
    {
        return TenantContext::fromMembership(
            membershipId: $membership->membership_id,
            organisationId: $membership->organisation_id,
            userId: $membership->user_id,
            roles: $membership->roles,
            clientScope: $membership->client_scope,
        );
    }

    /**
     * @param  list<MembershipContextData>  $memberships
     */
    private function find(array $memberships, string $membershipId): ?MembershipContextData
    {
        foreach ($memberships as $membership) {
            if (hash_equals($membership->membership_id, $membershipId)) {
                return $membership;
            }
        }

        return null;
    }

    /**
     * Decode a `json`/`jsonb` column returned as a string or array by PDO.
     *
     * @return list<string>
     */
    private function decodeJsonArray(mixed $value): array
    {
        return array_map(
            static fn (mixed $item): string => (string) $item,
            $this->decodeNullableJsonArray($value) ?? [],
        );
    }

    /**
     * @return list<string>|null
     */
    private function decodeNullableJsonArray(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $decoded = is_array($value) ? $value : json_decode((string) $value, true);

        if (! is_array($decoded)) {
            return null;
        }

        return array_values(array_map(static fn (mixed $item): string => (string) $item, $decoded));
    }
}
