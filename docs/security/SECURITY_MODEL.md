# Zero-Trust Security Model & Identity Governance

## 1. Zero-Trust Operating Principles

The Enterprise Application Platform enforces a strict **Zero-Trust** security philosophy based on five inviolable tenets:

1. **Explicit Verification:** Always authenticate and authorize based on all available data points: user identity, location, device health, tenant status, role hierarchy, and active permissions.
2. **Least Privilege Access:** Limit user access with Just-In-Time (JIT) and Just-Enough-Access (JEA), risk-based adaptive policies, and data protection.
3. **Assume Breach:** Minimize blast radius and segment access by network, user, devices, and application awareness. Encrypt all sessions end-to-end. Use analytics to gain visibility and improve defenses.
4. **Client Payload Distrust:** Never trust client-supplied identifiers (`tenant_id`, `company_id`, `role_id`, `is_platform_admin`, `status`). Server-side context is the sole authority.
5. **Continuous Telemetry & Auditing:** Every security-relevant event must be recorded in an immutable, cryptographically verifiable audit trail.

---

## 2. Identity Lifecycle & Authentication Controls

### 2.1 Password Security & Storage
- **Algorithm:** Passwords are encrypted using modern key-derivation functions (Bcrypt work factor 12 or Argon2id).
- **Complexity Policy:** Minimum 12 characters, requiring uppercase, lowercase, numeric, and special characters. Common password dictionary matching is enforced.
- **Credential Throttling:** Failed login attempts trigger exponential backoff per IP and per username (5 attempts per minute max before temporary lockout).

### 2.2 Multi-Factor Authentication (MFA)
- **Privileged Roles:** Platform Administrators, Tenant Administrators, Payroll Specialists, and HR Executives are mandated to enable MFA (TOTP via RFC 6238 / WebAuthn).
- **Step-Up Authentication:** High-risk actions (e.g. changing bank accounts, granting administrative roles, regenerating API credentials, bulk payroll release) require step-up re-authentication or one-time challenge approval.

### 2.3 Session Management
- **Session ID Rotation:** Session IDs are regenerated upon login, privilege upgrade, and logout to prevent session fixation attacks.
- **Idle & Absolute Timeouts:** Idle timeout of 15 minutes for administrative workspaces and 30 minutes for standard employee portals; absolute maximum session life of 8 hours.
- **Concurrent Session Limits:** Detects and flags anomalous concurrent sessions across differing geographic IP ranges.
- **Cookie Security:** Cookies are strictly configured with `Secure`, `HttpOnly`, and `SameSite=Lax` (or `Strict` for sensitive banking/admin portals).

---

## 3. Cryptographic Standards

| Usage | Standard / Algorithm | Key Management / Rotation |
|---|---|---|
| **Data in Transit** | TLS 1.3 (fallback TLS 1.2 with PFS ciphers) | Automated ACME / Let's Encrypt 90-day certificates |
| **Data at Rest** | AES-256-GCM / Laravel Database Encryption | Environment key `APP_KEY`, rotated via key re-encryption commands |
| **Password Hashes** | Bcrypt (cost 12) / Argon2id | Per-user random salt generated per hash |
| **Webhook Signatures** | HMAC-SHA256 | Per-tenant webhook secret, stored encrypted |
| **Audit Log Integrity** | SHA-256 Chained Hashes (`integrity_hash`) | Nonce + Previous Hash + Payload Hash verification |
| **Signed URLs** | HMAC-SHA256 with 5-minute expiry | Ephemeral temporary download signatures |
