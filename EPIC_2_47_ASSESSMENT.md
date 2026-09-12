# EPIC 2.47 — Pre-Implementation Architecture Assessment
## HCM Workforce Time, Attendance, Timesheets, Overtime, Absence & Labor Compliance Intelligence

**Author:** DeepMind Agentic Assistant  
**Date:** September 2026  
**Status:** Architecture Assessment & Gap Analysis Completed  

---

## 1. Executive Summary & Mission

The mission of **Epic 2.47** is to establish the authoritative actual-work, attendance, timesheet, and labor compliance layer of the SmartHCM platform. It bridges the gap between planned workforce schedules (Epic 2.46) and strategic capacity management (Epic 2.45), while feeding verified, approved, payable time directly to Payroll and chargeable time to Finance.

### Operational Closed-Loop
```text
Workforce Planning (Epic 2.44)
       ↓
Capacity Planning (Epic 2.45)
       ↓
Rostering & Scheduling (Epic 2.46)
       ↓
Actual Clock Events & Ingestion (Epic 2.47)
       ↓
Attendance Normalization & Cross-Midnight Sessions
       ↓
Break Deduction & Policy Evaluation
       ↓
Schedule Reconciliation & Exception Detection
       ↓
Timesheet Aggregation & Project/Task Allocation
       ↓
Multi-Tier Overtime Calculation & Approval Workflow
       ↓
Labor Compliance Intelligence Monitoring
       ↓
Payroll Time Export & Discrepancy Reconciliation
       ↓
Workforce Capacity & Analytics Feedback (Epic 2.45)
```

---

## 2. Existing Foundation Inspection (Epic 2.18 & Epic 2.46)

An in-depth inspection of the SmartHCM codebase confirms robust architectural foundations established under Epic 2.18 and Epic 2.46:

### Existing Models & Tables
1. **Raw & Normalized Events**:
   - `attendance_raw_events` (`AttendanceRawEvent`): Captures `employee_device_identifier`, `event_timestamp`, `raw_payload`, `idempotency_key`, `is_processed`, `source`.
   - `attendance_events` (`AttendanceEvent`): Normalized events with `local_date`, `local_time`, `timezone`, `event_type` (`IN`, `OUT`, `BREAK_START`, `BREAK_END`), `raw_event_id`.
   - `attendance_devices` (`AttendanceDevice`): Terminal and biometric device tracking (`vendor`, `ip_address`, `location_id`, `sync_status`).
2. **Attendance Sessions & Breaks**:
   - `attendance_sessions` (`AttendanceSession`): Pairs IN/OUT events, associates shifts (`shift_definition_id`, `roster_assignment_id`), records `scheduled_start_time`, `actual_start_time`, `net_worked_minutes`, `regular_minutes`, `overtime_minutes`, `late_minutes`, `early_departure_minutes`, `is_overnight`.
   - `attendance_session_breaks` (`AttendanceSessionBreak`): Tracks break starts, ends, and durations.
3. **Policies & Calendars**:
   - `attendance_policies` (`AttendancePolicy`): Configurable grace periods, late thresholds, rounding intervals, break auto-deduction flags.
   - `attendance_policy_assignments` (`AttendancePolicyAssignment`): Scope assignments (global, company, branch, department).
   - `work_calendars`, `work_calendar_days`, `work_calendar_exceptions`: Organization working days, standard hours, and custom dates.
   - `holiday_calendars`, `holidays`: Public and organizational holiday calendars.
4. **Exceptions & Adjustments**:
   - `attendance_exceptions` (`AttendanceException`): Tracks deviations (`late_arrival`, `early_departure`, `missing_in`, `missing_out`, `no_show`).
   - `attendance_adjustments` (`AttendanceAdjustment`): Manual correction requests storing `original_values`, `requested_values`, and workflow approval status.
5. **Timesheets & Overtime**:
   - `timesheets` (`Timesheet`): Period aggregations (`total_regular_minutes`, `total_overtime_minutes`, `total_paid_leave_minutes`, approval timestamps).
   - `timesheet_entries` (`TimesheetEntry`): Daily entry breakdown linked to sessions.
   - `overtime_requests` (`OvertimeRequest`): Single-tier overtime approval records.
6. **Device Connectors**:
   - `BiometricConnector`, `ZKTecoConnector`, `RFIDConnector`, `APIAttendanceConnector`, `MobileAttendanceConnector`, `CSVAttendanceConnector`.

---

## 3. Gap Analysis for Enterprise Epic 2.47

While Epic 2.18 provided the base models, Epic 2.47 demands advanced enterprise capabilities:

| Capability | Current State (Epic 2.18 / 2.46) | Epic 2.47 Requirement | Enhancement Path |
|---|---|---|---|
| **Cross-Midnight Shifts** | Flag `is_overnight` exists on `attendance_sessions` | Robust logic associating punches between 22:00 and 06:00 to operational shift date | Dedicated `CrossMidnightAttendanceService` resolving punches against operational schedule window |
| **Project & Task Time Allocation** | `TimesheetEntry` only records total daily regular & overtime minutes | Enterprise project/task/activity/cost-center time allocation (Payable vs Chargeable Time) | New table `hcm_time_allocations` linked to timesheets with client/project/task granularity |
| **Multi-Tier Overtime** | `overtime_requests` has a single `calculated_overtime_minutes` | Tier 1 (1.5x), Tier 2 (2.0x), Tier 3 (2.5x/3.0x holiday/rest day), unauthorized overtime detection | New table `hcm_overtime_tier_records` and `OvertimeTierCalculationService` |
| **Payroll Time Export & Reconciliation** | `timesheets.exported_at` flag only | Batched payload export via Integration Hub + bidirectional reconciliation tracking discrepancies | New tables `hcm_payroll_time_exports` and `hcm_payroll_time_reconciliations` + Services |
| **Labor Compliance Intelligence** | Basic grace period in `AttendancePolicy` | Comprehensive labor compliance checks (max daily/weekly hours, mandatory rest intervals, meal breaks, fatigue) | New table `hcm_labor_compliance_checks` and `LaborComplianceIntelligenceService` |
| **Attendance Correction Audit** | Basic adjustment status | Immutable audit trail capturing field-level before/after diffs, actor, reason, supporting docs | Dedicated `AttendanceCorrectionService` with immutable history logging |
| **Policy Effective-Dating & Recalculation** | Policies have assignments with effective dates | Historical recalculation versioning preserving previous snapshots and logging reason/actor | Historical recalculation engine with snapshot preservation |
| **AI Intelligence** | None in Attendance domain (added in Scheduling) | Advisory attendance anomaly detection, timesheet explanations, compliance risk summaries | Dedicated `AdvisoryTimeAiService` with strict `is_advisory_only: true` governance |

---

## 4. Cross-Domain Architectural Boundaries ("Zero Duplicate Engines")

Under strict platform guidelines, Epic 2.47 respects existing domain ownership:

```text
[Core HR]              --> Owns Employee master, employment contracts, departments, positions.
[Scheduling (2.46)]    --> Owns published rosters, shift patterns, coverage requirements.
[Leave Management]     --> Owns leave types, balances, requests, and approved leave transactions.
[Payroll Engine]       --> Authoritative for pay rates, payroll calculation, taxation, net disbursement.
[Finance Engine]       --> Authoritative for GL posting, cost center ledger, project accounting.
[Workforce Capacity]   --> Ingests actual hours and productive hours for utilization metrics (Epic 2.45).
[Rules & Workflow]     --> Uses platform workflow instances and shared rule evaluator.
[Notification Platform]--> Dispatches alerts (missing punch, overtime approval, compliance alert).
```

---

## 5. Architectural Conclusions & Implementation Roadmap

1. **Schema Migration**:
   - Create migration `2026_09_29_000001_create_enterprise_time_and_labor_compliance_tables.php` defining:
     - `hcm_time_allocations`
     - `hcm_overtime_tier_records`
     - `hcm_payroll_time_exports`
     - `hcm_payroll_time_reconciliations`
     - `hcm_labor_compliance_checks`
     - `hcm_attendance_correction_audits`
2. **Domain Services & Architecture**:
   - `CrossMidnightAttendanceService`
   - `TimesheetProjectAllocationService`
   - `OvertimeTierCalculationService`
   - `PayrollTimeExportService`
   - `PayrollTimeReconciliationService`
   - `LaborComplianceIntelligenceService`
   - `AttendanceCorrectionService`
   - `AdvisoryTimeAiService`
3. **Queue Jobs & Commands**:
   - `ProcessRawAttendanceJob`, `ReconcilePayrollTimeJob`, `EvaluateLaborComplianceJob`, `ExportPayrollTimeJob`
   - Artisan commands: `hcm:time-compliance-monitor` and `hcm:payroll-time-export`
4. **API Endpoints**:
   - Complete `/api/v1/hcm/time/` surface for punch ingestion, corrections, timesheet allocations, overtime approvals, compliance inspections, payroll export & reconciliation.
5. **Feature Tests**:
   - 8 comprehensive test suites under `tests/Feature/AttendanceTime/`.
6. **Documentation Suite**:
   - 18 comprehensive markdown documents in `docs/hcm/workforce-time/` + `EPIC_2_47_IMPLEMENTATION.md` + update `.ai/ARCHITECTURE.md`.