# Benefits, Expenses & Compensation Integration

## Multi-Source Aggregation
- **Benefits**: Extracts employer contributions from `benefit_contributions` and plan enrollments.
- **Expenses**: Ingests approved expense claims and travel vouchers from `expense_claims` linked to employee IDs.
- **Compensation**: Uses base pay rates and salary ranges from `compensation_plans` to establish baseline labor costs where payroll runs are pending.