# Attendance Exceptions & Correction Lifecycle

## 1. Exception Engine
Monitors operational deviations:
- `missing_in` / `missing_out`: Single punch without matching pair
- `late_arrival` / `early_departure`: Deviation exceeding policy grace period
- `no_show`: Scheduled employee who did not clock in

## 2. Correction Workflow & Audit Trail
1. Employee or manager submits adjustment request via `AttendanceAdjustment`
2. Stores `original_values` and `requested_values` with business justification
3. Writes immutable entry to `hcm_attendance_correction_audits`
4. Approver reviews and accepts/rejects
5. On approval, non-destructively recalculates session minutes, sets `is_adjusted = true`, and logs approved audit