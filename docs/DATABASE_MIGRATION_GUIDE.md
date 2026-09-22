# Enterprise HCM — Database Migration & Operations Guide

> Operational runbook for deploying, upgrading, and verifying the Enterprise HCM 975-table database schema.

---

## 1. Deployment Path Decision Tree

Depending on the deployment environment, select the appropriate migration path:

```text
                  DEPLOYMENT ENVIRONMENT
                           │
             ┌─────────────┴─────────────┐
             ▼                           ▼
    FRESH INSTALLATION         EXISTING DATABASE
  (Greenfield / Staging)      (Production / UAT)
             │                           │
    ┌────────┴────────┐                  ▼
    ▼                 ▼         NON-DESTRUCTIVE REPAIR
OPTION A:         OPTION B:       Run Targeted Migration:
Laravel Artisan   MySQL CLI      2026_10_11_000001_repair_
migrate:fresh     Direct Import  enterprise_hcm_foreign_
                                 keys_and_schema.php
```

---

## 2. Path 1: Fresh Installation (Greenfield Deployments)

### Option A: Via Laravel Artisan Migrations
Recommended for Laravel development environments and CI/CD pipelines:

```bash
# 1. Ensure MySQL is running with sufficient memory
# 2. Run fresh migration with verbose output
php artisan migrate:fresh -vvv --no-interaction

# 3. Verify all 104 migrations completed in Batch 1
php artisan migrate:status

# 4. Execute the schema verification command
php artisan hcm:schema:verify
```

### Option B: Via Consolidated Standalone SQL File
Recommended for high-speed automated cloud provisioning (Kubernetes, AWS RDS, Docker):

```bash
# 1. Create target database with utf8mb4
mysql -u root -p -e "CREATE DATABASE smarthcm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import authoritative SQL schema (executes in ~3 minutes)
mysql -u root -p smarthcm < database/schema/enterprise_hcm_full_schema.sql

# 3. Record migrations as completed in Laravel
php artisan migrate:status
```

---

## 3. Path 2: Existing Production / Staging Database Upgrade

> [!CAUTION]
> **NEVER run `php artisan migrate:fresh` on an existing database with production data.** Doing so will drop all tables and destroy existing data.

Follow this zero-data-loss procedure:

### Step 1: Pre-Migration Backup
```bash
mysqldump -u root -p --single-transaction --routines --triggers smarthcm > smarthcm_backup_pre_repair.sql
```

### Step 2: Execute Targeted Repair Migration
The targeted migration `2026_10_11_000001_repair_enterprise_hcm_foreign_keys_and_schema.php` contains safe, idempotent type alterations:
- Detects whether each table and column exists.
- Inspects existing column datatypes via `INFORMATION_SCHEMA`.
- Drops mismatched foreign key constraints safely.
- Modifies user-referencing columns to `BIGINT UNSIGNED`.
- Attaches standardized foreign keys to `users(id)`.

```bash
php artisan migrate --step
```

### Step 3: Verify Schema Post-Upgrade
```bash
php artisan hcm:schema:verify
```
The command will exit with code `0` if all tables, primary keys, foreign keys, and column types strictly match the authoritative standard.

---

## 4. Verification & Health Monitoring

### Automated Schema Integrity Command
Run the built-in integrity validator:

```bash
php artisan hcm:schema:verify
```

Checks performed:
1. **Core Table Existence**: Verifies that all foundational HCM core tables exist.
2. **Primary Key Standard**: Confirms `users.id` is `BIGINT UNSIGNED AUTO_INCREMENT` and enterprise entities are `UUID`.
3. **Foreign Key Datatype Compatibility**: Compares local and referenced column definitions across all 2,180 constraints.
4. **User-Referencing Columns**: Scans for any `user_id` or `*_user_id` columns that deviate from `BIGINT UNSIGNED`.
5. **Exit Codes**: Returns `0` on 100% success, `1` on failure (suitable for CI/CD gates).

---

## 5. Rollback Procedures

If a newly deployed migration batch needs to be rolled back:

```bash
# Roll back the most recent migration batch
php artisan migrate:rollback

# Or roll back a specific number of steps
php artisan migrate:rollback --step=1

# Check status after rollback
php artisan migrate:status
```

All 104 migrations have been audited to ensure clean `down()` execution with foreign-key order safety.
