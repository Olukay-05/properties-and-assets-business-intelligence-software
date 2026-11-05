<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy;

use App\Infrastructure\Tenancy\Actions\StoreTenantContext;
use App\Infrastructure\Tenancy\Exceptions\MembershipNotResolvable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds the tenant for an authenticated request (AD-3, AD-4).
 *
 * Registered as the `tenant` middleware alias and appended to the `web` group
 * so it runs after `StartSession` (the session is needed to read the selected
 * membership) and after the remaining web middleware. It runs for every web
 * request but only acts when a user is authenticated, so guest and
 * pre-authentication requests (login, password reset) are untouched.
 *
 * What it does, in order:
 *  1. requires an authenticated user (otherwise passes through untouched);
 *  2. resolves the context from the user's active membership via
 *     {@see TenantContextService} (never from request input);
 *  3. binds it for the rest of the request; and
 *  4. wraps the rest of the pipeline in one database transaction with
 *     `set_config('app.org_id', ..., true)` bound for its lifetime, so every
 *     query in the request runs under PostgreSQL RLS.
 *
 * A user with no membership, or no *active* membership, is rejected here with
 * a 403 before any tenant query can run.
 *
 * @see StoreTenantContext for why the
 *      setting is transaction-scoped rather than session-scoped.
 */
final class ResolveTenantContext
{
    public function __construct(
        private readonly TenantContextService $resolver,
        private readonly StoreTenantContext $storeTenantContext,
        private readonly TenantContextManager $manager,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        try {
            $context = $this->resolver->resolveFor(
                $user->getAuthIdentifier(),
                $request->hasSession() ? $request->session()->get(TenantContextService::SESSION_MEMBERSHIP_KEY) : null,
            );
        } catch (MembershipNotResolvable $exception) {
            // Fail closed: no context is bound and no tenant query runs. The
            // response is a plain access-denied message, identical regardless of
            // the reason, so it cannot be used to probe organisational structure.
            abort(403, 'You do not have access to a workspace.');
        }

        $this->manager->bind($context);

        return $this->storeTenantContext->handle($context, fn (): Response => $next($request));
    }

    /**
     * Clear the request-scoped state once the response has been sent.
     *
     * The transaction has already committed or rolled back, so PostgreSQL has
     * discarded `app.org_id`. Dropping the in-memory context as well keeps a
     * long-lived worker (Octane, test process) from retaining a tenant between
     * requests.
     */
    public function terminate(Request $request, Response $response): void
    {
        $this->manager->forget();
    }
}
