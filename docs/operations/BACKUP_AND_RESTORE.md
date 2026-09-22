# Enterprise Platform — Backup & Restore Operations Specification

## 1. Backup Architecture & Policies
The platform mandates automated multi-tiered backups across all stateful tiers:

| Tier | Backup Mechanism | Frequency | Retention Period | Storage Location | Encryption |
|---|---|---|---|---|---|
| **Primary Database (MySQL)** | Consistent mysqldump with `--single-transaction` + Continuous binary logs | Full: Every 24 hours (02:00 UTC)<br>Incremental/Binlogs: Every 15 minutes | 30 days daily, 12 months monthly | Offsite S3 Bucket with Object Lock (WORM) | AES-256 (KMS / GPG) |
| **Object Storage (Files/Docs)** | S3 Cross-Region Replication (CRR) with Versioning | Real-time continuous replication | Version history retained 90 days | Secondary Cloud Region | S3-KMS SSE |
| **Redis Cache / State** | RDB snapshotting | Hourly | 48 hours | Ephemeral worker block storage | In-flight TLS |
| **Application Config & Secrets** | Git version-controlled templates (`.env.example`) + HashiCorp Vault snapshots | Daily snapshot | 90 days | Encrypted cold storage | Multi-party threshold |

---

## 2. Automated Backup Execution
Database backups are orchestrated via `database/scripts/backup.ps1` (Windows) and `database/scripts/backup.sh` (Linux).

Execution Syntax:
```bash
# Linux / Docker
./database/scripts/backup.sh smarthcm 127.0.0.1 3306 root storage/backups

# Windows PowerShell
pwsh database/scripts/backup.ps1 -DbName smarthcm -OutputDir storage/backups
```
Output:
- Generated SQL Dump: `smarthcm_backup_YYYYMMDD_HHMMSS.sql`
- Cryptographic Checksum: `smarthcm_backup_YYYYMMDD_HHMMSS.sql.sha256`

---

## 3. Restore Drill & Verification Procedure
A backup is invalid until a restore drill is successfully performed. The platform restore procedure follows:

1. **Pre-flight Check:** Verify SHA-256 integrity against `.sha256` manifest.
2. **Environment Isolation:** Never restore directly over production without testing in an isolated staging or sandbox database first.
3. **Execution Syntax:**
```bash
# Windows PowerShell Restore Drill
pwsh database/scripts/restore.ps1 -BackupFile storage/backups/smarthcm_backup_*.sql -TargetDb smarthcm_drill_test -VerifyChecksum

# Linux / Docker Restore Drill
./database/scripts/restore.sh storage/backups/smarthcm_backup_*.sql smarthcm_drill_test
```
4. **Post-Restore Schema Verification:**
```bash
php artisan hcm:schema:verify --strict
```
5. **Table Count Audit:** Assert that exactly 985 tables and 2,180 foreign keys are restored with zero errors.
