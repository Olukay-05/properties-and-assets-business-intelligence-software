<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy;

use App\Infrastructure\Tenancy\Exceptions\TenantContextNotBound;
use Closure;

/**
 * The immutable, request-scoped tenant identity (AD-3).
 *
 * A context is only ever constructed from an authenticated user's active
 * `memberships` row, by {@see TenantContextService::resolveFor()}. Tenant
 * identity is therefore never taken from a request body, query string,
 * header or route parameter.
 *
 * A single instance is bound as a container singleton. Binding a second,
 * different context is an error rather than a silent rebind, so there is no
 * code path that can switch tenants mid-request.
 */
final class TenantContext
{
    /**
     * @param  list<string>  $roles  The membership's role set (union of abilities, AD-12). Stored, not authorised against here.
     * @param  list<string>|null  $clientScope  Permitted `client_accounts.id` values, or null for organisation-wide access (AD-5).
     */
    private function __construct(
        public readonly string $membershipId,
        public readonly string $organisationId,
        public readonly string $userId,
        public readonly array $roles,
        public readonly ?array $clientScope,
    ) {}

    /**
     * @param  list<string>  $roles
     * @param  list<string>|null  $clientScope
     */
    public static function fromMembership(
        string $membershipId,
        string $organisationId,
        string $userId,
        array $roles,
        ?array $clientScope,
    ): self {
        return new self(
            membershipId: $membershipId,
            organisationId: $organisationId,
            userId: $userId,
            roles: $roles,
            clientScope: $clientScope,
        );
    }

    /**
     * The roles reported by the context, for later policy authorisation (Story 1.4).
     *
     * @return list<string>
     */
    public function roles(): array
    {
        return $this->roles;
    }

    /**
     * The client-account restriction, or `null` when the member is unrestricted.
     *
     * @return list<string>|null
     */
    public function clientScope(): ?array
    {
        return $this->clientScope;
    }

    public function isRestrictedToClientAccounts(): bool
    {
        return $this->clientScope !== null;
    }

    /**
     * Whether two contexts describe the same membership and tenant.
     */
    public function is(self $other): bool
    {
        return $this->membershipId === $other->membershipId
            && $this->organisationId === $other->organisationId
            && $this->userId === $other->userId;
    }

    /**
     * Resolve the current context or fail loudly.
     *
     * Called by every tenant-owned model. A query that reaches a tenant model
     * without a bound context is a defect, so it aborts instead of running
     * unscoped.
     */
    public static function current(): self
    {
        $context = app(TenantContextManager::class)->context();

        if ($context === null) {
            throw TenantContextNotBound::make();
        }

        return $context;
    }

    public static function bound(): bool
    {
        return app(TenantContextManager::class)->context() !== null;
    }

    /**
     * Run a callback with the context bound, then restore the previous binding.
     *
     * This is the seam for entry points that are not an HTTP request (later
     * queued jobs and console commands, AD-14). The previous binding is always
     * restored, so a long-running worker never inherits the previous job's
     * tenant.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function run(self $context, Closure $callback): mixed
    {
        $manager = app(TenantContextManager::class);
        $previous = $manager->context();

        $manager->set($context);

        try {
            return $callback();
        } finally {
            $manager->set($previous);
        }
    }
}
