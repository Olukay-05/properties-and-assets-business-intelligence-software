<?php

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Isolation\IsolationTestCase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Pest configuration
|--------------------------------------------------------------------------
|
| Binds each suite to the application TestCase, so the Laravel container, HTTP
| test helpers and Inertia assertions are available inside Pest closures.
|
| Every suite runs against real PostgreSQL 17 + PostGIS under the non-owner
| runtime role `pai_app` (phpunit.xml, AD-19). The Isolation suite uses its own
| base case: it seeds two tenants and binds them explicitly, and it never
| stubs or removes a scope, because it tests the isolation boundaries.
|
*/

pest()->extend(TestCase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

pest()->extend(IsolationTestCase::class)
    ->in('Isolation');

// The gate-configuration suite reads committed configuration (composer.json,
// phpunit.xml, tests/Isolation) and runs the test runner to observe exit codes.
// It uses the framework base case, which boots the app but registers no
// database traits, so it runs without PostgreSQL: a guard that needed the
// database could only report a broken gate after that database was available.
pest()->extend(BaseTestCase::class)
    ->in('Gate');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function fixturesPath(string $path = ''): string
{
    return __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.($path === '' ? '' : DIRECTORY_SEPARATOR.$path);
}
