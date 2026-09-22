# Operational Runbook: Legal Hold Placement, Audit & Release

## Severity: P1 (Legal & Regulatory Compliance)
## Service: Operations Center / Data Lifecycle Engine

### 1. Overview
A Legal Hold suspends all lifecycle archival, cold tiering, and deletion actions for target scopes (tenant, individual employee, case, or specific data classes) pending litigation or regulatory inquiry.

### 2. Placing a Legal Hold
1. Authorized Legal/Compliance officer navigates to `/operations/data-lifecycle` or triggers API:
   ```bash
   php artisan lifecycle:legal-hold:place \
     --tenant={TENANT_ID} \
     --scope-type=employee \
     --scope-id={EMPLOYEE_UUID} \
     --reason="Matter #2026-CV-99182 litigation preservation"
   ```
2. System creates entry in `data_lifecycle_legal_holds` with status `active`.
3. Verification: Attempting any dry-run deletion or archive on this scope will return state `LEGAL_HOLD` with `blocked = true`.

### 3. Releasing a Legal Hold
1. Legal counsel confirms litigation resolution and signs release authorization.
2. Trigger release action:
   ```bash
   php artisan lifecycle:legal-hold:release \
     --hold-id={HOLD_ID} \
     --reason="Matter closed; formal release signed by General Counsel"
   ```
3. System updates hold to `released`, sets `released_at = now()`, and schedules normal lifecycle re-evaluation.
