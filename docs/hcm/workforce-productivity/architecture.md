# Architecture & System Design

## 1. Architectural Philosophy

The Workforce Productivity domain acts as an intelligence aggregator sitting above operational and financial transaction layers.

```
                    ┌─────────────────────────┐
                    │      SMARTHCM CORE      │
                    └────────────┬────────────┘
                                 │
         ┌───────────────┬───────┴───────┬───────────────┐
         │               │               │               │
         ▼               ▼               ▼               ▼
    Scheduling       Attendance       Capacity     Workforce Cost
    (Epic 2.46)     (Epic 2.47)     (Epic 2.45)     (Epic 2.49)
         │               │               │               │
         └───────────────┼───────────────┴───────────────┘
                         ▼
        ┌───────────────────────────────────┐
        │  WORKFORCE PRODUCTIVITY DOMAIN   │
        │  (app/Domains/WorkforceProductivity)│
        └────────────────┬──────────────────┘
                         │
      ┌──────────────────┼──────────────────┐
      ▼                  ▼                  ▼
Measurements       Scorecards           Workforce
& Utilization     & Snapshots          ROI & Scenarios
```

## 2. Directory Structure

```text
app/Domains/WorkforceProductivity/
├── Contracts/
├── DTOs/
├── Enums/
├── Events/
├── Http/Controllers/
├── Jobs/
├── Models/
├── Routes/
└── Services/
```

## 3. Immutability & Reproducibility
- Versioned formulas: modifying a metric creates a new version; prior periods retain their historical formula reference.
- Periodic snapshots: locked upon executive approval, providing reproducible numbers for investor, board, and operational reviews.
