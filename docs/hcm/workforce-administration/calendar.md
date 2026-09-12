# HR Calendar & Operational Deadlines

## 1. Overview
The HR Operational Calendar aggregates date-sensitive events and milestones across HCM domains without duplicating underlying transactions.

## 2. Event Aggregation Categories
- **Employee Joining & Leaving**: New hires joining soon and scheduled terminations.
- **Probation & Contract Expiries**: Upcoming probation reviews and fixed-term contract completions.
- **Compliance & Document Expiries**: Visa, work permit, driver license, and passport expirations.
- **Operational Deadlines**: Payroll cutoffs, annual performance cycle deadlines, open enrollment windows.

## 3. Synchronization & Reminders
- The `HrCalendarService` continuously synchronizes `OpsCalendarEvent` pointers.
- The `HcmOpsMonitorCommand` triggers reminder dispatches to HR administrators before critical operational cutoff dates.
