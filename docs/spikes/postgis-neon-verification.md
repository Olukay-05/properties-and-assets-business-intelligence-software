# Spike: verify PostGIS on Neon (D1)

**Goal:** confirm PostGIS works on the chosen Neon plan *before* the schema is locked. Run this in a throwaway Neon project or a dedicated schema; it must not touch production.

## Steps

1. Create a Neon project in region **EU Central (Frankfurt)**.
2. Open the SQL Editor (or connect with `psql`) and run the script below.
3. Record the results against the pass criteria.

## SQL script

```sql
-- 1. Install PostGIS in a dedicated schema (Neon guidance)
CREATE SCHEMA IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA postgis;

-- 2. Confirm version
SELECT postgis.postgis_full_version();

-- 3. Test table (SRID 4326)
CREATE TABLE _spike_test (
  id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text,
  geom postgis.geometry(Point, 4326)
);

-- 4. Two points near Lagos (lon, lat); second ~55 m north of the first
INSERT INTO _spike_test (name, geom) VALUES
  ('lagos', postgis.ST_SetSRID(postgis.ST_MakePoint(3.3792, 6.5244), 4326)),
  ('north', postgis.ST_SetSRID(postgis.ST_MakePoint(3.3792, 6.5249), 4326));

-- 5. Distance in METRES via the geography cast (expect ~55 m, NOT ~0.0005 degrees)
SELECT
  postgis.ST_Distance(a.geom::geography, b.geom::geography) AS metres,
  postgis.ST_DWithin(a.geom::geography, b.geom::geography, 60)  AS within_60m
FROM _spike_test a, _spike_test b
WHERE a.name = 'lagos' AND b.name = 'north';

-- 6. GiST index and confirm it is used
CREATE INDEX _spike_test_geom_idx ON _spike_test USING GIST (geom);
EXPLAIN
SELECT name FROM _spike_test
WHERE postgis.ST_DWithin(
  geom::geography,
  postgis.ST_SetSRID(postgis.ST_MakePoint(3.3792, 6.5244), 4326)::geography,
  100
);

-- 7. Derived lat/lng via generated columns (proves the "no independent columns" approach)
ALTER TABLE _spike_test ADD COLUMN lat double precision GENERATED ALWAYS AS (postgis.ST_Y(geom)) STORED;
ALTER TABLE _spike_test ADD COLUMN lng double precision GENERATED ALWAYS AS (postgis.ST_X(geom)) STORED;
SELECT name, lat, lng FROM _spike_test;

-- 8. Cleanup
DROP TABLE _spike_test;
```

## Pass criteria

- [ ] `CREATE EXTENSION postgis` succeeds (step 1) and `postgis_full_version()` returns a version.
- [ ] Step 5 returns a value ≈ **55** for `metres` and `true` for `within_60m`.
- [ ] Step 6 `EXPLAIN` shows an **Index Scan** using the GiST index.
- [ ] Step 7 returns `lat ≈ 6.5244` and `lng ≈ 3.3792` from the generated columns.

## Fail / escalate

If the extension is unavailable on the plan, or the geography cast / `ST_DWithin` / generated columns error, **stop and report** — do not proceed to schema design on an unverified platform.

## Outcome

Record the plan used, PostGIS version, and whether each criterion passed. This is the D1 go/no-go evidence.
