# Operational Runbook: Asynchronous Archive Job Failure Triage

## Severity: P2 (Degraded Background Processing)
## Service: Operations Center / Data Lifecycle Engine

### 1. Symptoms
* Horizon queue alert for `DataLifecycleArchiveJob` dead-lettering.
* Job status in `data_lifecycle_jobs` marked as `failed`.
* Batch progress stopped with `checkpoint_token` preserved.

### 2. Diagnosis Steps
1. Navigate to `/operations/data-lifecycle` to identify the failed job ID.
2. Query the job record details:
   ```sql
   SELECT id, tenant_id, target_class, processed_records, total_records, failure_reason, checkpoint_token
   FROM data_lifecycle_jobs
   WHERE status = 'failed'
   ORDER BY updated_at DESC LIMIT 5;
   ```
3. Check the application logs for timeout or storage endpoint unreachable exceptions.

### 3. Recovery Procedure
1. If storage vault had temporary network latency, resume from checkpoint:
   ```bash
   php artisan lifecycle:archive:resume --job-id={JOB_ID}
   ```
2. Verify that processing resumes from the last saved `checkpoint_token` without duplicating already packed records.
3. Once completed, verify the archive record in `data_lifecycle_archives` matches expected SHA-256 manifest hash.
