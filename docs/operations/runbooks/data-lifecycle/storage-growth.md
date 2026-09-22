# Operational Runbook: Storage Growth Monitoring & Forecasting

## Severity: P3 (Capacity Planning)
## Service: Operations Center / Storage Optimization

### 1. Growth Thresholds & Alerts
* **Warning (Yellow)**: Active operational database storage exceeds 75% allocated disk.
* **Critical (Red)**: Operational storage exceeds 85% disk or telemetry tables grow faster than 15% month-over-month.

### 2. Investigation Protocol
1. Open `/operations/data-lifecycle` to review current table row distributions and storage footprints.
2. Identify top high-volume tables:
   * `attendance_punches`
   * `activity_logs`
   * `ops_metrics`
   * `notifications`
3. Check volume of `ARCHIVE_ELIGIBLE` records:
   ```bash
   php artisan lifecycle:report --scope=platform
   ```

### 3. Remediation Actions
1. Trigger archival dry-run for aging biometric attendance data older than 90 days:
   ```bash
   php artisan lifecycle:archive --data-class=attendance --dry-run
   ```
2. Schedule off-peak batch archival to move records into compressed archive storage.
3. Validate database vacuum / index defragmentation post-archival to reclaim tablespace.
