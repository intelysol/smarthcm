# Operational Runbook: Secure Deletion Failure & Interruption Triage

## Severity: P2 (Data Lifecycle Execution)
## Service: Operations Center / Data Lifecycle Engine

### 1. Overview
Secure deletion jobs execute multi-factor checks before removing records that have reached `DELETION_ELIGIBLE` status. If an error occurs, jobs halt atomically.

### 2. Diagnosis
1. Check failure reason in `data_lifecycle_jobs`:
   ```sql
   SELECT id, tenant_id, target_class, failure_reason, protected_records
   FROM data_lifecycle_jobs
   WHERE job_type = 'deletion' AND status = 'failed'
   ORDER BY updated_at DESC LIMIT 1;
   ```
2. Common causes:
   * **Dependency Protection Triggered**: An active foreign key or dependency guard blocked deletion (e.g. Employee has active payroll run or audit log).
   * **Concurrent Legal Hold Placed**: A hold was placed mid-execution; job safely aborted.

### 3. Resolution
1. If blocked by dependency guard: Do NOT force delete. Remove the record from candidate deletion list and re-classify as `PROTECTED`.
2. Re-run deletion dry-run to ensure only unencumbered records remain.
3. Resume the authorized deletion run.
