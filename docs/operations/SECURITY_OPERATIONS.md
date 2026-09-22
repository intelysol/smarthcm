# Enterprise Platform — Security Operations & Hardening Manual

## 1. Security Architecture & Boundary Invariants
The Enterprise Platform operates under a **Zero-Trust Defense-in-Depth** model:
- **Tenant Segregation:** Enforced via `ResolveTenant` middleware, TenantContext, and database query filters. Zero cross-tenant data traversal is permitted.
- **Credential Storage:** All API keys are stored hashed (`SHA-256` / `SHA-512`). All partner secrets, OAuth tokens, and certificates are encrypted via AES-256-GCM.
- **Audit Logging:** Every administrative mutation, authentication event, privilege change, and external API invocation is permanently recorded in `api_request_logs` and audit trails.

---

## 2. Vulnerability Management & Patching Cadence
- **Dependency Auditing:** Continuous automated vulnerability checks via `composer audit` and `npm audit`.
- **Patching Cadence:**
  - **Critical CVEs (CVSS 9.0 - 10.0):** Patch deployed within 24 hours of disclosure.
  - **High CVEs (CVSS 7.0 - 8.9):** Patch deployed within 7 calendar days.
  - **Medium/Low CVEs:** Rolled into bi-weekly release trains.

---

## 3. Secret Rotation Protocols
- **API Key Invalidation:** Admin executes client revocation via `/v1/platform/api/clients/{client}/revoke` which sets `api_clients.status = 'revoked'`.
- **Database Credentials:** Dual-user rotation strategy ensures application instances never experience downtime during password renewal.
- **Application Key (`APP_KEY`):** If compromised, execute batch database re-encryption prior to key replacement.

---

## 4. Security Incident Escalation
Any detected attempt at SQL injection, cross-tenant IDOR, or mass assignment privilege escalation triggers:
1. Immediate HTTP 403/422 response with zero sensitive error details.
2. High-priority audit record logged with request IP, user ID, tenant ID, and payload hash.
3. Automated rate limiting ban on repeated offending client IPs.
