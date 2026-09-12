# EPIC 2.47 — HCM Workforce Time, Attendance, Timesheets, Overtime, Absence & Labor Compliance Intelligence

## 1. Overview
Epic 2.47 establishes the authoritative actual-work, attendance, timesheet, overtime, and labor compliance layer of SmartHCM. It bridges the gap between planned workforce schedules (Epic 2.46) and strategic capacity management (Epic 2.45), while feeding verified, approved, payable time directly to Payroll and chargeable time to Finance.

## 2. Core Capabilities
- **Multi-Source Ingestion & Idempotency**: Normalizes biometric terminals, mobile, web, RFID, and API feeds with non-destructive duplicate filtering.
- **Cross-Midnight Attendance**: Robust operational date resolution and punch pairing for shifts spanning midnight (e.g. 22:00 to 06:00).
- **Timesheet Project & Task Time**: Enterprise project/task/activity/cost-center time allocation distinguishing chargeable vs payable time.
- **Multi-Tier Overtime Engine**: Segregates overtime into Tier 1 (1.5x), Tier 2 (2.0x), and Tier 3 (2.5x/3.0x holiday/rest-day), tracking unauthorized deviations.
- **Labor Compliance Intelligence**: Real-time evaluation of maximum daily hours (12h cap), minimum rest intervals between shifts (11h rest), and mandatory meal breaks.
- **Payroll Export & Reconciliation**: Batched payable time payloads for Integration Hub + automated bidirectional reconciliation detecting discrepancies.
- **Advisory AI Intelligence**: Strictly advisory summaries (`is_advisory_only: true`) for anomalies, timesheet explanations, and fatigue risk.

## 3. Documentation Index
1. [Architecture](architecture.md)
2. [Raw Events & Normalization](raw-events-and-normalization.md)
3. [Attendance Sessions & Breaks](attendance-sessions-breaks.md)
4. [Cross-Midnight Shifts](cross-midnight-shifts.md)
5. [Schedule Reconciliation](schedule-reconciliation.md)
6. [Exceptions & Corrections](exceptions-and-corrections.md)
7. [Timesheets & Lifecycle](timesheets-and-lifecycle.md)
8. [Project & Task Time](project-and-task-time.md)
9. [Overtime Tiers & Policies](overtime-tiers-and-policies.md)
10. [Labor Compliance Intelligence](labor-compliance-intelligence.md)
11. [Payroll Integration & Export](payroll-integration-export.md)
12. [Payroll Reconciliation](payroll-reconciliation.md)
13. [Finance & Capacity Integration](finance-capacity-integration.md)
14. [Device Integration Adapters](device-integration-adapters.md)
15. [AI Governance & Advisory Guidelines](ai-governance-and-advisory.md)
16. [API Reference](api.md)
17. [Security, Privacy & Audit](security-privacy-audit.md)