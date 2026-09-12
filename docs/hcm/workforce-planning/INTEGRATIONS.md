# Workforce Planning System Integrations

## Core Domain Integrations

1. **Core HR (`App\Domains\Employee\Models\Employee`)**:
   - Reads active headcount, department rosters, and hire/termination dates as baseline opening balances.
   - Never writes to or mutates employee personnel files.
2. **Payroll (`App\Domains\Payroll\Models\PayrollRun`)**:
   - Ingests gross and net payroll expenditures for actual vs plan variance calculations.
3. **Recruitment (`App\Domains\WorkforcePlanning\Services\HiringPlanService`)**:
   - Approved hiring plan lines generate structured job requisition payloads for recruitment intake without creating fake candidate profiles.
4. **Platform Analytics & Reporting (`App\Domains\Analytics`)**:
   - Provides historical turnover, absenteeism, and compensation percentiles to inform planning assumptions.
