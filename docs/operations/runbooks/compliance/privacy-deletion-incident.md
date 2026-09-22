# Operational Runbook: Privacy Deletion (Right to be Forgotten) Resolution

## Severity: P2 (Data Protection & Retention Conflict)
## Applicable Regulations: GDPR Article 17 / CCPA Section 1798.105

### 1. Overview & Conflict Interception
A data subject requests complete erasure of their personal records. However, under GDPR Article 17(3) and national employment laws, employers must preserve records necessary for compliance with a legal obligation (e.g. tax, payroll, social security, worker compensation) or active legal claims.

### 2. Automated Retention & Legal Hold Interception
1. System passes requested employee ID to `EnterprisePrivacyService::processDeletionRequest()`.
2. Service queries `DataLifecycleService::evaluateRecordLifecycleState()`:
   * **If Legal Hold Active**: Deletion is **STRICTLY BLOCKED**. Status updated to `blocked`, `blocked_reason = "Active legal hold {HOLD_REFERENCE} in effect."`
   * **If Statutory Retention Active**: Deletion is **STRICTLY BLOCKED**. Status updated to `blocked`, `blocked_reason = "Statutory retention floor active (e.g. 10 years payroll/tax)."`.
3. Provide explainable, auditable written rationale to the data subject explaining the statutory legal retention requirements that prevent immediate erasure.

### 3. Authorized Erasure Execution
1. Once statutory retention has elapsed and all legal holds have been released:
   ```bash
   php artisan privacy:deletion:execute --request-id={REQUEST_ID}
   ```
2. System executes controlled redaction/purging in accordance with Epic 2.78 secure deletion controls and logs an immutable audit event.
