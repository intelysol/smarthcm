# Operational Runbook: Database Backup & Point-in-Time Restore Failure

## 1. Overview
- **ID:** `rb-backup-failure`
- **Severity:** SEV-1 (Major)
- **Component:** Backup Operations / Point-in-Time Recovery (PITR) / S3 Glacier Archive
- **Trigger:** Scheduled snapshot missed, backup creation failed, or automated restore verification test failed.

---

## 2. Immediate Diagnostic Steps
1. **Check Backup Execution Logs:**
   ```bash
   grep -i "backup" storage/logs/laravel.log | grep -E "ERROR|CRITICAL" | tail -n 20
   ```
2. **Inspect S3 / Storage Target Quota:**
   - Confirm backup destination bucket has sufficient capacity and lifecycle policies enabled.
3. **Verify Last Successful Snapshot Timestamp:**
   - Must be within RPO window (< 1 hour for transaction logs, < 24 hours for full snapshots).

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Backup Failed Due to Lock Wait Timeout**
  - Use `--single-transaction` and `--quick` flags with `mysqldump` to avoid acquiring table locks:
    ```bash
    mysqldump --single-transaction --quick --routines --triggers -u root -p smarthcm | gzip > /backups/manual_emergency_backup.sql.gz
    ```
- **Scenario B: S3 Storage Authentication Failure**
  - Verify IAM credentials for the backup service account.
- **Scenario C: Scheduled Point-in-Time Restore (PITR) Exercise**
  - To test or execute disaster recovery restore to a clean staging environment:
    ```bash
    # Decompress backup
    gunzip < backup_snapshot.sql.gz | mysql -h staging-db -u root -p staging_smarthcm
    # Run sanity verification
    php artisan db:monitor
    ```

---

## 4. Verification & Post-Resolution
- Verify latest backup archive file exists in remote storage with size > 0.
- Operations Console `/operations/backups` displays green status with current timestamp.
