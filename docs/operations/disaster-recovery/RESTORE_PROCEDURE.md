# Database & System Restore Procedure Playbook

## 1. Overview
This playbook details the step-by-step technical restoration procedure for bringing down an operational backup, verifying its cryptographic integrity, importing into a clean target database, validating schema and data integrity, and conducting safe cutover.

---

## 2. Pre-Restoration Checklist
- [ ] Incident Commander has declared disaster recovery state.
- [ ] Production traffic is drained or directed to maintenance status page.
- [ ] Target database instance is provisioned with matching character set (`utf8mb4`) and collation (`utf8mb4_unicode_ci`).
- [ ] Backup file and corresponding `.sha256` checksum manifest are retrieved from offsite storage.

---

## 3. Step-by-Step Restoration Sequence

### Step 1: Cryptographic Checksum Verification
Always verify the SHA-256 hash before piping any SQL dump into the database engine:
```powershell
# Windows PowerShell
$expected = (Get-Content "storage/backups/smarthcm_backup_*.sql.sha256").Split(" ")[0].Trim()
$actual = (Get-FileHash -Path "storage/backups/smarthcm_backup_*.sql" -Algorithm SHA256).Hash
if ($expected.ToUpper() -ne $actual.ToUpper()) { throw "CHECKSUM MISMATCH!" }
```
```bash
# Linux
sha256sum -c storage/backups/smarthcm_backup_*.sql.sha256
```

### Step 2: Database Import
Execute the import using `database/scripts/restore.ps1` or `database/scripts/restore.sh`:
```powershell
# Windows PowerShell
pwsh database/scripts/restore.ps1 `
    -BackupFile "storage/backups/smarthcm_backup_20260922_020000.sql" `
    -TargetDb "smarthcm" `
    -DbHost "127.0.0.1" `
    -DbPort 3306 `
    -DbUser "root" `
    -VerifyChecksum
```

### Step 3: Point-in-Time Binary Log Replay (Optional)
If restoring to a specific timestamp prior to corruption:
```bash
mysqlbinlog --start-datetime="2026-09-22 02:00:00" \
            --stop-datetime="2026-09-22 08:14:30" \
            /var/log/mysql/binlog.000042 | mysql -u root -p smarthcm
```

### Step 4: Schema Integrity Audit
Verify that all 985 tables, 2,180 foreign keys, and indexes exist:
```bash
php artisan tinker --execute="dump(app(\App\Domains\Operations\Services\DisasterRecoveryService::class)->validateSchemaIntegrity());"
```

### Step 5: Relational Consistency & Tenant Boundary Audit
Scan for broken foreign keys, orphan records, and multi-tenant isolation:
```bash
php artisan tinker --execute="dump(app(\App\Domains\Operations\Services\DisasterRecoveryService::class)->validateDataIntegrity());"
```

### Step 6: Application Stack Rebuild & Cache Warm-up
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan horizon:terminate
systemctl reload php8.3-fpm
```

### Step 7: Post-Restoration Smoke Tests
```bash
php artisan test --filter=DisasterRecoveryCertificationTest
```
- Verify `/health/ready` returns HTTP 200 OK.
- Verify `/health/dependencies` returns all dependencies green.
