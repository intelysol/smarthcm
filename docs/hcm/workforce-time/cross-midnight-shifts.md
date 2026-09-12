# Cross-Midnight Shifts & Operational Date Resolution

## 1. The Challenge
When a shift starts in the evening (e.g. 22:00 on Monday) and ends the next morning (e.g. 06:00 on Tuesday), naïve date partitioning incorrectly records the check-out on Tuesday.

## 2. Cross-Midnight Solution
`CrossMidnightAttendanceService` evaluates:
- Scheduled shift hours (`start_time > end_time`)
- Lookback window for open active sessions within the past 18 hours
- Associates morning punches (< 12:00) with the previous evening's operational shift date
- Flags `is_overnight = true` and records total continuous shift duration