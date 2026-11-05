<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;
use PDOException;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Fail once, clearly, if the test database was never provisioned.
     *
     * Without this check an unprovisioned database produces one "could not find
     * driver"-style failure per test, which reads like 72 broken tests rather
     * than one missing setup step. The message is the step to run.
     */
    protected function setUpTraits()
    {
        $this->assertTestDatabaseIsProvisioned();

        return parent::setUpTraits();
    }

    private function assertTestDatabaseIsProvisioned(): void
    {
        $connection = (string) config('database.default');
        $config = config("database.connections.{$connection}");

        if (! is_array($config) || ($config['driver'] ?? null) !== 'pgsql') {
            return;
        }

        try {
            $this->app['db']->connection($connection)->getPdo();
        } catch (PDOException $exception) {
            $database = (string) ($config['database'] ?? '?');

            throw new RuntimeException(
                "Cannot connect to the test database [{$database}]: {$exception->getMessage()}\n\n"
                ."Provision it first (creates the roles and the database, safe to re-run):\n"
                .'  php tools/create-test-database.php',
                0,
                $exception,
            );
        }
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }

    /**
     * Schema resets run on the schema-owner connection.
     *
     * The runtime role owns nothing and holds no DDL privileges (AD-4), so it
     * cannot drop or create tables. Tests themselves still run as the runtime
     * role under FORCE RLS.
     *
     * @return array<string, mixed>
     */
    protected function migrateFreshUsing()
    {
        return [
            '--database' => 'migrations',
            '--seed' => false,
        ];
    }
}
