#!/usr/bin/env bash

set -Eeuo pipefail

database_name="${DB_DATABASE:-memaerp}"
database_host="${DB_HOST:-127.0.0.1}"
database_port="${DB_PORT:-5433}"
database_user="${DB_USERNAME:-memaerp}"
backup_directory="${DB_BACKUP_DIRECTORY:-./backups}"
retention_days="${DB_BACKUP_RETENTION_DAYS:-35}"
timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
backup_path="${backup_directory}/${database_name}-${timestamp}.dump"
checksum_path="${backup_path}.sha256"

if [[ ! "${retention_days}" =~ ^[0-9]+$ ]] || (( retention_days < 1 )); then
  echo "DB_BACKUP_RETENTION_DAYS must be a positive integer." >&2
  exit 64
fi

mkdir -p "${backup_directory}"

PGPASSWORD="${DB_PASSWORD:-}" pg_dump \
  --host="${database_host}" \
  --port="${database_port}" \
  --username="${database_user}" \
  --format=custom \
  --compress=9 \
  --no-owner \
  --no-privileges \
  --file="${backup_path}" \
  "${database_name}"

shasum -a 256 "${backup_path}" > "${checksum_path}"
find "${backup_directory}" -type f -name "${database_name}-*.dump" -mtime "+${retention_days}" -delete
find "${backup_directory}" -type f -name "${database_name}-*.dump.sha256" -mtime "+${retention_days}" -delete

echo "Backup created: ${backup_path}"
echo "Checksum created: ${checksum_path}"
