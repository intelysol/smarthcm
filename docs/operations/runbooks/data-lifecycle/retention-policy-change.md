# Operational Runbook: Retention Policy Change Governance

## Severity: P3 (Policy Governance & Compliance)
## Service: Operations Center / Data Lifecycle Engine

### 1. Objective
Governs the safe updating of retention schedules without unintended premature data deletion.

### 2. Constraints & Safeguards
* **Statutory Floor Protection**: Any request to reduce retention below platform minimums is rejected by the policy engine (`MAX(Platform Minimum, Tenant Request)`).
* **Deferred Evaluation**: Updating a policy does **not** trigger immediate background purges.
* A full dry-run simulation must be conducted before new deletion eligibility windows are scheduled.

### 3. Procedure
1. Tenant Administrator submits policy change via `/tenant-admin/data-lifecycle`.
2. System validates requested days against statutory floor.
3. If valid, increment policy `version` and record old/new values in audit log:
   ```json
   {
     "event": "policy_updated",
     "tenant_id": "{TENANT_ID}",
     "data_class": "{DATA_CLASS}",
     "old_days": 1825,
     "new_days": 2555,
     "actor": "{USER_EMAIL}"
   }
   ```
4. Run preview simulation to assess impacted record volume before enabling the new policy for batch processing.
