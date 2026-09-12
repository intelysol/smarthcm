# Absence Forecasting Engine

## Predictive Modeling
`AbsenceAnalyticsAndForecastingService` provides forward-looking absence expectations using historical seasonal trends:
- Computes baseline expected absence rate per department and skill category.
- Applies seasonal multipliers (e.g., winter flu season, school holiday periods).
- Feeds anticipated absence rates directly into Epic 2.45 (Capacity Planning) and Epic 2.46 (Rostering) to avoid systematic understaffing.

## Console Command
```bash
php artisan hcm:absence-forecast --tenant-id=1 --forecast-month=2026-10
```