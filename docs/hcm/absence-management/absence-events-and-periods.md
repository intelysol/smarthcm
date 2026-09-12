# Absence Events & Continuous Periods

## Event Model (`HcmAbsenceEvent`)
An absence event captures a single absence occurrence for an employee on a specific calendar date.
- `absence_type`: `sick`, `unplanned_leave`, `no_show`, `injury_work`, `personal_emergency`, `statutory`.
- `is_unplanned`: Boolean distinguishing sudden call-offs from planned leave.
- `hours_absent`: Number of scheduled hours missed.
- `is_verified`: Supervisory verification flag.

## Continuous Period Aggregation (`HcmAbsencePeriod`)
Consecutive or contiguous single-day absence events are automatically rolled into continuous absence periods:
- Tracks total working days lost and elapsed calendar days.
- Triggers threshold warnings when an employee exceeds continuous duration limits (e.g., 5+ consecutive days triggering RTW protocols).
- Associates medical certification requirements upon crossing statutory thresholds.