# Attendance Sessions & Break Management

## 1. Attendance Session Model
`attendance_sessions` pairs `IN` and `OUT` events into a coherent operational work session:
- `scheduled_start_time` & `scheduled_end_time`
- `actual_start_time` & `actual_end_time`
- `gross_duration_minutes`
- `unpaid_break_minutes` & `paid_break_minutes`
- `net_worked_minutes`
- `regular_minutes` & `overtime_minutes`

## 2. Break Policies
- **Auto-Deducted Breaks**: Automatically deducted based on shift definition (e.g. 60m lunch break).
- **Recorded Breaks**: Explicit `break_start` and `break_end` punches tracked via `attendance_session_breaks`.