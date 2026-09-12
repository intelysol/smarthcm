# Domain Ownership Boundaries

## Strict Boundary Rules

1. **Zero Duplicate Engines**:
   - Workforce Productivity never recalculates timesheets (Attendance domain owns punches).
   - Workforce Productivity never recalculates pay lines (Payroll domain owns gross/net earnings).
   - Workforce Productivity never calculates general ledger debits/credits (Finance domain owns GL).
   - Workforce Productivity never replaces Performance Reviews (Performance domain owns OKRs/appraisals).

2. **Consumption Contracts**:
   - Consumes actual punches from `hcm_time_allocations` & `attendance_sessions`.
   - Consumes planned shifts from `hcm_shifts` & `roster_assignments`.
   - Consumes absence impact from `hcm_absence_operational_impacts`.
   - Consumes cost lines and economics from `hcm_workforce_cost_lines` & `hcm_workforce_cost_economics`.
   - Consumes training costs from `learning_training_costs`.
