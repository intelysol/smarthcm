#!/usr/bin/env bash
# Enterprise HCM Database Backup Utility (Bash/Linux)
set -euo pipefail

DB_NAME="${1:-${DB_DATABASE:-smarthcm}}"
DB_HOST="${2:-${DB_HOST:-127.0.0.1}}"
DB_PORT="${3:-${DB_PORT:-3306}}"
DB_USER="${4:-${DB_USERNAME:-root}}"
OUTPUT_DIR="${5:-storage/backups}"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
mkdir -p "${OUTPUT_DIR}"

DUMP_FILE="${OUTPUT_DIR}/${DB_NAME}_backup_${TIMESTAMP}.sql"
CHECKSUM_FILE="${DUMP_FILE}.sha256"

echo "Creating transactionally consistent backup of ${DB_NAME} to ${DUMP_FILE}..."

mysqldump \
  --host="${DB_HOST}" \
  --port="${DB_PORT}" \
  --user="${DB_USER}" \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  --default-character-set=utf8mb4 \
  "${DB_NAME}" > "${DUMP_FILE}"

sha256sum "${DUMP_FILE}" > "${CHECKSUM_FILE}"

echo "SUCCESS: Backup completed. Size: $(du -sh "${DUMP_FILE}" | cut -f1), Checksum: ${CHECKSUM_FILE}"
