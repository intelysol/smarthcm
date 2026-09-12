# HCM Canonical Domain Model

The HCM reference application treats Organization, Employee, Candidate,
Payroll Cycle, and Performance Review as aggregate roots. All roots are
tenant-scoped, use stable UUIDs, expose metadata extensions, and emit domain
events for Workflow, Communication, Analytics, Documents, AI, and Integration.

```mermaid
classDiagram
  Organization o-- Company
  Organization o-- Department
  Organization o-- Position
  Employee o-- Employment
  Employee o-- EmployeeDocument
  Employee o-- EmployeeSkill
  Candidate o-- Application
  PayrollCycle o-- PayrollRun
  PerformanceReview o-- Goal
```

## Employee lifecycle

```mermaid
stateDiagram-v2
  [*] --> draft
  draft --> pending_approval
  pending_approval --> active
  active --> on_leave
  on_leave --> active
  active --> suspended
  suspended --> active
  active --> resigned
  active --> terminated
  resigned --> archived
  terminated --> archived
```

The `EmployeeLifecycleService` is the single transition boundary. Invalid
transitions raise validation errors; valid transitions append immutable-style
timeline records and dispatch `EmployeeStatusChanged`.

## Canonical events

`EmployeeCreated`, `EmployeeUpdated`, `EmployeeStatusChanged`,
`LeaveRequested`, `LeaveApproved`, `PayrollProcessed`, `CandidateHired`,
`TrainingCompleted`, and `PerformanceReviewCompleted` are the event catalog
for downstream workflow, notification, analytics, AI, audit, and integration
consumers.

Universal entity fields are represented by the existing tenant, UUID, status,
version, creator, timestamps, and soft-delete conventions; custom attributes
remain in the Metadata Platform rather than altering core tables.
