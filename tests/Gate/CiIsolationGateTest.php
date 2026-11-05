<?php

use Symfony\Component\Process\Process;

/*
|--------------------------------------------------------------------------
| CI isolation gate integrity (AGENTS.md §6, AD-19)
|--------------------------------------------------------------------------
|
| The tenant-isolation suite is a merge gate. Three separate links hold it in
| place, and each one can be broken without any other test noticing:
|
|   1. composer ci:check must invoke the `isolation` script before the general
|      suite, and that script must run the Isolation suite explicitly;
|   2. phpunit.xml must declare an `Isolation` testsuite that points at
|      tests/Isolation, and that directory must hold real test files;
|   3. a suite that runs zero tests must exit non-zero.
|
| Link 3 is the one that is easy to get wrong. `failOnEmptyTestSuite="true"` in
| phpunit.xml looks like it provides that guarantee, but measurement shows the
| runner already exits 1 for an empty suite with or without the attribute (see
| the comment in phpunit.xml). So the guarantee is pinned here BEHAVIOURALLY,
| by running a real suite and reading its exit code, instead of by asserting
| that a config string is present. A config-string assertion would pass while
| the behaviour was absent, which is exactly the failure this suite prevents.
|
| These tests do not touch the database. The gate must be checkable before a
| PostGIS container exists, and a guard that needed PostgreSQL could only
| report a broken gate after the database it guards was already available.
|
*/

/**
 * Read a file from the project root, failing the test if it is unreadable.
 */
function gateRead(string $path): string
{
    $full = base_path($path);
    $contents = is_file($full) ? file_get_contents($full) : false;

    expect($contents)->not->toBeFalse(sprintf('%s could not be read from the project root.', $path));

    return (string) $contents;
}

/**
 * Decode a JSON file from the project root, failing the test if it is invalid.
 *
 * @return array<string, mixed>
 */
function gateJson(string $path): array
{
    $decoded = json_decode(gateRead($path), true, 512, JSON_THROW_ON_ERROR);

    expect($decoded)->toBeArray(sprintf('%s did not decode to an object.', $path));

    return $decoded;
}

/**
 * A Composer script as a flat list of command strings.
 *
 * @return array<int, string>
 */
function gateScriptLines(string $script): array
{
    $scripts = gateJson('composer.json')['scripts'] ?? [];

    expect($scripts)->toBeArray('composer.json must define a scripts object.');
    expect(array_key_exists($script, $scripts))->toBeTrue(sprintf('composer.json has no `%s` script.', $script));

    $raw = $scripts[$script];

    expect($raw)->toBeArray(sprintf('The `%s` script must be an array of commands.', $script));

    return array_values(array_filter($raw, 'is_string'));
}

/**
 * Assert every fragment appears somewhere in a Composer script.
 *
 * @param  array<int, string>  $expected
 */
function gateExpectScriptContains(string $script, array $expected): void
{
    $joined = implode(' | ', gateScriptLines($script));

    foreach ($expected as $fragment) {
        expect($joined)->toContain($fragment);
    }
}

test('the isolation script provisions the roles and runs the isolation suite', function () {
    gateExpectScriptContains('isolation', ['--testsuite=Isolation']);

    // Roles must exist before the suite connects as pai_app. The gate may call
    // the shared `db:setup` script or the tool directly; follow the indirection
    // so the assertion is not pinned to one spelling.
    $lines = gateScriptLines('isolation');
    $joined = implode(' | ', $lines);
    $provisions = str_contains($joined, 'tools/create-test-database.php')
        || str_contains($joined, '@db:setup');

    expect($provisions)->toBeTrue('The isolation gate must provision the database roles first.');
    gateExpectScriptContains('db:setup', ['tools/create-test-database.php']);

    // The gate must never neutralise itself by excluding suites.
    foreach ($lines as $line) {
        expect($line)->not->toContain('--exclude-testsuite');
    }
});

test('ci:check runs the isolation gate before the general suite', function () {
    $lines = gateScriptLines('ci:check');

    $isolationAt = array_search('@isolation', $lines, true);
    $testAt = array_search('@test', $lines, true);

    expect($isolationAt)->not->toBeFalse('ci:check must invoke @isolation.');
    expect($testAt)->not->toBeFalse('ci:check must still invoke @test.');

    // The isolation gate must run before the general PHP suite, so a
    // cross-tenant leak is reported ahead of unrelated PHP test failures.
    // Note this does NOT put it first in ci:check: pnpm check and
    // types:check still run before it.
    expect($isolationAt)->toBeLessThan($testAt);
});

test('CI runs the suite as the non-owner runtime role', function () {
    // The isolation suite proves nothing if CI connects as a superuser or as
    // the schema owner: FORCE RLS would still bind the owner, but a superuser
    // bypasses RLS entirely and every assertion could pass vacuously. Pointing
    // the workflow at postgres/postgres would leave this suite green while the
    // tenant boundary was no longer being exercised (AGENTS.md §6, AD-4).
    $workflow = gateRead('.github/workflows/tests.yml');

    $expected = [
        'DB_USERNAME' => 'pai_app',
        'DB_MIGRATION_USERNAME' => 'pai_owner',
        'DB_CONNECTION' => 'pgsql',
    ];

    foreach ($expected as $key => $value) {
        expect(preg_match('/^\s*'.$key.':\s*'.preg_quote($value, '/').'\s*$/m', $workflow))
            ->toBe(1, "CI must set {$key}: {$value} so the suite runs as the runtime role.");
    }

    // DB_URL would silently override every discrete DB_* value.
    expect(preg_match('/^\s*DB_URL:\s*\S+/m', $workflow))
        ->toBe(0, 'CI must not set DB_URL; it would override the role and port chosen above.');
});

test('phpstan is given enough memory to finish in CI', function () {
    // The gate is only real if ci:check can reach it. PHPStan aborted at the
    // 128M default before analysing anything, so ci:check never got past the
    // type check. The limit is pinned in the script, not a php.ini, because the
    // problem is the runner's default, which this repository does not control.
    gateExpectScriptContains('types:check', ['phpstan', '--memory-limit']);
});

test('phpunit declares an Isolation testsuite pointing at tests/Isolation', function () {
    $xml = simplexml_load_file(base_path('phpunit.xml'));

    expect($xml)->not->toBeFalse('phpunit.xml did not parse.');

    $suites = [];
    foreach ($xml->testsuites->testsuite as $suite) {
        $suites[(string) $suite['name']] = trim((string) $suite->directory);
    }

    expect(array_key_exists('Isolation', $suites))->toBeTrue('phpunit.xml must declare an Isolation testsuite.');
    expect($suites['Isolation'])->toBe('tests/Isolation');
});

test('tests/Isolation contains real isolation tests', function () {
    $files = glob(base_path('tests/Isolation/*Test.php')) ?: [];

    expect($files)->not->toBeEmpty(
        'tests/Isolation has no *Test.php files, so the gate would run zero tests.',
    );

    // Count the cases the RUNNER registers, not the tokens in the file text.
    // Counting `it(`/`test(` substrings is defeated by a retained comment or a
    // string literal: gut the suite, leave the words behind, and the counter
    // still reports a healthy number. `--list-tests` asks the runner itself, so
    // only genuinely registered cases count.
    //
    // IsolationTestCase connects to PostgreSQL, so the list is produced with
    // --list-tests, which enumerates without executing setUp().
    $process = new Process(
        [PHP_BINARY, base_path('vendor/bin/pest'), '--testsuite=Isolation', '--list-tests'],
        base_path(),
        null,
        null,
        timeout: 120,
    );
    $process->run();

    // Pest prefixes listed tests with `P\`, so match the namespace anywhere in
    // the line rather than anchoring to `- Tests\`.
    $listed = preg_grep('/\\\\Tests\\\\Isolation\\\\/', explode("\n", $process->getOutput())) ?: [];

    expect(count($listed))->toBeGreaterThanOrEqual(
        20,
        sprintf(
            'The Isolation suite registers only %d test cases (runner output: %s). '
            .'Story 1.2 shipped 31; the gate refuses a suite gutted below the isolation matrix.',
            count($listed),
            trim($process->getOutput()) === '' ? 'no output' : 'see --list-tests',
        ),
    );
});

test('a suite that runs zero tests exits non-zero', function () {
    // The behavioural proof behind "the gate cannot pass vacuously".
    //
    // Two invocation shapes are covered, because they can regress separately:
    //   a) the shape the gate actually uses — `artisan test --testsuite=…` with
    //      a suite that resolves to no test files;
    //   b) the shape with no suite selection at all, which is where an
    //      "empty is fine" default would silently return zero.
    //
    // Each run uses a throwaway config and a unique directory, so concurrent
    // Gate runs cannot collide, and the fixture cannot leak into the repo.
    $directory = sys_get_temp_dir().'/pai-empty-suite-'.bin2hex(random_bytes(8));
    $config = $directory.'.xml';

    expect(mkdir($directory, 0777, true))->toBeTrue('Could not create the empty-suite probe directory.');

    $bootstrap = base_path('vendor/autoload.php');
    $xml = <<<XML
    <?xml version="1.0"?>
    <phpunit bootstrap="{$bootstrap}">
        <testsuites>
            <testsuite name="EmptyProbe">
                <directory>{$directory}</directory>
            </testsuite>
        </testsuites>
    </phpunit>
    XML;

    expect(file_put_contents($config, $xml))->not->toBeFalse('Could not write the empty-suite probe config.');

    try {
        // (a) The gate's own shape: a named suite that contains no tests.
        $selected = new Process(
            [PHP_BINARY, base_path('artisan'), 'test', '--testsuite=EmptyProbe', '--configuration', $config],
            base_path(),
            null,
            null,
            timeout: 120,
        );
        $selected->run();

        expect($selected->getExitCode())->not->toBe(
            0,
            "A named testsuite with zero tests exited 0. The isolation gate could pass without running anything.\n"
            .$selected->getOutput().$selected->getErrorOutput(),
        );

        // (b) No selection at all: an empty run must not be reported as success.
        $unselected = new Process(
            [PHP_BINARY, base_path('artisan'), 'test', '--configuration', $config],
            base_path(),
            null,
            null,
            timeout: 120,
        );
        $unselected->run();

        expect($unselected->getExitCode())->not->toBe(
            0,
            "A test run that selected no tests exited 0. A skipped gate would read as green.\n"
            .$unselected->getOutput().$unselected->getErrorOutput(),
        );
    } finally {
        @unlink($config);
        @rmdir($directory);
    }
});
