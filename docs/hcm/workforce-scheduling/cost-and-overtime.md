# Labor Cost Estimation & Overtime Risk

## Overview

To avoid unexpected budget overruns, `ScheduleCostEstimationService` provides forward-looking labor cost estimates before schedules are locked:
- **Base Planned Cost**: Planned shift hours multiplied by standard hourly wage or position midpoint.
- **Shift Differentials**: Configurable premiums for anti-social or demanding hours:
  - Night Shift Premium (e.g., +15%).
  - Weekend Shift Premium (e.g., +10%).
  - Holiday Premium (e.g., +25%).
- **Projected Overtime Premium**: When an employee's cumulative scheduled hours in a weekly window exceed 40 hours, projected hours above the threshold are weighted at 1.5× rate.

## Separation from Payroll
> **CRITICAL ARCHITECTURAL BOUNDARY**: The schedule cost engine produces planning estimates only. It never mutates payroll ledgers, wage records, or tax calculations, which remain strictly owned by the Payroll domain.
