#!/bin/bash
# Provision the two-role PostgreSQL setup required by docs/security/RLS-POLICIES.md §3.
#
# Runs once, on first boot of an empty pai-postgres volume (docker-entrypoint-initdb.d).
# Compose creates the container superuser (POSTGRES_USER, `pai`); this script
# creates the roles the application actually uses:
#
#   pai_owner  schema owner / migration role — runs `php artisan migrate`, owns
#              every table and policy, and is NOT used by the application.
#   pai_app    application runtime role — NOSUPERUSER NOCREATEDB NOCREATEROLE
#              NOBYPASSRLS, owns nothing. Because the runtime never owns a table,
#              and every tenant table is FORCE ROW LEVEL SECURITY, RLS applies
#              to it with no exception.
#
# Table-level privileges are granted by the migrations, which run as pai_owner,
# so grants can never drift from the schema.
#
# Passwords come from the environment (compose reads them from .env). They are
# local placeholders only and are never committed.
#
# To re-run after changing role attributes, recreate the volume:
#   docker compose down -v && docker compose up -d

set -euo pipefail

: "${POSTGRES_USER:?POSTGRES_USER must be set}"
: "${POSTGRES_DB:?POSTGRES_DB must be set}"
: "${PAI_OWNER_PASSWORD:?PAI_OWNER_PASSWORD must be set}"
: "${PAI_APP_PASSWORD:?PAI_APP_PASSWORD must be set}"

psql -v ON_ERROR_STOP=1 \
    --username "$POSTGRES_USER" \
    --dbname "$POSTGRES_DB" \
    -v owner_password="$PAI_OWNER_PASSWORD" \
    -v app_password="$PAI_APP_PASSWORD" \
    <<'SQL'
-- CREATE ROLE has no IF NOT EXISTS, so probe pg_roles first. Create with the
-- security-relevant attributes; the ALTERs below re-apply them (and the
-- password) on every run so an existing volume is corrected too.
SELECT 'CREATE ROLE pai_owner WITH LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS'
WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'pai_owner')\gexec

SELECT 'CREATE ROLE pai_app WITH LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS'
WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'pai_app')\gexec

-- pai_bootstrap owns only the pre-tenant membership lookup function. It is the
-- single role in the cluster with BYPASSRLS and it cannot log in, so it exists
-- solely as the SECURITY DEFINER identity for that one narrow read.
SELECT 'CREATE ROLE pai_bootstrap NOLOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE BYPASSRLS'
WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'pai_bootstrap')\gexec

\set ON_ERROR_STOP on
ALTER ROLE pai_owner WITH LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS PASSWORD :'owner_password';
ALTER ROLE pai_app   WITH LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS PASSWORD :'app_password';

-- The runtime role may use the schema but owns nothing: it cannot create,
-- alter or drop objects, and it cannot disable row-level security.
GRANT USAGE ON SCHEMA public TO pai_app;

-- The schema owner must be able to run DDL. Do not rely on being a superuser
-- or on the schema's original owner: if `public` is ever dropped and recreated
-- (a routine reset: DROP SCHEMA public CASCADE; CREATE SCHEMA public), the new
-- schema is owned by whoever ran the reset and pai_owner would immediately hit
-- "permission denied for schema public" on the next migration. Grant it
-- explicitly so a reset is survivable.
GRANT USAGE, CREATE ON SCHEMA public TO pai_owner;

-- The schema owner must also be able to hand the bootstrap function to
-- pai_bootstrap: ALTER ... OWNER TO requires membership in the target role,
-- and the receiving role needs CREATE on the schema that holds the function.
-- This grants no data-read ability; FORCE RLS still applies to pai_owner.
GRANT pai_bootstrap TO pai_owner;
GRANT USAGE, CREATE ON SCHEMA public TO pai_bootstrap;
SQL

echo "pai_owner and pai_app roles provisioned."
