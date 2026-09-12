# EPIC 2.48 — Pre-Implementation Architecture Assessment
## HCM Workforce Absence, Leave, Attendance Exceptions, Return-to-Work & Absence Management Intelligence

**Author:** DeepMind Agentic Assistant  
**Date:** September 2026  
**Status:** Pre-Implementation Architecture Assessment Completed  

---

## 1. Executive Summary & Mission

The mission of **Epic 2.48** is to construct the authoritative workforce absence orchestration, operational coverage, return-to-work, and absence-intelligence layer of the SmartHCM platform. It does NOT replace or duplicate the core Leave engine, but rather connects approved leave, attendance exceptions, and unexpected absences with operational shift coverage, workforce capacity, and return-to-work workflows.

### Operational Absence Closed-Loop:
```text
Leave / Absence Event (Planned or Unplanned)
        ↓
Operational Schedule Impact (Epic 2.46 Shift Gaps)
        ↓
Workforce Capacity Impact (Epic 2.45 Capacity Deficit)
        ↓
Coverage & Replacement Options (Open Shifts, Swaps, Overtime)
        ↓
Attendance Reconciliation (Epic 2.47 Clock vs Leave Reconciliation)
        ↓
Return-to-Work Workflow & Operational Restrictions (HealthSafety 2.34)
        ↓
Absence Case Management for Long-Term/Pattern Absence (EmployeeRelations 2.41)
        ↓
Absence Intelligence & Trend Analytics (Privacy-Preserving)
        ↓
Strategic Workforce Planning Feedback (Epic 2.44)
```

---

## 2. Existing Foundation Inspection

A comprehensive survey of the SmartHCM codebase confirms existing capabilities across multiple domains:

### 2.1 Leave Management Foundation
- Tables: `leave_types`, `leave_policies`, `leave_balances`, `leave_applications`.
- Domain Ownership: Leave domain owns entitlements, accruals, leave balances, leave applications, and approval workflows.
- Boundary: Epic 2.48 references approved `leave_applications` and NEVER duplicates entitlement calculations or leave balances.

### 2.2 Time & Attendance (Epic 2.18 & Epic 2.47)
- Tables: `attendance_sessions`, `attendance_events`, `attendance_exceptions`, `attendance_adjustments`, `hcm_labor_compliance_checks`.
- Exceptions Captured: `missing_in`, `missing_out`, `late_arrival`, `early_departure`, `no_show`.
- Boundary: Epic 2.48 consumes no-shows and attendance exceptions to determine whether they represent operational absences, without automatically converting unexcused exceptions into approved leave.

### 2.3 Workforce Scheduling (Epic 2.46)
- Tables: `roster_periods`, `roster_assignments`, `shift_definitions`, `hcm_open_shifts`, `hcm_shift_swap_requests`, `hcm_schedule_exceptions`.
- Boundary: Epic 2.48 computes the *operational impact* of an absence (lost hours, gap in required coverage) and signals Epic 2.46 to initiate replacement planning (open shifts, swaps, or reassignment).

### 2.4 Workforce Capacity (Epic 2.45) & Workforce Planning (Epic 2.44)
- Tables: `hcm_workforce_plans`, `hcm_workforce_capacity_plans`, `hcm_capacity_calculations`.
- Boundary: Epic 2.48 feeds actual absence hours to Epic 2.45 to calculate net available capacity vs required capacity, and provides historical absence rates to Epic 2.44 to calibrate workforce planning assumptions.

### 2.5 Occupational Health & Safety (Epic 2.34)
- Tables: `hcm_medical_fitness_records`, `hcm_medical_restrictions`, `hcm_return_to_work_cases`, `hcm_workplace_accommodations`.
- Boundary: HealthSafety owns confidential medical determinations and clinical clearances. Epic 2.48 references the operational clearance and models the *operational return-to-work plan* (phased hours: 50% → 75% → 100%, no night shifts, max standing hours) without exposing diagnosis details to line managers.

### 2.6 HR Case Management (Epic 2.41)
- Tables: `employee_relation_cases`, `employee_relation_case_tasks`.
- Boundary: Long-term absences (> 30 days or policy threshold) or repeated absence patterns create an Absence Case referencing Epic 2.41 case management infrastructure without creating a duplicate case engine.

---

## 3. Gap Analysis for Enterprise Epic 2.48

| Capability | Current State | Required by Epic 2.48 | Implementation Path |
|---|---|---|---|
| **Normalized Absence Events** | Fragmented across `leave_applications` and `attendance_exceptions` | Authoritative `hcm_absence_events` unifying planned leave, unplanned sickness, and no-shows | Create `HcmAbsenceEvent` with source tracing (`leave`, `attendance`, `self_service`, `manager`) |
| **Absence Periods** | Single leave applications | Multi-day / continuous absence aggregation with working vs calendar days calculation | Create `HcmAbsencePeriod` supporting full, partial, and hourly absence |
| **Schedule Impact & Coverage** | Roster assignment has no direct link to absence gap | Immediate detection of affected shifts, lost capacity, and suggested replacement strategy | Create `HcmAbsenceOperationalImpact` linked to `roster_assignments` and `HcmOpenShift` |
| **Return-to-Work Workflow** | Base case in HealthSafety | Enterprise operational RTW plan: phased capacity (50%/75%/100%), modified duties, review reminders | Create `HcmAbsenceReturnToWorkPlan` with strict medical privacy separation |
| **Multi-Way Absence Reconciliation** | Basic payroll reconciliation in Epic 2.47 | 5-way reconciliation: Leave vs Absence vs Schedule vs Attendance vs Payroll | Create `HcmAbsenceReconciliation` detecting attendance during leave, leave without schedule adjustment, etc. |
| **Absence Intelligence & Forecasting** | Basic overtime analytics | Aggregated department/location absence rates, seasonal trends, capacity risk forecasts | Create `HcmAbsenceForecast` and `AbsenceAnalyticsService` |
| **AI Governance** | Strictly advisory | Trend synthesis and coverage impact summaries with strict data minimization | Create `AdvisoryAbsenceAiService` with `is_advisory_only: true` |

---

## 4. Architectural Boundaries ("Zero Duplicate Engines")

```text
[Core HR]               --> Authoritative for Employee, Position, Organization.
[Leave Engine]          --> Authoritative for Entitlements, Balances, Leave Requests, and Approvals.
[Time & Attendance]     --> Authoritative for Biometric Punches, Sessions, Worked Minutes, and Exceptions.
[Workforce Scheduling]  --> Authoritative for Published Rosters, Shifts, Swaps, and Open Shift Bids.
[Workforce Capacity]    --> Authoritative for Demand, Planned Capacity, and Utilization Calculations.
[Occupational Health]   --> Authoritative for Medical Fitness, Clinical Restrictions, and Clinical Clearances.
[HR Case Management]    --> Authoritative for Case Triage, Investigation, and Workflow Governance.
[Payroll Platform]      --> Authoritative for Pay Calculation, Leave Pay Treatment, and Disbursement.
```

---

## 5. Architectural Conclusions & Implementation Roadmap

1. **New Bounded Context / Domain**:
   - Resides cleanly under `App\Domains\Absence\`.
2. **Database Schema & Migration**:
   - Migration: `2026_09_30_000001_create_enterprise_absence_and_return_to_work_tables.php`
   - Tables:
     - `hcm_absence_events`
     - `hcm_absence_periods`
     - `hcm_absence_operational_impacts`
     - `hcm_absence_return_to_work_plans`
     - `hcm_absence_cases`
     - `hcm_absence_reconciliations`
     - `hcm_absence_forecasts`
3. **Domain Services**:
   - `AbsenceOrchestrationService`
   - `AbsenceOperationalImpactService`
   - `ReturnToWorkService`
   - `AbsenceReconciliationService`
   - `AbsenceAnalyticsAndForecastingService`
   - `AdvisoryAbsenceAiService` (`is_advisory_only: true`)
4. **Queued Jobs & Console Commands**:
   - `ProcessAbsenceEventJob`, `ReconcileAbsenceRecordsJob`, `GenerateAbsenceForecastJob`
   - Artisan CLI: `hcm:absence-reconcile`, `hcm:absence-forecast`
5. **REST APIs & Inertia UI**:
   - Complete `/api/v1/hcm/absence/` route surface.
6. **Feature Tests**:
   - 8 comprehensive feature tests under `tests/Feature/Absence/`.
7. **Documentation Suite (18 Markdown Files)**:
   - Under `docs/hcm/absence-management/` + `EPIC_2_48_IMPLEMENTATION.md` + update `.ai/ARCHITECTURE.md`.