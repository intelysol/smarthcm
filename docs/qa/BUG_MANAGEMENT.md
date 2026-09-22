# Enterprise Bug Management & Defect Severity Protocol

## 1. Defect Severity Classification

Defects detected during development, QA testing, or production telemetry are classified into 4 strict severity tiers:

### P0 — Blocker / Critical Catastrophe
- **Definition**: Security vulnerability (IDOR, auth bypass), multi-tenant data leak, data loss/corruption, payroll calculation failure, or widespread platform outage.
- **SLA**: Triage within 15 minutes; mitigation hotfix within 2 hours; RCA within 24 hours.
- **Release Impact**: **IMMEDIATE RELEASE FREEZE**. Blocks all deployments.

### P1 — Major Functionality Failure
- **Definition**: Core business capability unusable (e.g. employee cannot submit leave, manager cannot approve timesheet, recruitment applicant cannot apply) with no viable workaround.
- **SLA**: Triage within 1 hour; hotfix within 12 hours.
- **Release Impact**: Blocks release certification until resolved and tested.

### P2 — Moderate Non-Critical Flaw
- **Definition**: Minor functional issue with an acceptable operational workaround (e.g. export formatting glitch, non-critical notification delayed, minor UI layout misalignment on secondary screens).
- **SLA**: Fix scheduled in next sprint cycle (within 5 business days).
- **Release Impact**: Allowed for release with written product sign-off.

### P3 — Cosmetic / Enhancement
- **Definition**: Typo, visual polish, non-blocking aesthetic improvement.
- **SLA**: Backlog prioritization.
- **Release Impact**: Does not block release.

---

## 2. The Mandatory Regression Test Mandate

From Epic 2.68 onward, the platform operates under an absolute mandate:

> **NO BUG FIX IS MERGED WITHOUT AN AUTOMATED REGRESSION TEST.**

### Protocol:
1. **Reproduce**: Author an automated test replicating the exact defect conditions. Verify the test **FAILS** against current code.
2. **Remediate**: Apply the minimal targeted fix to the domain controller, service, or model.
3. **Certify**: Run the regression test and verify it now **PASSES**.
4. **Permanent Inclusion**: Commit the test permanently to the corresponding regression suite (`tests/Security`, `tests/Tenant`, `tests/API`, `tests/Feature`).
5. **Root Cause Analysis (RCA)**: For P0/P1 defects, document the root cause, contributing factors, detection gap, and architectural prevention measures.
