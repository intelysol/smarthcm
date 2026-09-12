# HCM Workforce Scheduling, Shift Management, Rostering & Real-Time Workforce Allocation (Epic 2.46)

## Overview

The **HCM Workforce Scheduling & Operational Allocation** capability in SmartHCM operationalizes workforce demand and capacity into executable schedules.

It directly extends:
- **Epic 2.18**: Foundation shift definitions, recurring patterns, and roster assignments.
- **Epic 2.45**: Capacity and demand requirements layer.

---

## Key Capabilities

1. **Shift Management & Patterns**: Flexible, fixed, split, and overnight shifts with configurable breaks and grace periods.
2. **Demand-Driven Coverage**: Seamless ingestion of staffing requirements from Epic 2.45 capacity models.
3. **Availability & Preferences**: Employee self-service availability blocks and shift preferences.
4. **Skills & Compliance Eligibility**: Automated validation of required skills, proficiency levels, and active compliance certifications.
5. **Constraint Validation**: Hard constraint blocking (leave conflicts, rest period violations, inactive employment) vs soft constraint warnings (overtime risk, preference mismatches).
6. **Schedule Optimization**: Deterministic rule-based optimization engine generating proposals without autonomous auto-publishing.
7. **Shift Swaps & Open Shifts**: Peer-to-peer swap workflows and open shift bidding with transparent eligibility scoring.
8. **Publication & Locking**: Gated publishing workflow, schedule cutoff locking, and published change audit trails.
9. **Real-Time Coverage & Exceptions**: Real-time comparison between planned schedules and attendance clock-ins, detecting no-shows and late arrivals.
10. **Advisory AI Governance**: Advisory insights with transparent trade-offs (`is_advisory_only: true`).

---

## Documentation Index

- [Architecture & Domain Boundaries](architecture.md)
- [Shift Definitions & Patterns](shifts-and-patterns.md)
- [Schedule Periods & Versions](schedule-periods-and-versions.md)
- [Coverage Requirements & Ingestion](coverage-requirements.md)
- [Availability & Shift Preferences](availability-and-preferences.md)
- [Skills-Based Scheduling & Compliance](skills-based-scheduling.md)
- [Schedule Validation & Constraints](schedule-validation-constraints.md)
- [Optimization Engine & Heuristics](optimization-engine.md)
- [Shift Swaps & Peer Workflows](shift-swaps.md)
- [Open Shifts & Bidding](open-shifts-bidding.md)
- [Publishing & Schedule Locks](publishing-and-locks.md)
- [Real-Time Operational Coverage](real-time-coverage.md)
- [Schedule Exceptions & Deviations](schedule-exceptions.md)
- [Labor Cost & Overtime Risk](cost-and-overtime.md)
- [AI Governance & Safety Rules](ai-governance.md)
- [API Reference](api.md)
- [Security, RBAC & Privacy](security-privacy.md)
