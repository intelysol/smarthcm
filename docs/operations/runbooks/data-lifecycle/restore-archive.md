# Operational Runbook: Safe Archive Data Restoration

## Severity: P2 (Operational Data Recovery)
## Service: Operations Center / Data Lifecycle Engine

### 1. Pre-Flight Verification
1. Confirm tenant identity and authorization token.
2. Verify the target archive record in `data_lifecycle_archives`:
   ```sql
   SELECT id, tenant_id, data_class, record_count, checksum_sha256, status
   FROM data_lifecycle_archives
   WHERE id = '{ARCHIVE_ID}';
   ```
3. Verify cryptographic checksum of the archive package against stored `checksum_sha256`.

### 2. Conflict Detection & Restoration Procedure
1. Execute dry-run restoration to detect potential primary key collisions with active operational data:
   ```bash
   php artisan lifecycle:restore --archive-id={ARCHIVE_ID} --dry-run
   ```
2. Review conflict report:
   * If collisions = 0: Proceed with direct atomic restoration.
   * If collisions > 0: Review collision resolution strategy (`skip_existing`, `rename_key`, or `quarantine_duplicate`).
3. Execute verified restoration:
   ```bash
   php artisan lifecycle:restore --archive-id={ARCHIVE_ID} --strategy=skip_existing
   ```
4. Confirm post-restore row counts and update archive status to `RESTORED`.
