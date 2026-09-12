# HCM Workforce Absence, Leave, Attendance Exceptions, Return-to-Work & Absence Management Intelligence (Epic 2.48)

## Overview
Epic 2.48 delivers an enterprise-grade absence orchestration, operational impact evaluation, return-to-work (RTW), multi-way reconciliation, and predictive forecasting layer for the SmartHCM platform.

It links previously fragmented silos:
- **Leave Management** (`leave_applications`)
- **Time & Attendance** (Actual punches, timesheets, no-shows — Epic 2.18 / 2.47)
- **Workforce Scheduling** (Rosters, shifts, allocations — Epic 2.46)
- **Workforce Capacity & Workload** (Capacity loss, understaffing — Epic 2.45)
- **Occupational Health & Safety** (Functional restrictions, medical clearances — Epic 2.34)
- **HR Shared Services / Case Management** (Formal absence cases, reviews — Epic 2.41)

## Guiding Principles & Architectural Boundaries
1. **Zero Duplicate Engines**: Leverages existing leave requests, punch timesheets, and shift schedules.
2. **Medical Privacy by Design**: Diagnostic information resides solely within Occupational Health (`hcm_health_records`). Absence records capture only `operational_restrictions` and `capacity_percentage`.
3. **Advisory AI Only**: AI recommendations operate under strict human-in-the-loop oversight (`is_advisory_only: true`, `autonomous_actions_permitted: false`).
4. **Closed-Loop Reconciliation**: Multi-way reconciliation continuously aligns Leave + Punch Attendance + Scheduled Shifts + Case tracking.

## Documentation Directory
- [1. Architecture & Domain Boundaries](architecture.md)
- [2. Planned vs. Unplanned Absence Lifecycle](planned-vs-unplanned-absence.md)
- [3. Absence Events & Continuous Periods](absence-events-and-periods.md)
- [4. Leave Management Integration](leave-integration.md)
- [5. Attendance Exceptions & Automated No-Shows](attendance-exception-no-show.md)
- [6. Schedule & Operational Capacity Impact](schedule-capacity-impact.md)
- [7. Coverage & Replacement Planning](coverage-replacement-planning.md)
- [8. Return-to-Work (RTW) Workflow](return-to-work-workflow.md)
- [9. Phased Return & Operational Restrictions](phased-return-restrictions.md)
- [10. Formal Absence Case Management](absence-case-management.md)
- [11. Multi-Way Absence Reconciliation](multi-way-reconciliation.md)
- [12. Absence Rate & Pattern Analytics](absence-rate-analytics.md)
- [13. Absence Forecasting Engine](absence-forecasting.md)
- [14. Payroll, Finance & Capacity Feedback Loop](payroll-finance-capacity-loop.md)
- [15. AI Governance, Ethics & Privacy Protection](ai-governance-and-advisory.md)
- [16. REST API Reference](api.md)
- [17. Security, Audit Trail & Privacy Safeguards](security-privacy-audit.md)