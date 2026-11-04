<?php

/*
|--------------------------------------------------------------------------
| Pest configuration
|--------------------------------------------------------------------------
|
| Binds every test in the Feature and Unit suites to the application TestCase,
| so the Laravel container, HTTP test helpers and Inertia assertions are
| available inside Pest closures.
|
| Feature tests run against SQLite in memory by default (see phpunit.xml).
| Database-backed suites (the tenant-isolation matrix, RLS behaviour) must run
| against real PostgreSQL + PostGIS, which is wired in a later story.
|
*/

pest()->extend(Tests\TestCase::class)
    ->in('Feature', 'Isolation');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function fixturesPath(string $path = ''): string
{
    return __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.($path === '' ? '' : DIRECTORY_SEPARATOR.$path);
}
