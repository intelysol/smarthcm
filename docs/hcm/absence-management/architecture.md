# Architecture & Domain Boundaries

## Architectural Role
The Absence Management domain (`app/Domains/Absence/`) functions as the operational bridge between policy-driven leave approvals, real-time clock-in/out attendance, and operational workforce scheduling.

```text
  [Leave Engine]                 [Time & Attendance]
  (Approved Leave)                 (Punch Records, No-Shows)
          \                               /
           \                             /
            ▼                           ▼
        +-----------------------------------+
        |       Absence Orchestrator        |
        |  (Events, Periods, Continuous Seq)|
        +-----------------------------------+
                          │
          ┌───────────────┴───────────────┐
          ▼                               ▼
  [Schedule Impact]               [Return to Work]
  - Shift Cancellations           - Phased Capacity Ramp
  - Coverage Candidates           - Operational Restrictions
  - Capacity Deficit Signals      - Case Review Lifecycles
          │                               │
          └───────────────┬───────────────┘
                          ▼
        +-----------------------------------+
        |      Multi-Way Reconciliation     |
        |  (Punch on Leave, Unplanned Gaps) |
        +-----------------------------------+
                          │
                          ▼
        +-----------------------------------+
        |     Forecasting & Advisory AI     |
        |  (Prophet/Holt-Winters, Bradford) |
        +-----------------------------------+
```

## Domain Entity Model
1. `HcmAbsenceEvent`: Atomic daily unit of employee absence.
2. `HcmAbsencePeriod`: Aggregated continuous absence span linking consecutive events.
3. `HcmAbsenceOperationalImpact`: Calculated capacity shortfall and replacement shift assignments.
4. `HcmAbsenceReturnToWorkPlan`: Phased return schedules (e.g., 50% -> 75% -> 100%) and work restrictions.
5. `HcmAbsenceCase`: Extended formal case governance for long-term/chronic absence.
6. `HcmAbsenceReconciliation`: Discrepancy detector across leave approvals, timesheet punches, and rosters.
7. `HcmAbsenceForecast`: Forward-looking expected absence rates by department and skill category.