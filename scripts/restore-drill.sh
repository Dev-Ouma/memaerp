#!/usr/bin/env bash
#
# Prove that a backup restores, and time it.
#
# A backup nobody has restored is not a backup, it is a file. This script takes the most recent
# dump, restores it into a scratch database, checks that the restored copy actually contains the
# things the university cannot lose, and prints how long it took. That number is the real Recovery
# Time Objective; everything else is an aspiration.
#
# Run it on a schedule, not only before an audit. The failure mode this catches — a dump that has
# been silently truncated, or one taken with the wrong flags — is invisible until the day it
# matters.
#
# Usage:
#   scripts/restore-drill.sh [path/to/dump]
#
# With no argument, the newest dump in DB_BACKUP_DIRECTORY is used.

set -Eeuo pipefail

readonly REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
readonly ENV_FILE="${REPO_ROOT}/apps/api/.env"

# Read a single key out of the API .env without sourcing it — .env files legitimately contain
# values with spaces and '#' that a naive `source` would mangle or execute.
env_value() {
    local key="$1" fallback="${2-}"
    local line
    line="$(grep -E "^${key}=" "${ENV_FILE}" 2>/dev/null | tail -1 || true)"
    if [[ -z "${line}" ]]; then
        printf '%s' "${fallback}"
        return
    fi
    local value="${line#*=}"
    value="${value%\"}"; value="${value#\"}"
    value="${value%\'}"; value="${value#\'}"
    printf '%s' "${value}"
}

DB_HOST="$(env_value DB_HOST 127.0.0.1)"
DB_PORT="$(env_value DB_PORT 5433)"
DB_USERNAME="$(env_value DB_USERNAME memaerp)"
DB_PASSWORD="$(env_value DB_PASSWORD secret)"
BACKUP_DIR="$(env_value DB_BACKUP_DIRECTORY "${REPO_ROOT}/storage/backups")"
DRILL_DB="$(env_value DB_RESTORE_DRILL_DATABASE memaerp_restore_drill)"

export PGPASSWORD="${DB_PASSWORD}"

log()  { printf '  %s\n' "$*"; }
fail() { printf '\n  FAILED: %s\n\n' "$*" >&2; exit 1; }

# Choosing a client.
#
# On the server the client and the database are the same release and there is nothing to decide.
# On a developer laptop they routinely are not, and a pg_restore older than the server cannot read
# its dumps at all — it fails with "unsupported version in file header", which reads like a corrupt
# backup and is not one. Rather than let the drill cry wolf, route the client through the database
# container when the local one is too old.
command -v psql >/dev/null 2>&1 || fail 'psql not found; install the PostgreSQL client tools'

CLIENT_MAJOR="$(psql --version | sed -E 's/[^0-9]*([0-9]+).*/\1/')"
SERVER_MAJOR="$(psql -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}" -d postgres -qtAX \
    -c 'SHOW server_version' 2>/dev/null | sed -E 's/[^0-9]*([0-9]+).*/\1/')" \
    || fail "cannot reach the database at ${DB_HOST}:${DB_PORT}"
[[ -n "${SERVER_MAJOR}" ]] || fail "cannot reach the database at ${DB_HOST}:${DB_PORT}"

if (( CLIENT_MAJOR < SERVER_MAJOR )); then
    docker compose --project-directory "${REPO_ROOT}" ps --services --status running 2>/dev/null \
        | grep -qx postgres \
        || fail "local client is PostgreSQL ${CLIENT_MAJOR} but the server is ${SERVER_MAJOR}; upgrade the client tools or start the postgres container"

    log "client is PostgreSQL ${CLIENT_MAJOR}, server is ${SERVER_MAJOR} — using the container's client"
    IN_CONTAINER=(docker compose --project-directory "${REPO_ROOT}" exec -T \
        -e PGPASSWORD="${DB_PASSWORD}" postgres)
    # Inside the container the database is always local on the default port.
    CLIENT_HOST=127.0.0.1
    CLIENT_PORT=5432
else
    IN_CONTAINER=()
    CLIENT_HOST="${DB_HOST}"
    CLIENT_PORT="${DB_PORT}"
fi

PSQL=("${IN_CONTAINER[@]}" psql -h "${CLIENT_HOST}" -p "${CLIENT_PORT}" -U "${DB_USERNAME}" -v ON_ERROR_STOP=1 -qtAX)
PG_RESTORE=("${IN_CONTAINER[@]}" pg_restore -h "${CLIENT_HOST}" -p "${CLIENT_PORT}" -U "${DB_USERNAME}")

# Never leave a scratch database behind, whether the drill passed, failed or was interrupted.
cleanup() {
    local status=$?
    "${PSQL[@]}" -d postgres -c "DROP DATABASE IF EXISTS \"${DRILL_DB}\" WITH (FORCE)" >/dev/null 2>&1 || true
    exit "${status}"
}
trap cleanup EXIT INT TERM

DUMP="${1-}"
if [[ -z "${DUMP}" ]]; then
    [[ -d "${BACKUP_DIR}" ]] || fail "no backup directory at ${BACKUP_DIR}; set DB_BACKUP_DIRECTORY or pass a dump path"
    DUMP="$(find "${BACKUP_DIR}" -type f \( -name '*.dump' -o -name '*.sql' -o -name '*.sql.gz' \) -print0 \
        | xargs -0 ls -t 2>/dev/null | head -1 || true)"
fi

[[ -n "${DUMP}" ]] || fail "no dump found in ${BACKUP_DIR}"
[[ -f "${DUMP}" ]] || fail "dump not found: ${DUMP}"

DUMP_BYTES="$(wc -c < "${DUMP}" | tr -d ' ')"
[[ "${DUMP_BYTES}" -gt 0 ]] || fail "dump is empty: ${DUMP}"

# A dump taken while the server was unreachable can still be a valid, tiny, useless file. Age is
# the cheapest signal that the backup job itself has quietly stopped running.
DUMP_AGE_HOURS=$(( ( $(date +%s) - $(date -r "${DUMP}" +%s) ) / 3600 ))

printf '\n  Restore drill\n  -------------\n'
log "dump:      ${DUMP}"
log "size:      $(( DUMP_BYTES / 1024 / 1024 )) MiB"
log "age:       ${DUMP_AGE_HOURS}h"
log "target:    ${DRILL_DB} on ${DB_HOST}:${DB_PORT}"
printf '\n'

if (( DUMP_AGE_HOURS > 26 )); then
    log "WARNING: the newest dump is ${DUMP_AGE_HOURS}h old. Daily backups should never exceed 26h."
fi

log 'dropping any previous drill database...'
"${PSQL[@]}" -d postgres -c "DROP DATABASE IF EXISTS \"${DRILL_DB}\" WITH (FORCE)" >/dev/null

log 'creating drill database...'
"${PSQL[@]}" -d postgres -c "CREATE DATABASE \"${DRILL_DB}\"" >/dev/null

log 'restoring...'
STARTED_AT=$(date +%s)

# Everything is fed over stdin: the container's client cannot see the host filesystem, and doing
# it the same way in both modes means the drill exercises one code path rather than two.
case "${DUMP}" in
    *.sql.gz)
        gunzip -c "${DUMP}" | "${PSQL[@]}" -d "${DRILL_DB}" >/dev/null
        ;;
    *.sql)
        "${PSQL[@]}" -d "${DRILL_DB}" < "${DUMP}" >/dev/null
        ;;
    *)
        # --exit-on-error makes a partial restore a failure rather than a database that looks fine
        # until somebody queries the missing half. Restoring from stdin rules out --jobs, which is
        # a real cost on a large dump; the drill measures the pessimistic case, which is the honest
        # one to hold an RTO against.
        "${PG_RESTORE[@]}" -d "${DRILL_DB}" --no-owner --no-privileges --exit-on-error < "${DUMP}" >/dev/null
        ;;
esac

ELAPSED=$(( $(date +%s) - STARTED_AT ))
log "restored in ${ELAPSED}s"
printf '\n  Verifying the restored copy\n'

query() { "${PSQL[@]}" -d "${DRILL_DB}" -c "$1"; }

# Structure. A restore that produced no schemas "succeeded" at doing nothing.
SCHEMA_COUNT="$(query "SELECT count(*) FROM information_schema.schemata
                       WHERE schema_name IN ('iam','institution','student','finance','examination','audit')")"
[[ "${SCHEMA_COUNT}" == "6" ]] || fail "expected 6 core schemas, found ${SCHEMA_COUNT}"
log "schemas:   ${SCHEMA_COUNT}/6 core schemas present"

# Content. These are the tables whose loss ends the institution's ability to operate: who people
# are, what they were charged, what they scored, and what the system did.
for check in \
    "institution.institutions:institutions" \
    "iam.users:user accounts" \
    "iam.roles:roles" \
    "student.persons:person records" \
    "audit.activity_log:audit entries"
do
    table="${check%%:*}"; label="${check##*:}"
    if ! rows="$(query "SELECT count(*) FROM ${table}" 2>/dev/null)"; then
        fail "table ${table} is missing from the restored database"
    fi
    log "$(printf '%-10s %s %s' 'rows:' "${rows}" "${label}")"
    [[ "${rows}" -gt 0 ]] || fail "${table} restored empty — the dump is not usable"
done

# The audit trail must still be append-only after a restore. Triggers are part of the schema, and
# a dump taken or restored with the wrong flags drops them silently — leaving a database that
# accepts history rewrites.
IMMUTABLE="$(query "SELECT count(*) FROM pg_trigger t
                    JOIN pg_class c ON c.oid = t.tgrelid
                    JOIN pg_namespace n ON n.oid = c.relnamespace
                    WHERE n.nspname = 'audit' AND c.relname = 'activity_log' AND NOT t.tgisinternal")"
[[ "${IMMUTABLE}" -ge 2 ]] || fail "audit immutability triggers did not survive the restore (found ${IMMUTABLE}, expected 2)"
log "triggers:  audit append-only enforcement intact"

printf '\n  PASS — restored and verified in %ss.\n' "${ELAPSED}"
printf '  Record this against the RTO target. If it is drifting upward, the recovery plan is\n'
printf '  drifting away from reality.\n\n'
