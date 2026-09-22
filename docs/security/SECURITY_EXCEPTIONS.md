# Enterprise Security Exceptions & Risk Acceptance Register

## 1. Governance Policy for Security Exceptions

Under the Zero-Trust Architecture, all deviations, temporary waivers, or environmental exceptions must be formally documented, risk-assessed, approved by the Security Gate Owner, and assigned a finite expiration date.

Critical vulnerabilities (SEV-1) may **never** receive a risk exception.

---

## 2. Active Exceptions Register

| Exception ID | Target Component | Description / Rationale | Compensating Controls | Risk Level | Approved By | Expiration Date | Status |
|---|---|---|---|---|---|---|---|
| **EXC-2026-001** | Local Development Debugging | `APP_DEBUG=true` permitted in local development environments. | Strictly disabled (`APP_DEBUG=false`) in production CI, staging, and production container images. Stack traces completely suppressed in production error envelope. | **Low** | Security Lead | 2026-12-31 | **ACTIVE** |
| **EXC-2026-002** | Content Security Policy Inline Styles | `unsafe-inline` permitted for style-src in CSP header to accommodate dynamic Tailwind CSS utility classes and FontAwesome icons. | All user-generated content strictly sanitized via HTMLPurifier; script-src forbids `unsafe-eval` and untrusted external scripts. | **Low** | Platform Architect | 2026-12-31 | **ACTIVE** |
| **EXC-2026-003** | Webhook Timestamp Drift Window | Maximum allowed clock skew for inbound webhooks set to 300 seconds (5 minutes) rather than 60 seconds. | Idempotency keys tracked in Redis/DB with 24-hour TTL, mitigating replay attacks during the 5-minute window. | **Low** | Integration Lead | 2026-12-31 | **ACTIVE** |

---

## 3. Review & Revocation Process
Exceptions are reviewed monthly by the Security Review Board. Any expired exception without formal renewal automatically transitions to `REVOKED`, re-enabling standard blocking release gates.
