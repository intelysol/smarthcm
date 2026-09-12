# EPIC 2.48 Implementation Summary
## HCM Workforce Absence, Leave, Attendance Exceptions, Return-to-Work & Absence Management Intelligence

**Date:** September 2026  
**Status:** Completed & 100% Verified (8/8 Feature Tests Passing, 50 Assertions)  

---

## 1. Executive Summary

Epic 2.48 establishes the enterprise-grade absence orchestration, operational impact evaluation, return-to-work (RTW), multi-way reconciliation, and predictive forecasting layer for the SmartHCM platform.

It directly integrates and links previously disconnected domains:
- **Leave Management** (`leave_applications`) as the source for planned leaves.
- **Time & Attendance** (Punches, timesheets, no-show exceptions — Epic 2.18 / 2.47).
- **Workforce Scheduling** (Rosters, shifts, allocations — Epic 2.46) for shift cancellations and replacement coverage.
- **Workforce Capacity & Workload** (Epic 2.45) for capacity deficit alerts and workload rebalancing.
- **Occupational Health & Safety** (Epic 2.34) for medical clearance and operational restrictions.
- **HR Shared Services & Case Management** (Epic 2.41) for formal chronic/long-term absence cases.

The module answers authoritatively:
> *"Who is unavailable, why at an operational level, what impact does that absence have, how should coverage be handled, and how can the organization identify absence trends without violating employee privacy?"*

---

## 2. Key Components Delivered

### 2.1 Database Schema & Migration
Migration: `database/migrations/2026_09_30_000001_create_enterprise_absence_and_return_to_work_tables.php`
- `hcm_absence_events`: Atomic unit of absence tracking planned vs. unplanned, categories, sources, and shift references.
- `hcm_absence_periods`: Continuous absence spans aggregating contiguous single-day events, tracking working/calendar days lost and long-term absence (> 30 days).
- `hcm_absence_operational_impacts`: Evaluates shift understaffing risks, capacity lost hours, and replacement assignments.
- `hcm_absence_return_to_work_plans`: Structured return workflows with medical clearance flags, operational restrictions, and phased capacity trajectories (e.g. 50% -> 75% -> 100%).
- `hcm_absence_cases`: Formal absence governance cases for chronic and long-term absences linked to Epic 2.41.
- `hcm_absence_reconciliations`: Automated multi-way discrepancies (punches during approved leave, unplanned absence without leave, overlapping records).
- `hcm_absence_forecasts`: Forward-looking absence rates and projected hours by department and skill category.

### 2.2 Eloquent Models (`app/Domains/Absence/Models/`)
- `HcmAbsenceEvent`
- `HcmAbsencePeriod`
- `HcmAbsenceOperationalImpact`
- `HcmAbsenceReturnToWorkPlan`
- `HcmAbsenceCase`
- `HcmAbsenceReconciliation`
- `HcmAbsenceForecast`

### 2.3 Domain Services (`app/Domains/Absence/Services/`)
- `AbsenceOrchestrationService`: Reports planned/unplanned absences, converts leave approvals and no-show attendance exceptions into events, aggregates contiguous days into continuous periods, and flags long-term episodes.
- `AbsenceOperationalImpactService`: Evaluates schedule impact on active shifts, calculates understaffing risks, and assigns qualified replacements.
- `ReturnToWorkService`: Manages RTW plan creation, phased capacity ramps, functional operational restrictions, and status transitions.
- `AbsenceReconciliationService`: Runs multi-way reconciliation across leave applications, raw punches/sessions, and absence events.
- `AbsenceAnalyticsAndForecastingService`: Calculates enterprise absence rates (lost time %, frequency rate, Bradford Factor) and generates seasonal forecasts.
- `AdvisoryAbsenceAiService`: Generates strictly advisory absence insights with zero access to confidential medical diagnoses.

### 2.4 Queue Jobs & Artisan Commands
- Jobs (`app/Domains/Absence/Jobs/`): `ProcessAbsenceEventJob`, `ReconcileAbsenceRecordsJob`, `GenerateAbsenceForecastJob`.
- Commands (`app/Console/Commands/`):
  - `php artisan hcm:absence-reconcile`: Daily/on-demand multi-way reconciliation runner.
  - `php artisan hcm:absence-forecast`: Monthly predictive absence forecasting runner.

### 2.5 REST API Controllers & Routes
Prefix: `/api/v1/hcm/absence`
- `AbsenceEventController`: POST/GET `/events`, GET `/events/{id}`
- `AbsencePeriodController`: GET `/periods`, GET `/periods/{id}`
- `AbsenceImpactController`: GET `/impacts`, POST `/impacts/{id}/replace`
- `ReturnToWorkController`: POST/GET `/rtw-plans`, GET `/rtw-plans/{id}`, PUT `/rtw-plans/{id}/status`
- `AbsenceCaseController`: POST/GET `/cases`, GET `/cases/{id}`
- `AbsenceReconciliationController`: POST `/reconciliation/run`, GET `/reconciliation`
- `AbsenceAnalyticsController`: GET `/analytics/rates`, GET `/analytics/forecasts`
- `AdvisoryAbsenceAiController`: GET `/ai/advisory-insights`

### 2.6 Comprehensive Documentation Suite
Located in `docs/hcm/absence-management/`:
1. `README.md`
2. `architecture.md`
3. `planned-vs-unplanned-absence.md`
4. `absence-events-and-periods.md`
5. `leave-integration.md`
6. `attendance-exception-no-show.md`
7. `schedule-capacity-impact.md`
8. `coverage-replacement-planning.md`
9. `return-to-work-workflow.md`
10. `phased-return-restrictions.md`
11. `absence-case-management.md`
12. `multi-way-reconciliation.md`
13. `absence-rate-analytics.md`
14. `absence-forecasting.md`
15. `payroll-finance-capacity-loop.md`
16. `ai-governance-and-advisory.md`
17. `api.md`
18. `security-privacy-audit.md`

---

## 3. Verification & Test Results

### 3.1 Feature Test Suite (`tests/Feature/Absence/`)
- `AbsenceOrchestrationAndEventTest`: Verified planned leave conversion, self-service unplanned reporting, and continuous period aggregation.
- `AttendanceExceptionAndNoShowTest`: Verified automatic ingestion of attendance no-show exceptions into unplanned absence events.
- `OperationalImpactAndCoverageTest`: Verified understaffing risk evaluation and replacement candidate assignment.
- `ReturnToWorkAndPhasedCapacityTest`: Verified structured RTW plans, operational restrictions, and phased capacity transitions.
- `AbsenceCaseManagementTest`: Verified long-term absence (> 30 days) automatically triggering formal HR absence cases.
- `MultiWayAbsenceReconciliationTest`: Verified detection of punches during approved leave and unplanned absence without leave.
- `AbsenceAnalyticsAndForecastingTest`: Verified accurate absence rate calculations and forward-looking seasonal forecasts.
- `AdvisoryAbsenceAiPrivacyTest`: Verified AI insights operate strictly as advisory recommendations without exposing medical details.

**Result:** 8 tests, 8 passed, 50 assertions (100% green).