# Operational Runbook: Document & Asset Storage Failure

## 1. Overview
- **ID:** `rb-storage-failure`
- **Severity:** SEV-1 (Major)
- **Component:** Storage / S3 / Local Disk / Filesystem Permissions
- **Trigger:** Storage disk write probe failure in `/health/dependencies` or disk usage > 90%.

---

## 2. Immediate Diagnostic Steps
1. **Probe Storage Health:**
   ```bash
   curl -s https://app.smarthcm.com/health/dependencies | jq .dependencies.storage
   ```
2. **Check Disk Space & Inode Capacity (Local / Mount):**
   ```bash
   df -h
   df -i
   ```
3. **Check Cloud Storage (S3 / Blob) Connectivity & Credentials:**
   - Verify AWS credentials / IAM role permissions: `s3:PutObject`, `s3:GetObject`, `s3:DeleteObject`.
   - Test read/write probe via Artisan tinker:
     ```bash
     php artisan tinker --execute="Storage::disk('s3')->put('test.txt', 'ok'); dump(Storage::disk('s3')->get('test.txt')); Storage::disk('s3')->delete('test.txt');"
     ```

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Local Disk Full**
  - Clean temporary export artifacts, old log archives, and cached views:
    ```bash
    php artisan view:clear
    find storage/logs -name "*.log.*" -mtime +14 -delete
    find storage/app/temp -type f -mtime +1 -delete
    ```
  - Expand EBS/block storage volume if underlying disk is full.
- **Scenario B: AWS S3 Rate Limiting (SlowDown 503)**
  - If high-volume document batch export triggers S3 throttling, partition object keys with hashed prefixes (e.g. `documents/{tenant_id}/{md5_hash}/{doc_id}.pdf`).
- **Scenario C: Filesystem Permission Errors**
  - Restore correct web server permissions:
    ```bash
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    ```

---

## 4. Verification & Post-Resolution
- Confirm `/health/dependencies` storage check returns `ok`.
- Verify user document upload and download succeed in the UI.
