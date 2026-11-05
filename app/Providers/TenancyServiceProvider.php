<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\Tenancy\TenantContextManager;
use App\Infrastructure\Tenancy\TenantContextService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;

/**
 * Single composition point for tenant isolation (AD-3, AD-4).
 *
 * Registered here:
 * - TenantContextManager: scoped singleton, one context per request or job.
 * - TenantContextService: stateless singleton that resolves memberships.
 * - Migrations resolve to the schema-owner connection, never the runtime role.
 *
 * The `tenant` middleware alias lives in bootstrap/app.php, next to the rest
 * of the web stack.
 */
final class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Connection used for schema changes (the schema owner, pai_owner).
     */
    public const MIGRATION_CONNECTION = 'migrations';

    public function register(): void
    {
        $this->app->scoped(
            TenantContextManager::class,
            fn (Application $app): TenantContextManager => new TenantContextManager,
        );

        $this->app->singleton(TenantContextService::class);
    }

    public function boot(): void
    {
        $this->pinMigrationsToSchemaOwner();
    }

    /**
     * Point the migrator at the schema-owner connection by default.
     *
     * The runtime role owns nothing, so DDL would fail under it. An explicit
     * `--database` option still wins.
     */
    /**
     * Resolve the connection the migrator should use.
     */
    private function resolveMigrationConnection(ConnectionResolverInterface $resolver, ?string $connection): Connection
    {
        /** @var Connection $resolved */
        $resolved = $resolver->connection($connection ?? self::MIGRATION_CONNECTION);

        return $resolved;
    }

    private function pinMigrationsToSchemaOwner(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        Migrator::resolveConnectionsUsing($this->resolveMigrationConnection(...));
    }
}
