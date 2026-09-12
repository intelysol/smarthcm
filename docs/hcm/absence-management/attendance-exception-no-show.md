# Attendance Exceptions & Automated No-Shows

## Real-Time No-Show Ingestion
When a scheduled shift begins and no punch-in occurs within the organization's grace period (e.g., 120 minutes):
1. The Time & Attendance engine (Epic 2.18 / 2.47) flags an attendance exception (`no_show`).
2. `AbsenceOrchestrationService::recordFromAttendanceException()` ingests this exception.
3. An unplanned `HcmAbsenceEvent` is generated with `absence_type = 'no_show'`.
4. Operational impact assessment immediately alerts the shift supervisor and computes replacement candidates.