# Enterprise Security Incident Response Plan (S-IRP)

## 1. Incident Management Overview

The Security Incident Response Plan establishes a deterministic, audited process for identifying, containing, eradicating, and recovering from security events affecting the Enterprise Application Platform.

---

## 2. Severity Classification & Escalation SLAs

| Severity Tier | Definition | Response SLA | Resolution SLA | Stakeholders Notified |
|---|---|---|---|---|
| **SEV-1 (Critical)** | Active cross-tenant data leak, remote code execution (RCE), widespread credential compromise, total outage due to attack. | **< 15 minutes** | **< 4 hours** | CISO, CTO, Platform Owner, Legal, Affected Tenant Admins |
| **SEV-2 (High)** | Single-tenant privilege escalation, localized BOLA/IDOR vulnerability, bypass of MFA for a privileged user, unencrypted PII exposure. | **< 30 minutes** | **< 12 hours** | Security Lead, Domain Lead, Platform Operations |
| **SEV-3 (Medium)** | Credential stuffing attempts mitigated by rate limits, unvalidated redirect, missing security header on low-impact route, excessive export rate. | **< 2 hours** | **< 48 hours** | Security Engineer, On-Call Developer |
| **SEV-4 (Low)** | Minor security hygiene observation, low-risk CSP policy warning, informational vulnerability scanner finding. | **< 24 hours** | Next Release Cycle | Security Team Backlog |

---

## 3. Incident Lifecycle Phases

```text
┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  DETECTION   │ ──► │ CONTAINMENT  │ ──► │ ERADICATION  │ ──► │   RECOVERY   │ ──► │ POST-MORTEM  │
└──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘
```

### Phase 1: Detection & Triage
- Automated alerts from `EvaluateAnalyticsAlertsJob`, failed authentication rate spikes, cross-tenant authorization rejections (`403` log anomalies).
- Immediate assignment of Incident Commander (IC).
- Dedicated secure communication channel established.

### Phase 2: Containment
- **Short-Term Containment:**
  - Revocation of compromised sessions: `Auth::logoutOtherDevices()`, invalidation of user `authz:version`.
  - Account locking: `TenantSecurityAdminService::updateUserStatus(userId, 'LOCKED')`.
  - IP blocking / WAF perimeter rule addition.
  - Tenant suspension if systemic breach detected: `Tenant::where('id', $tenantId)->update(['status' => 'suspended'])`.

### Phase 3: Eradication
- Identify root cause (e.g. missing policy check, dependency vulnerability, unescaped parameter).
- Implement emergency hotfix and regression test.
- Verify through security test suite.

### Phase 4: Recovery
- Deploy hotfix through automated CI/CD pipeline.
- Restore affected accounts/tenants after credential resets.
- Monitor telemetry for secondary recurrence.

### Phase 5: Post-Mortem & Forensic Audit
- Conduct Root Cause Analysis (RCA) within 72 hours.
- Document timeline, impact scope, mitigating factors, and permanent preventive actions.
- Issue regulatory / customer notifications where mandated by GDPR / CCPA / HIPAA.
