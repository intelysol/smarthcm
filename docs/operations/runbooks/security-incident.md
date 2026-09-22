# Operational Runbook: Security Incident & Threat Containment

## 1. Overview
- **ID:** `rb-security-incident`
- **Severity:** SEV-0 (Critical)
- **Component:** Security Operations / Zero-Trust Layer / WAF / Identity & Access
- **Trigger:** Cross-tenant access attempt anomaly, credential stuffing, rate-limit threshold breach, or unauthenticated privilege escalation attempt.

---

## 2. Immediate Diagnostic Steps
1. **Identify Source IP, User Account, and Target Tenant:**
   ```bash
   grep -i "SecurityHeadersMiddleware\|AccessDenied\|CrossTenant" storage/logs/laravel.log | tail -n 25
   ```
2. **Inspect Correlated Request Context:**
   - Note `request_id`, `correlation_id`, source IP, and user-agent.
3. **Check Active User Sessions:**
   - Query `sessions` table for suspicious concurrent sessions.

---

## 3. Mitigation & Containment Procedures
- **Scenario A: Compromised User Credentials / Suspicious Activity**
  - Revoke all active sessions for the user:
    ```sql
    DELETE FROM sessions WHERE user_id = '<user_id>';
    ```
  - Lock account by setting `is_active = false` or generating password reset.
  - Invalidate all Sanctum API tokens:
    ```sql
    DELETE FROM personal_access_tokens WHERE tokenable_id = '<user_id>';
    ```
- **Scenario B: Distributed Brute Force / Credential Stuffing**
  - Blacklist source IP CIDR block at Cloudflare / AWS WAF edge.
  - Tighten rate limiting in `bootstrap/app.php` or `RouteServiceProvider`.
- **Scenario C: Potential Cross-Tenant Leakage Attempt**
  - Verify `BasePolicy` and `TenantContext` isolation prevented data exposure.
  - Review query logs for any foreign `tenant_id` leakage.
  - Preserve all audit trail logs in `audit_logs` table for forensic analysis.

---

## 4. Verification & Post-Resolution
- Confirm compromised account is locked and all sessions terminated.
- Complete formal Post-Mortem within 24 hours using `docs/operations/POSTMORTEM_TEMPLATE.md`.
