# Operational Runbook: Compliance Control Test Failure Remediation

## Severity: P2 / P1 (Compliance Gap / Audit Risk)
## Service: Governance Control Plane / Control Testing

### 1. Alert & Automated Finding Generation
1. Scheduled or manual control test reports result `FAIL`.
2. System immediately creates an audit finding in `governance_findings` with status `OPEN` and generates a remediation task assigned to the control owner.

### 2. Triage & Impact Assessment
1. Control Owner and Compliance Officer inspect test failure reason and attached cryptographic evidence.
2. Determine if the failure introduces immediate security or regulatory exposure:
   * **Critical/High Severity**: Require immediate compensating control and 48-hour remediation SLA.
   * **Medium/Low Severity**: Require standard 30-day remediation roadmap.

### 3. Remediation & Re-Testing
1. Implement technical or procedural fix (e.g. configure missing TLS cipher, enforce MFA policy).
2. Attach updated remediation proof and set finding status to `IN_REMEDIATION`.
3. Execute formal re-test:
   ```bash
   php artisan compliance:control:test --control-id={CONTROL_ID}
   ```
4. Upon receiving `PASS`:
   * Finding transitions to `VERIFIED` and `CLOSED`.
   * Control status returns to `ACTIVE`.
