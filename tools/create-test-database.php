<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Provision the dedicated test database and database roles
|--------------------------------------------------------------------------
|
| Creates (idempotently) the two-role PostgreSQL setup Story 1.2 requires, and
| the dedicated test database the suite runs against:
|
|   pai_owner  schema owner / migration role (DDL, owns every table)
|   pai_app    application runtime role: NOSUPERUSER NOBYPASSRLS, owns nothing
|
| and the database property_asset_intelligence_test, owned by pai_owner, with
| a real RLS policy in force so the suite proves isolation and never silently
| falls back to a superuser connection.
|
| Run as a role allowed to create roles and databases (the local container's
| superuser, `pai`, by default):
|
|   php tools/create-test-database.php
|
| Environment:
|   TEST_DB_HOST / TEST_DB_PORT   connection details; they fall back to
|                                 DB_HOST / DB_PORT, then to 127.0.0.1:5433.
|                                 Falling back to DB_* matters: this script is
|                                 invoked both by a dedicated provisioning step
|                                 and by `composer isolation` via @db:setup, and
|                                 only some call sites set TEST_DB_*. Without the
|                                 DB_* fallback the script targeted 5433 while the
|                                 suite connected to DB_PORT, so provisioning and
|                                 the tests could silently talk to different servers.
|   PAI_OWNER_PASSWORD           password for pai_owner (default: local only)
|   PAI_APP_PASSWORD             password for pai_app   (default: local only)
|   ADMIN_DB_DATABASE            database to connect to   (default: postgres)
|
| The passwords default to obvious local placeholders, are never committed,
| and must be overridden outside a local machine (docs/security/ENV.md).
|
| This is local/CI tooling. Neon (staging/production) roles are provisioned in
| the Neon console; nothing here runs against a remote environment.
|
*/

$host = getenv('TEST_DB_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('TEST_DB_PORT') ?: getenv('DB_PORT') ?: '5433';
$adminDatabase = getenv('ADMIN_DB_DATABASE') ?: 'postgres';
$adminUser = getenv('ADMIN_DB_USERNAME') ?: 'pai';
$adminPassword = getenv('ADMIN_DB_PASSWORD') ?: 'secret';
$testDatabase = getenv('TEST_DB_DATABASE') ?: 'property_asset_intelligence_test';
$ownerRole = 'pai_owner';
$appRole = 'pai_app';
$ownerPassword = getenv('PAI_OWNER_PASSWORD') ?: 'pai_owner_local_only';
$appPassword = getenv('PAI_APP_PASSWORD') ?: 'pai_app_local_only';

$dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $adminDatabase);

try {
    $pdo = new PDO($dsn, $adminUser, $adminPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $exception) {
    fwrite(STDERR, "Unable to connect to PostgreSQL at {$host}:{$port}: {$exception->getMessage()}\n".PHP_EOL);
    fwrite(STDERR, 'Start the local stack first: docker compose up -d'.PHP_EOL);
    exit(1);
}

$quoteLiteral = static fn (string $value): string => "'".str_replace("'", "''", $value)."'";
$quoteIdentifier = static fn (string $value): string => '"'.str_replace('"', '""', $value).'"';

$steps = [];

// Roles. `CREATE ROLE` has no IF NOT EXISTS, so probe pg_roles first. The
// attributes are re-applied unconditionally so a role that drifted (for
// example, was made a SUPERUSER by hand) is corrected on every run.
$steps['role pai_owner'] = static function (PDO $pdo) use ($ownerRole, $ownerPassword, $quoteLiteral, $quoteIdentifier): string {
    $exists = $pdo->query('SELECT 1 FROM pg_roles WHERE rolname = '.$quoteLiteral($ownerRole))->fetchColumn();

    return $exists
        ? sprintf('ALTER ROLE %s WITH LOGIN PASSWORD %s', $quoteIdentifier($ownerRole), $quoteLiteral($ownerPassword))
        : sprintf('CREATE ROLE %s WITH LOGIN PASSWORD %s', $quoteIdentifier($ownerRole), $quoteLiteral($ownerPassword));
};

$steps['role pai_bootstrap'] = static function (PDO $pdo) use ($quoteLiteral, $quoteIdentifier): string {
    // Owner of the pre-tenant membership lookup function and the only role with
    // BYPASSRLS. NOLOGIN: it never opens a connection of its own.
    $exists = $pdo->query('SELECT 1 FROM pg_roles WHERE rolname = '.$quoteLiteral('pai_bootstrap'))->fetchColumn();

    return $exists
        ? sprintf('ALTER ROLE %s NOLOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE BYPASSRLS', $quoteIdentifier('pai_bootstrap'))
        : sprintf('CREATE ROLE %s NOLOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE BYPASSRLS', $quoteIdentifier('pai_bootstrap'));
};

$steps['role pai_app'] = static function (PDO $pdo) use ($appRole, $appPassword, $quoteLiteral, $quoteIdentifier): string {
    $exists = $pdo->query('SELECT 1 FROM pg_roles WHERE rolname = '.$quoteLiteral($appRole))->fetchColumn();

    // The security of the whole isolation model rests on these attributes:
    // the runtime role must never be a superuser and never bypass RLS.
    return $exists
        ? sprintf('ALTER ROLE %s WITH LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT NOBYPASSRLS PASSWORD %s', $quoteIdentifier($appRole), $quoteLiteral($appPassword))
        : sprintf('CREATE ROLE %s WITH LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT NOBYPASSRLS PASSWORD %s', $quoteIdentifier($appRole), $quoteLiteral($appPassword));
};

foreach ($steps as $label => $sql) {
    try {
        $pdo->exec($sql($pdo));
        echo "ok: {$label}".PHP_EOL;
    } catch (PDOException $exception) {
        fwrite(STDERR, "failed: {$label}: {$exception->getMessage()}\n");
        exit(1);
    }
}

// The test database is owned by pai_owner so migrations run as the schema
// owner while the suite itself connects as pai_app.
$exists = (bool) $pdo->query('SELECT 1 FROM pg_database WHERE datname = '.$quoteLiteral($testDatabase))->fetchColumn();

if ($exists) {
    echo "ok: database {$testDatabase} (already exists)".PHP_EOL;
} else {
    try {
        $pdo->exec(sprintf(
            'CREATE DATABASE %s OWNER %s',
            $quoteIdentifier($testDatabase),
            $quoteIdentifier($ownerRole),
        ));
        echo "ok: database {$testDatabase}".PHP_EOL;
    } catch (PDOException $exception) {
        fwrite(STDERR, "failed: database {$testDatabase}: {$exception->getMessage()}\n");
        exit(1);
    }
}

// The schema owner must be able to hand the bootstrap function to pai_bootstrap
// (ALTER ... OWNER TO requires membership). FORCE RLS is unaffected: the owner
// still cannot read `memberships` outside a bound tenant.
try {
    $pdo->exec(sprintf(
        'GRANT %s TO %s',
        $quoteIdentifier('pai_bootstrap'),
        $quoteIdentifier($ownerRole),
    ));
    echo "ok: {$ownerRole} may own {$appRole} objects via pai_bootstrap".PHP_EOL;
} catch (PDOException $exception) {
    fwrite(STDERR, "failed: grant pai_bootstrap to {$ownerRole}: {$exception->getMessage()}\n");
    exit(1);
}

// Schema-level grants must run inside the test database, not the administrative
// one. A database created by this script gets a `public` schema owned by
// `pg_database_owner`, and PostgreSQL 15+ no longer grants CREATE on `public`
// to PUBLIC, so pai_owner must be granted explicitly or every migration fails
// with "permission denied for schema public".
$schemaGrants = [
    "GRANT USAGE, CREATE ON SCHEMA public TO {$quoteIdentifier($ownerRole)}",
    "GRANT USAGE ON SCHEMA public TO {$quoteIdentifier($appRole)}",
    "GRANT USAGE, CREATE ON SCHEMA public TO {$quoteIdentifier('pai_bootstrap')}",
];

try {
    $testDsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $testDatabase);
    $testPdo = new PDO($testDsn, $adminUser, $adminPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    foreach ($schemaGrants as $sql) {
        $testPdo->exec($sql);
    }

    echo "ok: schema public granted to {$ownerRole}, {$appRole}, pai_bootstrap".PHP_EOL;
} catch (PDOException $exception) {
    fwrite(STDERR, "failed: schema grants on {$testDatabase}: {$exception->getMessage()}\n");
    exit(1);
}

echo PHP_EOL.'Roles and test database are ready.'.PHP_EOL;
echo 'Next: php artisan migrate --env=testing (or php artisan test, which migrates automatically)'.PHP_EOL;
