# Multi-Way Absence Reconciliation

## Automated Discrepancy Detection
The reconciliation engine runs daily or on-demand to identify inconsistencies between three primary data sources:
1. **Leave Records** (`leave_applications`)
2. **Attendance Punches** (`hcm_punch_records`, `hcm_daily_attendance_summaries`)
3. **Absence Events** (`hcm_absence_events`)

## Discrepancy Types
- `punch_during_approved_leave`: Employee clocked in on a day they were approved for annual or sick leave.
- `unplanned_absence_without_leave`: Employee was absent or no-showed without an existing leave application or absence record.
- `overlapping_absence_leave`: Redundant duplicate records across leave and direct absence logging.

## Remediation Workflow
Administrators can resolve discrepancies via the API or command line:
```bash
php artisan hcm:absence-reconcile --tenant-id=1 --date=2026-09-07
```