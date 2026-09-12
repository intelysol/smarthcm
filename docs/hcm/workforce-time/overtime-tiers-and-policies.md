# Multi-Tier Overtime Engine

## 1. Configurable Overtime Tiers
- **Tier 1 (1.5x)**: First 120 minutes (up to 2 hours) of overtime beyond standard daily hours
- **Tier 2 (2.0x)**: Next 120 minutes (2 to 4 hours) of overtime
- **Tier 3 (2.5x / 3.0x)**: Overtime exceeding 4 hours, OR any worked hours on Rest Days and Public Holidays

## 2. Unauthorized Overtime Detection
Whenever `Actual Worked Minutes > Scheduled Minutes` without pre-approval, the record is flagged with `is_unauthorized = true` and routed for manager approval.