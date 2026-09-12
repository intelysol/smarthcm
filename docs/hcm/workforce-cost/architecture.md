# Architecture & Domain Boundaries

## Architectural Role
The Workforce Cost domain (`app/Domains/WorkforceCost/`) serves as the central economic aggregation and allocation hub for the platform.

```text
[Payroll Domain]           [Time & Attendance]         [Benefits / Expenses]
(Gross Pay & Burden)      (Worked Hours & Overtime)     (Employer Cost & Claims)
         \                           |                           /
          \                          |                          /
           ▼                         ▼                         ▼
     +-------------------------------------------------------------+
     |                Labor Cost Aggregation Pipeline              |
     |         (Normalized Cost Lines, Idempotent Ingestion)       |
     +-------------------------------------------------------------+
                                     │
                 ┌───────────────────┼───────────────────┐
                 ▼                   ▼                   ▼
         [Cost Snapshots]    [Allocation Engine]    [Forecasting & Variance]
         - Monthly/Quarterly - Direct / Pct / Hours  - Assumption Drivers
         - Immutable Version - Target Projects / CC  - Budget vs Plan vs Actual
                 │                   │                   │
                 └───────────────────┼───────────────────┘
                                     ▼
         +---------------------------------------------------------+
         |              Workforce Economics & Reconciliation       |
         |         - Cost/FTE, Cost/Hour, Overtime/Contractor %    |
         |         - Absence & Vacancy Drag                        |
         |         - Closed-Loop Payroll/Finance Reconciliation    |
         +---------------------------------------------------------+
```

## Bounded Context Ownership
- **Payroll**: Authoritative owner of earnings calculations, deductions, and tax withholding.
- **Finance**: Authoritative owner of GL accounts, journal entries, and financial actuals.
- **Workforce Cost**: Analytical modeling, labor allocation proposals, economics metrics, and forecasting.