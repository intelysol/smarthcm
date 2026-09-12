# Workforce Scheduling Architecture

## High-Level Architecture Diagram

```mermaid
graph TD
    subgraph "Demand & Capacity (Epic 2.45)"
        CAP[Capacity Calculations] --> DEM[Coverage Requirements]
    end

    subgraph "Core HR & Platform Masters"
        EMP[Employee Master]
        POS[Position Master]
        SKILL[Career Skills]
        CERT[Compliance / Learning]
        LEAVE[Approved Leave]
    end

    subgraph "Workforce Scheduling Engine (Epic 2.46)"
        DEM --> COV[Coverage Calculation Service]
        COV --> OPT[Schedule Optimization Engine]
        AVAIL[Availability & Preferences] --> OPT
        OPT --> PROP[Proposed Assignments]
        MGR[Manager Review] -->|Approve| ACT[Active Scheduled Roster]
        
        EMP --> ELIG[Eligibility & Validation Engine]
        SKILL --> ELIG
        CERT --> ELIG
        LEAVE --> ELIG
        ELIG --> VAL[Schedule Validation Service]
        VAL --> PUB[Schedule Publication Service]
        PUB --> LOCK[Schedule Lock & Changes]
    end

    subgraph "Time & Attendance Actuals (Epic 2.18)"
        ACT --> ATT[Attendance Sessions / Clocks]
        ATT --> RTC[Real-Time Coverage Service]
        RTC --> EXC[Schedule Exceptions: No-Shows / Late]
    end
```

## Cross-Domain Ownership Rules

1. **Scheduling vs Attendance**:
   - `Scheduling` owns the planned commitment: shift definition, scheduled hours, planned break window.
   - `Attendance` owns the actual reality: clock-in/out stamps, actual duration, punches, overtime approvals.
2. **Eligibility & Governance**:
   - Employee eligibility is strictly checked before shift assignment and swap approval.
   - Zero hard constraints (approved leave, inactive employment, expired mandatory compliance, <11h rest) can be bypassed without explicit override logging.
3. **Multi-Tenancy**:
   - Every table and service is strictly partitioned by `tenant_id`.
