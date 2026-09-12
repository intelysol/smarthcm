# EPIC 2.46 — HCM Workforce Scheduling, Shift Management, Rostering, Skills-Based Scheduling & Real-Time Workforce Allocation

## 1. Implementation Overview

Epic 2.46 delivers the operational workforce scheduling, rostering, and real-time workforce allocation capability for SmartHCM.

It directly extends:
- **Epic 2.18** (*Time, Attendance, Shifts, Rostering & Workforce Scheduling*) as the foundational shift and roster model.
- **Epic 2.45** (*Workforce Capacity, Productivity, Workload & Operational Workforce Optimization*) as the demand and coverage requirement layer.

---

## 2. Core Capabilities Implemented

1. **Shift Management & Multi-Pattern Rotations**:
   - Fixed, weekly, rotating, split, and cross-midnight overnight shifts.
   - Punctuality parameters, grace periods, and break rules.
2. **Demand-Driven Coverage**:
   - `HcmScheduleCoverageRequirement` connects business capacity demand with shift requirements.
   - Real-time coverage calculation (`CoverageCalculationService`) generating multi-dimensional heatmaps (under-covered, optimal, over-covered).
3. **Availability & Preferences**:
   - `HcmEmployeeAvailability` provides individual and recurring availability blocks with strict privacy masking.
   - `HcmEmployeeShiftPreference` allows workers to declare preferred/avoided shifts and days.
4. **Skills & Compliance Eligibility Engine**:
   - `ScheduleEligibilityService` enforces required skill matching and minimum proficiency tiers (1–5 scale) from `CareerSkill` / `EmployeeSkill`.
   - Mandatory expired compliance certifications (`LearningRequirement`) block shift assignment.
5. **Schedule Validation & Quality Scoring**:
   - `ScheduleValidationService` segregates hard constraints (leave, inactive, rest <11h, missing skills) from soft constraints (overtime fatigue, preferences).
   - Generates objective technical `schedule_quality_score` (never used as an employee performance metric).
6. **Schedule Optimization Engine**:
   - `ScheduleOptimizationService` runs deterministic rule-based heuristic optimization.
   - Evaluates uncovered shifts, filters eligible workers, balances weekly workloads, and produces proposals (`HcmScheduleOptimizationRun`) without autonomous auto-publishing.
7. **Shift Swaps & Open Shifts**:
   - `ShiftSwapService`: Two-phase peer request/acceptance followed by manager authorization.
   - `OpenShiftService`: Broadcasts open shifts, collects bids with automated eligibility scoring, and assigns upon award.
8. **Publication, Cutoff Locks & Change Auditing**:
   - `SchedulePublicationService`: Gates publication on zero hard violations.
   - Schedule locking (`is_locked = true`) prevents unauthorized late edits.
   - Post-publication changes are audited in `HcmScheduleChangeLog`.
9. **Real-Time Operational Coverage & Exception Management**:
   - `RealTimeCoverageService` reconciles planned rosters with live `AttendanceSession` clock punches.
   - Detects no-shows and late arrivals, automatically generating `HcmScheduleException` records.
10. **Labor Cost & Overtime Risk Projections**:
    - `ScheduleCostEstimationService` calculates base wages, overtime risk premiums, and shift differentials (e.g. night +15%, weekend +10%) without mutating Payroll ledgers.
11. **Advisory AI Governance**:
    - `AdvisorySchedulingAiService` provides explainable recommendations with documented trade-offs and confidence scores, strictly adhering to `is_advisory_only: true`.

---

## 3. Database Schema Extensions

### Migration: `2026_09_28_000001_create_enterprise_workforce_scheduling_tables.php`
- Enhanced `roster_periods`: added `version`, `is_locked`, `locked_at`, `locked_by`, `validation_status`, `validation_summary`, `schedule_quality_score`, `estimated_labor_cost`.
- `hcm_schedule_coverage_requirements`: Demand staffing targets per shift, skill, position, and date.
- `hcm_employee_availabilities`: Availability declarations (available, unavailable, preferred, restricted).
- `hcm_employee_shift_preferences`: Shift and day preferences with priority weighting.
- `hcm_shift_swap_requests`: Multi-party swap requests with eligibility payloads.
- `hcm_open_shifts`: Open shift broadcast pool.
- `hcm_open_shift_bids`: Employee bids with eligibility scoring.
- `hcm_schedule_exceptions`: Real-time operational exceptions (no-show, late arrival, undercoverage).
- `hcm_schedule_acknowledgements`: Employee schedule receipt and dispute tracking.
- `hcm_schedule_change_logs`: Audit trail of post-publication assignment changes.
- `hcm_schedule_optimization_runs`: Records of optimization execution, metrics before/after, and proposals.

---

## 4. Verification & Testing Summary

Executed via Laragon PHP (`php-8.3.30-Win32-vs16-x64`):
```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test tests/Feature/Scheduling
```
**Results**:
- Tests: **13 passed**
- Assertions: **75 passed**
- Failures: **0**
- Test suites:
  1. `ScheduleValidationAndConstraintTest.php`
  2. `SkillAndCertificationEligibilityTest.php`
  3. `CoverageHeatmapAndDemandTest.php`
  4. `ScheduleOptimizationEngineTest.php`
  5. `ShiftSwapAndOpenShiftTest.php`
  6. `SchedulePublicationAndChangeAuditTest.php`
  7. `RealTimeCoverageAndExceptionTest.php`
  8. `AdvisoryAiAndPrivacyTest.php`
