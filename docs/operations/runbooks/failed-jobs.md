# Operational Runbook: Dead-Letter Queue & Failed Jobs Triage

## 1. Overview
- **ID:** `rb-failed-jobs`
- **Severity:** SEV-1 (Major) / SEV-2 (Significant)
- **Component:** Queues / Dead-Letter Queue (DLQ) / `failed_jobs` table
- **Trigger:** `failed_jobs_count > 50 in 5m` or high-value job failure (e.g. `ExecutePayrollRunJob`).

---

## 2. Immediate Diagnostic Steps
1. **Inspect Failed Jobs via Artisan or Database:**
   ```bash
   php artisan queue:failed
   ```
   Or via SQL query:
   ```sql
   SELECT id, queue, payload, exception, failed_at 
   FROM failed_jobs 
   ORDER BY failed_at DESC LIMIT 10;
   ```
2. **Inspect Exception Type:**
   - Database deadlocks?
   - Upstream API HTTP 500 / timeouts?
   - Unhandled domain validation or schema mismatch?

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Transient Network or Database Lock Timeout**
  - Once the upstream issue is resolved, retry individual or batch jobs:
    ```bash
    # Retry a specific failed job
    php artisan queue:retry <job-uuid>

    # Retry all failed jobs on a specific queue
    php artisan queue:retry --queue=notifications
    ```
- **Scenario B: Poisoned Payload (Code Bug)**
  - If a code bug is throwing an unrecoverable exception:
    1. Do NOT retry blindly.
    2. Deploy hotfix for the offending job handler.
    3. Retry failed jobs once fix is live:
       ```bash
       php artisan queue:retry all
       ```
- **Scenario C: Malformed / Obsolete Jobs**
  - If jobs are invalid and verified irrecoverable:
    ```bash
    php artisan queue:forget <job-uuid>
    ```

---

## 4. Verification & Post-Resolution
- Verify `SELECT COUNT(*) FROM failed_jobs;` returns 0 or known non-critical entries.
- Confirm related business records (payslips, leave balances, audit logs) reflect accurate status.
