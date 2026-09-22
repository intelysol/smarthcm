# Operational Runbook: Compliance Policy Exception Governance

## Severity: P3 (Risk Acceptance Governance)
## Service: Governance Control Plane / Exceptions Engine

### 1. Exception Request Criteria
An exception is permitted only when technical debt or operational constraints prevent immediate adherence to a control and a valid compensating control exists.

### 2. Submission & Mandatory Fields
1. Requester submits exception via `/admin/compliance-governance` or API:
   * Target Control ID
   * Root Cause & Reason
   * Documented Business Justification
   * Risk Assessment Level (`LOW`, `MEDIUM`, `HIGH`)
   * Active Compensating Control
   * Requested Expiration Date (maximum 90 days)

### 3. Approval & Expiration Monitoring
1. Exceptions with Risk Level `HIGH` require CISO and DPO dual-approval.
2. System records approval in `governance_exceptions` with status `approved`.
3. Scheduled cron continuously evaluates `expires_at`:
   * 14 days prior: Notification reminder sent to owner.
   * On expiration: Status transitions to `expired`, control re-tests automatically.
