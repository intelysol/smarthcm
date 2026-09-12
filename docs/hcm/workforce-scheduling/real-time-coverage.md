# Real-Time Operational Coverage

## Purpose

While scheduling produces the forward plan, operational success requires real-time reconciliation against reality.
The `RealTimeCoverageService` joins planned `RosterAssignment` with live `AttendanceSession` clock punches.

## Metrics & KPIs

- **Total Scheduled**: Count of workers rostered for the day.
- **Total Present**: Count of rostered workers with an active `actual_start_time`.
- **Total No-Shows**: Rostered workers with no clock punch after shift start + grace period.
- **Total Late**: Rostered workers who punched in after scheduled start + grace period (`late_minutes > 0`).
- **Real-Time Coverage %**: $$\frac{\text{Total Present}}{\text{Total Scheduled}} \times 100$$

## Operational Remediation
When real-time coverage drops below operational thresholds, the system can trigger emergency workforce allocation:
- Broadcasting emergency open shifts.
- Paging standby / on-call employees.
- Requesting shift extensions from existing workers.
