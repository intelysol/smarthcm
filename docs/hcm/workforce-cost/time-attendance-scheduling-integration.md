# Time & Attendance and Scheduling Integration

## Integrating Actual Work Records
Workforce Cost consumes worked hours and overtime from Epic 2.18 / 2.47:
1. **Regular Hours**: Aggregates gross and net worked minutes from `attendance_sessions`.
2. **Overtime Tiers**: Ingests `hcm_overtime_tier_records` (Tier 1 @ 1.5x, Tier 2 @ 2.0x, Tier 3 holiday) to compute overtime spend.
3. **Chargeable vs. Payable**: Leverages `hcm_time_allocations` to distinguish client-billable labor from internal operational labor.