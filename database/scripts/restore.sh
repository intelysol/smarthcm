#!/usr/bin/env bash
# Enterprise HCM Database Restore & Verification Utility (Bash/Linux)
set -euo pipefail

BACKUP_FILE="${1:?Error: Specify backup file path}"
TARGET_DB="${2:-smarthcm_restore_test}"
DB_HOST="${3:-${DB_HOST:-127.0.0.1}}"
DB_PORT="${4:-${DB_PORT:-3306}}"
DB_USER="${5:-${DB_USERNAME:-root}}"

if [ ! -f "${BACKUP_FILE}" ]; then
  echo "Error: Backup file not found: ${BACKUP_FILE}" >&2
  exit 1
fi

if [ -f "${BACKUP_FILE}.sha256" ]; then
  echo "Verifying SHA-256 checksum..."
  sha256sum -c "${BACKUP_FILE}.sha256"
fi

echo "Creating target database ${TARGET_DB}..."
mysql --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USER}" -e "CREATE DATABASE IF NOT EXISTS \`${TARGET_DB}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Importing dump into ${TARGET_DB}..."
mysql --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USER}" "${TARGET_DB}" < "${BACKUP_FILE}"

TABLE_COUNT=$(mysql --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USER}" -N -e "SELECT count(*) FROM information_schema.tables WHERE table_schema='${TARGET_DB}';")

echo "RESTORE SUCCESS: Table count in ${TARGET_DB} is ${TABLE_COUNT}."
