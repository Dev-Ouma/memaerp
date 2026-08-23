#!/usr/bin/env bash

set -Eeuo pipefail

source_database="${DB_DATABASE:-memaerp}"
restore_database="${DB_RESTORE_DRILL_DATABASE:-memaerp_restore_drill}"
database_host="${DB_HOST:-127.0.0.1}"
database_port="${DB_PORT:-5433}"
database_user="${DB_USERNAME:-memaerp}"
started_at="$(date +%s)"
drill_directory="$(mktemp -d)"
backup_path="${drill_directory}/${source_database}.dump"

cleanup() {
  PGPASSWORD="${DB_PASSWORD:-}" dropdb \
    --host="${database_host}" \
    --port="${database_port}" \
    --username="${database_user}" \
    --if-exists \
    "${restore_database}" >/dev/null 2>&1 || true
  rm -rf "${drill_directory}"
}
trap cleanup EXIT

if [[ "${restore_database}" == "${source_database}" ]]; then
  echo "Restore drill database must differ from the source database." >&2
  exit 64
fi

PGPASSWORD="${DB_PASSWORD:-}" pg_dump \
  --host="${database_host}" \
  --port="${database_port}" \
  --username="${database_user}" \
  --format=custom \
  --no-owner \
  --no-privileges \
  --file="${backup_path}" \
  "${source_database}"

PGPASSWORD="${DB_PASSWORD:-}" dropdb \
  --host="${database_host}" \
  --port="${database_port}" \
  --username="${database_user}" \
  --if-exists \
  "${restore_database}"

PGPASSWORD="${DB_PASSWORD:-}" createdb \
  --host="${database_host}" \
  --port="${database_port}" \
  --username="${database_user}" \
  "${restore_database}"

PGPASSWORD="${DB_PASSWORD:-}" pg_restore \
  --host="${database_host}" \
  --port="${database_port}" \
  --username="${database_user}" \
  --dbname="${restore_database}" \
  --exit-on-error \
  --no-owner \
  --no-privileges \
  "${backup_path}"

source_counts="$(PGPASSWORD="${DB_PASSWORD:-}" psql \
  --host="${database_host}" --port="${database_port}" --username="${database_user}" \
  --dbname="${source_database}" --tuples-only --no-align \
  --command="SELECT table_schema || '.' || table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog', 'information_schema') ORDER BY 1")"

while IFS= read -r qualified_table; do
  [[ -z "${qualified_table}" ]] && continue
  source_count="$(PGPASSWORD="${DB_PASSWORD:-}" psql --host="${database_host}" --port="${database_port}" --username="${database_user}" --dbname="${source_database}" --tuples-only --no-align --command="SELECT count(*) FROM ${qualified_table}")"
  restored_count="$(PGPASSWORD="${DB_PASSWORD:-}" psql --host="${database_host}" --port="${database_port}" --username="${database_user}" --dbname="${restore_database}" --tuples-only --no-align --command="SELECT count(*) FROM ${qualified_table}")"

  if [[ "${source_count}" != "${restored_count}" ]]; then
    echo "Row-count mismatch for ${qualified_table}: source=${source_count}, restored=${restored_count}" >&2
    exit 1
  fi
done <<< "${source_counts}"

finished_at="$(date +%s)"
echo "Restore drill passed in $((finished_at - started_at)) seconds."
