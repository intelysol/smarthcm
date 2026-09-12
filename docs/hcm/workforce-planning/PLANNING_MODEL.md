# Workforce Planning Lifecycle & Assumptions Engine

## Planning Lifecycle State Transitions

```text
       ┌──────────┐
       │  DRAFT   │ ◄─── Plan created / Initial assumptions configured
       └────┬─────┘
            │ submit
            ▼
     ┌──────────────┐
     │ UNDER_REVIEW │ ◄─── Routing through HR, Finance & Business approvals
     └──────┬───────┘
            │ approve
            ▼
       ┌──────────┐
       │ APPROVED │ ◄─── Financially and organizationally sanctioned
       └────┬─────┘
            │ lock
            ▼
       ┌──────────┐
       │  LOCKED  │ ◄─── Frozen immutable snapshot generated
       └────┬─────┘
            │ create new version
            ▼
       ┌──────────┐
       │   OPEN   │ ◄─── v2, v3 unlocked for authorized replanning
       └──────────┘
```

## Assumption Parameter Catalog

Assumptions are never hardcoded. They are tenant- and plan-specific:
- `annual_salary_increase_pct`: Annual salary increment allowance (e.g. 6.5%).
- `expected_attrition_pct`: Annual expected aggregate turnover percentage (e.g. 10.0%).
- `recruitment_fill_rate_pct`: Historical job vacancy filling success rate (e.g. 88%).
- `hiring_lead_time_days`: Average days from requisition opening to employee start date (e.g. 45 days).
- `benefit_cost_pct`: Employer health and pension contribution rate on base salary (e.g. 18.0%).
- `payroll_tax_pct`: Employer statutory tax percentage (e.g. 8.5%).
