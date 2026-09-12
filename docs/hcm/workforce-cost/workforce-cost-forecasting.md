# Workforce Cost Forecasting

## Predictive Mechanics
`WorkforceCostForecastService` builds forward-looking monthly or quarterly cost forecasts:
```text
Current Period Actuals (Baseline)
       + Headcount Growth Impact (e.g. +2.5%)
       + Scheduled Salary Increase Impact (e.g. +3.0%)
       + Overtime Factor Impact (e.g. +1.0%)
       + Contractor Factor Impact (e.g. +0.5%)
       = Projected Workforce Cost
```
Assumptions are preserved within the forecast record for transparent auditability.